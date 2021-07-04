<?php

class HouseholdController
{
    private static $db;

    public static function index($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        $stmt = self::$db->prepare('SELECT households.*, users_households.privilages AS privilages 
        FROM households INNER JOIN users_households ON users_households.household_id = households.id 
        WHERE users_households.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $households = self::$db->getArray($stmt->execute());

        echo json_encode($households);

        exit;
    }

    public static function store($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['name'])) {
            $data = ["status" => "error", "message" => "Household name is required!"];
            echo json_encode($data);
            exit;
        }

        $name = $_POST['name'];

        if (strlen($name) < 1 || strlen($name) > 100) {
            $data = ["status" => "error", "message" => "Household name not within 1 to 100 character range!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT code FROM households');
        $codes = self::$db->getArray($stmt->execute());

        $codesCount = count($codes);

        $lowerBound = pow(10, 5);
        $upperPower = 5;
        do {
            $upperPower++;
            $upperBound = pow(10, $upperPower) - 1;

            // Extend the allow random number range, 
            // 'more than' 50% of the random numbers 
            // in the allowed range is taken.
        } while (($upperBound - $lowerBound) < 2 * $codesCount);


        do {
            $randCode = mt_rand($lowerBound, $upperBound);
        } while (in_array($randCode, $codes));

        $stmt = self::$db->prepare("INSERT INTO households VALUES (NULL, :n, $randCode)");
        $stmt->bindValue(':n', $name, SQLITE3_TEXT);
        $stmt->execute();
        $household = self::$db->lastInsertRowID();

        $dateJoined = new DateTime('now');

        $stmt = self::$db->prepare("INSERT INTO users_households VALUES (NULL, :u_id, :h_id, 2, 0, :date)");
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $stmt->bindValue(':date', $dateJoined->format("Y-m-d"), SQLITE3_TEXT);
        $stmt->execute();

        $data = ["status" => "success", "message" => "Household $name added!", "redir" => "./index.php"];
        echo json_encode($data);
    }

    public static function show($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['household'])) {
            $data = ["status" => "error", "message" => "Could not find the household!", "redir" => "./index.php"];
            echo json_encode($data);
            exit;
        }

        $household_id = $data['household'];

        $stmt = self::$db->prepare('SELECT households.*, users_households.privilages AS privilages FROM households INNER JOIN users_households ON users_households.household_id = households.id WHERE users_households.user_id = :u_id AND households.id = :h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $household = $stmt->execute()->fetchArray();

        if (empty($household)) {
            $data = ["status" => "error", "message" => "Household does not exist!", "redir" => "./index.php"];
            echo json_encode($data);
            exit;
        }

        $data = ["household" => $household, "user" => $user_id];
        echo json_encode($data);
    }


    public static function destroy($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];
        $household_id = $_POST["household"];

        $stmt = self::$db->prepare('SELECT households.* FROM households INNER JOIN users_households ON users_households.household_id = households.id WHERE households.id = :id AND users_households.user_id = :u_id AND users_households.privilages = 2');
        $stmt->bindValue(':id', $household_id, SQLITE3_INTEGER);
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $household = $stmt->execute()->fetchArray();

        if (empty($household)) {
            $data = ["status" => "error", "message" => "Household not found!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }

        // I couldn't get sqlite constaints (cascading on deletion) to work, that's why this is super messy.
        $stmt = self::$db->prepare('DELETE FROM join_requests WHERE household_id = :h_id');
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $stmt->execute();


        $stmt = self::$db->prepare('SELECT id FROM chores WHERE household_id = :h_id');
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $choresToDelete = $stmt->execute()->fetchArray();

        if (!is_bool($choresToDelete)) {

            $choreAllocationsToDelete = [];
            foreach ($choresToDelete as $choreToDelete) {
                $stmt = self::$db->prepare('SELECT id FROM chore_allocations WHERE chore_id = :c_id');
                $stmt->bindValue(':c_id', $choreToDelete, SQLITE3_INTEGER);
                $cd = $stmt->execute()->fetchArray();
                if (!is_bool($cd)) {

                    $choreAllocationsToDelete = array_merge($choreAllocationsToDelete, $cd);
                }
            }


            foreach ($choreAllocationsToDelete as $choreAllocationToDelete) {
                $stmt = self::$db->prepare('DELETE FROM complete_chores_photos WHERE chore_allocation_id = :ca_id');
                $stmt->bindValue(':ca_id', $choreAllocationToDelete, SQLITE3_INTEGER);
                $stmt->execute();
            }

            foreach ($choresToDelete as $choreToDelete) {
                $stmt = self::$db->prepare('DELETE FROM chore_allocations WHERE chore_id = :c_id');
                $stmt->bindValue(':c_id', $choreToDelete, SQLITE3_INTEGER);
                $stmt->execute();
            }
        }
        $stmt = self::$db->prepare('DELETE FROM chores WHERE household_id = :h_id');
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $stmt->execute();


        $stmt = self::$db->prepare('DELETE FROM users_households WHERE household_id = :h_id');
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $stmt->execute();

        $name = $household['name'];
        $stmt = self::$db->prepare('DELETE FROM households WHERE id = :id');
        $stmt->bindValue(':id', $household_id, SQLITE3_INTEGER);
        $stmt->execute();


        $data = ["status" => "success", "message" => "Household $name deleted!"];
        echo json_encode($data);
    }

    public static function edit($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['household'])) {
            $data = ["status" => "error", "message" => "Could not find the household!"];
            self::index($data);
            exit;
        }

        $household_id = $data['household'];

        $stmt = self::$db->prepare('SELECT households.*, users_households.privilages AS privilages FROM households INNER JOIN users_households ON users_households.household_id = households.id WHERE users_households.user_id = :u_id AND households.id = :h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $household = $stmt->execute()->fetchArray();

        if (empty($household)) {
            $data = ["status" => "error", "message" => "Household does not exist!"];
            self::index($data);
            exit;
        }

        $_SESSION['household'] = $household;

        $params = http_build_query($data);
        header("Location: ../household/edit.php?$params");
        exit;
    }
}
