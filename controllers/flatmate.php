<?php

class FlatmateController
{
    private static $db;

    public static function index($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['household'])) {
            $data = ["status" => "error", "message" => "Household id not provided!"];
            echo json_encode($data);
            exit;
        }

        $household_id = $data['household'];


        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE user_id = :u_id AND household_id = :h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $households = $stmt->execute()->fetchArray();

        if (empty($households)) {
            echo json_encode(["status" => "error", "message" => "Household does not exist!"]);
            exit;
        }

        $stmt = self::$db->prepare('SELECT  users.id AS u_id, 
                                            users.name AS u_name, 
                                            users_households.id AS id, 
                                            users_households.privilages, 
                                            users_households.duration_worked, 
                                            users_households.date_time_joined                                    
                                    FROM users_households 
                                        INNER JOIN users 
                                            ON users_households.user_id = users.id 
                                    WHERE users_households.household_id = :h_id');
        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $flatmates = self::$db->getArray($stmt->execute());

        echo json_encode($flatmates);
    }

    public static function destroy($data = [])
    {
        self::$db = new Database();

        if (!isset($_POST['flatmate'])) {
            $data = ["status" => "error", "message" => "Flatmate id not provided!"];
            echo json_encode($data);
            exit;
        }

        $flatmate_id = $_POST['flatmate'];
        $user_id = $_SESSION['u_id'];


        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE id = :id');
        $stmt->bindValue(':id', $flatmate_id, SQLITE3_INTEGER);
        $flatmate = $stmt->execute()->fetchArray();

        if (empty($flatmate)) {
            echo json_encode(["status" => "error", "message" => "Could not find the flatmate!"]);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE user_id = :u_id AND household_id = :h_id AND privilages = 2');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $flatmate['household_id'], SQLITE3_INTEGER);
        $householdSuperAdmin = $stmt->execute()->fetchArray();

        if (empty($householdSuperAdmin)) {
            echo json_encode(["status" => "error", "message" => "You do not have permissions for this operation!"]);
            exit;
        }


        // Make chores that were allocated to the deleted flatmate randomly allocated from now on
        $stmt = self::$db->prepare("UPDATE chores SET users_households_id = NULL WHERE users_households_id=:uh_id");
        $stmt->bindValue(':uh_id', $flatmate_id, SQLITE3_INTEGER);
        $stmt->execute();


        // Delete all completed chores allocations that belonged to the deleted flatmate
        $stmt = self::$db->prepare('DELETE FROM chore_allocations WHERE users_households_id = :uh_id AND status = 2');
        $stmt->bindValue(':uh_id', $flatmate_id, SQLITE3_INTEGER);
        $stmt->execute();


        // Get all flatmates except the deleted one
        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE household_id=:h_id AND NOT id=:uh_id ORDER BY duration_worked ASC');
        $stmt->bindValue(':h_id', $flatmate["household_id"], SQLITE3_INTEGER);
        $stmt->bindValue(':uh_id', $flatmate_id, SQLITE3_INTEGER);
        $flatmates = self::$db->getArray($stmt->execute());

        if (empty($flatmates)) {
            // delete household
            $_POST["household"] = $flatmate["household_id"];
            HouseholdController::destroy();
            exit;
        }

        if ($flatmate["privilages"] == 2) {
            $privilage = 1;
            do {
                $stmt = self::$db->prepare("SELECT * FROM users_households WHERE household_id=:h_id AND privilages = $privilage AND NOT id=:uh_id ORDER BY date(date_time_joined) ASC Limit 1");
                $stmt->bindValue(':h_id', $flatmate["household_id"], SQLITE3_INTEGER);
                $stmt->bindValue(':uh_id', $flatmate_id, SQLITE3_INTEGER);
                $nextSuperAdmin = $stmt->execute()->fetchArray();
                $privilage = 0;
            } while (empty($nextSuperAdmin));

            $stmt = self::$db->prepare('UPDATE users_households SET privilages = 2 WHERE id = :id');
            $stmt->bindValue(':id', $nextSuperAdmin["id"], SQLITE3_INTEGER);
            $stmt->execute();
        }

        // Get all chore allocations that were not completed that belonged to the deleted user - they need to be reasigned
        $stmt = self::$db->prepare('SELECT chore_allocations.*, chores.duration AS duration FROM chore_allocations INNER JOIN chores ON chore_allocations.chore_id = chores.id WHERE chore_allocations.users_households_id=:uh_id ORDER BY chores.duration DESC');
        $stmt->bindValue(':uh_id', $flatmate_id, SQLITE3_INTEGER);
        $choreAllocations = self::$db->getArray($stmt->execute());



        foreach ($choreAllocations as $choreAllocation) {

            $stmt = self::$db->prepare('UPDATE chore_allocations SET users_households_id = :uh_id WHERE id = :id');
            $stmt->bindValue(':uh_id', $flatmates[0]["id"], SQLITE3_INTEGER);
            $stmt->bindValue(':id', $choreAllocation["id"], SQLITE3_INTEGER);
            $stmt->execute();

            $flatmates[0]["duration_worked"] += $choreAllocation["duration"];
            $flatmates = self::array_orderby($flatmates, 'duration_worked', SORT_ASC);
        }

        foreach ($flatmates as $flatmate) {
            $stmt = self::$db->prepare('UPDATE users_households SET duration_worked = :duration WHERE id = :id');
            $stmt->bindValue(':duration', $flatmate["duration_worked"], SQLITE3_INTEGER);
            $stmt->bindValue(':id', $flatmate["id"], SQLITE3_INTEGER);
            $stmt->execute();
        }

        $stmt = self::$db->prepare('DELETE FROM users_households WHERE id = :id');
        $stmt->bindValue(':id', $flatmate_id, SQLITE3_INTEGER);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Flatmate removed!"]);
    }

    public static function updateprivilage($data = [])
    {
        self::$db = new Database();

        $flatmate_id = $_POST['flatmate'];
        $privilage = $_POST['privilage'];
        $household = $_POST['household'];
        $user_id = $_SESSION['u_id'];

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE user_id = :u_id AND household_id = :h_id AND privilages = 2');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $householdSuperAdmin = $stmt->execute()->fetchArray();

        if (empty($householdSuperAdmin)) {
            echo json_encode(["status" => "error", "message" => "You do not have permissions for this operation!"]);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE id = :id AND household_id = :h_id');
        $stmt->bindValue(':id', $flatmate_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $flatmate = $stmt->execute()->fetchArray();

        if (empty($flatmate)) {
            echo json_encode(["status" => "error", "message" => "Flatmate does not exist!"]);
            exit;
        }

        if (!is_numeric($privilage)) {
            echo json_encode(["status" => "error", "message" => "Privilage has wrong format!"]);
            exit;
        }

        if ($privilage < 0 || $privilage > 1) {
            echo json_encode(["status" => "error", "message" => "Wrong privilage!"]);
            exit;
        }

        $stmt = self::$db->prepare("UPDATE users_households SET privilages = :privilages WHERE id=:id");
        $stmt->bindValue(':id', $flatmate_id, SQLITE3_INTEGER);
        $stmt->bindValue(':privilages', $privilage, SQLITE3_INTEGER);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Privilages changed!"]);
    }

    // https://www.php.net/manual/en/function.array-multisort.php
    private static function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}
