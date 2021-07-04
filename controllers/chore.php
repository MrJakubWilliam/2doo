<?php
require "../util/validateDate.php";
class ChoreController
{
    private static $db;

    public static function index($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];
        $now = date('Y-m-d', strtotime("today"));

        $stmt = self::$db->prepare("UPDATE chore_allocations SET status = 1 WHERE date(:date) >= date(date_complete_by) AND status != 2");
        $stmt->bindValue(':date', $now, SQLITE3_TEXT);
        $stmt->execute();

        if (isset($data["household"])) {
            $householdId = $data["household"];

            $stmt = self::$db->prepare('SELECT chore_allocations.id AS ca_id, chore_allocations.status AS status, 
                                        chore_allocations.date_complete_by AS date_complete_by, 
                                        chores.*, users.name AS u_name, users.id AS u_id 
                                        FROM chore_allocations 
                                        INNER JOIN chores ON chore_allocations.chore_id = chores.id
                                        INNER JOIN users_households 
                                            ON chore_allocations.users_households_id = users_households.id 
                                        INNER JOIN users 
                                            ON users_households.user_id = users.id 
                                        WHERE users_households.household_id=:h_id ORDER BY date(date_complete_by) ASC');

            $stmt->bindValue(':h_id', $householdId, SQLITE3_INTEGER);
            $chores = self::$db->getArray($stmt->execute());
        } else {
            $stmt = self::$db->prepare('SELECT      chore_allocations.*, 
                                                    chores.name                 AS c_name,
                                                    chores.duration             AS duration,
                                                    households.id               AS h_id, 
                                                    households.name             AS h_name,
                                                    users_households.privilages AS privilages

                                    FROM            chore_allocations 
                                    INNER JOIN      users_households 
                                        ON          chore_allocations.users_households_id = users_households.id  
                                    INNER JOIN      chores 
                                        ON          chore_allocations.chore_id = chores.id 
                                    INNER JOIN      households 
                                        ON          users_households.household_id = households.id
                                    WHERE           users_households.user_id = :u_id 
                                    ORDER BY date(date_complete_by) ASC');

            $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
            $chores = self::$db->getArray($stmt->execute());
        }

        echo json_encode($chores);
    }

    public static function store($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['name'])) {
            $data = ["status" => "error", "message" => "Name field is required!"];
            echo json_encode($data);
            exit;
        }

        if (!isset($_POST['start'])) {
            $data = ["status" => "error", "message" => "Start field is required!"];
            echo json_encode($data);
            exit;
        }

        $name = $_POST['name'];
        $description = $_POST['description'];
        $frequency = $_POST['frequency'];
        $duration = $_POST['duration'];
        $start = $_POST['start'];
        $household = $_POST['household'];
        $flatmate = $_POST['flatmate'];

        if (strlen($name) < 1 || strlen($name) > 100) {
            $data = ["status" => "error", "message" => "Chore name not within 1 to 100 character range!"];
            echo json_encode($data);
            exit;
        }

        if (strlen($description) > 1000) {
            $data = ["status" => "error", "message" => "Description not within 1 to 1000 character range!"];
            echo json_encode($data);
            exit;
        }

        if (!is_numeric($frequency) || $frequency < 0 || $frequency > 3) {
            $data = ["status" => "error", "message" => "Frequency has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        if (!is_numeric($duration) || $duration < 5 || $duration > 60 || $duration % 5 != 0) {
            $data = ["status" => "error", "message" => "Duration has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        if (!validateDate($start, 'Y-m-d')) {
            $data = ["status" => "error", "message" => "Start date has an incorrect format!"];
            echo json_encode($data);
            exit;
        }
        $start = DateTime::createFromFormat('Y-m-d', $start);
        $now = new DateTime("yesterday");
        if ($start < $now) {
            $data = ["status" => "error", "message" => "Start date before the current date!"];
            echo json_encode($data);
            exit;
        }


        if (!is_numeric($household)) {
            $data = ["status" => "error", "message" => "Household has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE user_id=:u_id AND household_id=:h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $userHousehold = $stmt->execute()->fetchArray();

        if (empty($userHousehold)) {
            $data = ["status" => "error", "message" => "Household does not exist!"];
            echo json_encode($data);
            exit;
        }

        if (!is_numeric($flatmate)) {
            $data = ["status" => "error", "message" => "Flatmate has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE household_id=:h_id AND user_id=:f_id');
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $stmt->bindValue(':f_id', $flatmate, SQLITE3_INTEGER);
        $flatmateArr = $stmt->execute()->fetchArray();

        if (empty($flatmateArr) && $flatmate != 0) {
            $data = ["status" => "error", "message" => "Flatmate does not exist!"];
            echo json_encode($data);
            exit;
        }

        if ($flatmate == 0) {
            $stmt = self::$db->prepare('SELECT * FROM users_households WHERE household_id=:h_id ORDER BY duration_worked ASC');
            $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
            $flatmateArr = $stmt->execute()->fetchArray();

            $stmt = self::$db->prepare("INSERT INTO chores VALUES (NULL, NULL, :h_id, :name, :description, :frequency, :duration)");
        } else {
            $stmt = self::$db->prepare("INSERT INTO chores VALUES (NULL, :f_id, :h_id, :name, :description, :frequency, :duration)");
            $stmt->bindValue(':f_id', $flatmateArr['id'], SQLITE3_INTEGER);
        }
        $stmt->bindValue(':h_id', $household, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':frequency', $frequency, SQLITE3_INTEGER);
        $stmt->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $stmt->execute();
        $chore = self::$db->lastInsertRowID();

        $stmt = self::$db->prepare("UPDATE users_households SET duration_worked = duration_worked + :duration WHERE id=:f_id");
        $stmt->bindValue(':f_id', $flatmateArr['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare("INSERT INTO chore_allocations VALUES (NULL, :f_id, :c_id, 0, :date)");
        $stmt->bindValue(':f_id', $flatmateArr['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $chore, SQLITE3_INTEGER);
        $stmt->bindValue(':date', $start->format('Y-m-d'), SQLITE3_TEXT);
        $stmt->execute();

        $data = ["status" => "success", "message" => "Chore $name created!", "redir" => "back"];
        echo json_encode($data);
    }

    public static function show($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['chore'])) {
            $data = ["status" => "error", "message" => "Chore not provided!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }
        $choreAllocationId = $data["chore"];

        $stmt = self::$db->prepare('SELECT  chore_allocations.*, 
                                            chores.id AS c_id,
                                            chores.name AS name, 
                                            chores.duration AS duration, 
                                            chores.description AS description,
                                            chores.frequency AS frequency,
                                            users_households.privilages AS privilages,
                                            users.name AS flatmate,
                                            users.id AS f_id,
                                            households.name AS h_name,
                                            households.id AS h_id
                                    FROM chore_allocations
                                        INNER JOIN chores 
                                            ON chore_allocations.chore_id = chores.id 
                                        INNER JOIN households 
                                            ON chores.household_id = households.id 
                                        INNER JOIN users_households 
                                            ON users_households.household_id = households.id 
                                        INNER JOIN users_households uh2 
                                            ON chore_allocations.users_households_id = uh2.id
                                        INNER JOIN users
                                            ON uh2.user_id = users.id
                                    WHERE chore_allocations.id = :ca_id 
                                        AND users_households.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':ca_id', $choreAllocationId, SQLITE3_INTEGER);
        $choreAllocation = $stmt->execute()->fetchArray();

        if (empty($choreAllocation)) {
            $data = ["status" => "error", "message" => "Chore does not exist!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM complete_chores_photos WHERE chore_allocation_id=:ca_id');
        $stmt->bindValue(':ca_id', $choreAllocationId, SQLITE3_INTEGER);
        $chorePhotos = self::$db->getArray($stmt->execute());

        echo json_encode(["chore" => $choreAllocation, "photos" => $chorePhotos]);
    }

    public static function destroy($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['chore'])) {
            $data = ["status" => "error", "message" => "Chore not provided!"];
            echo json_encode($data);
            exit;
        }

        $choreAllocationId = $_POST["chore"];

        $stmt = self::$db->prepare('SELECT chore_allocations.*, chores.duration AS duration FROM chore_allocations
        INNER JOIN chores ON chore_allocations.chore_id = chores.id  INNER JOIN households 
        ON chores.household_id = households.id INNER JOIN users_households ON 
        users_households.household_id = households.id WHERE chore_allocations.id = :ca_id 
        AND users_households.privilages > 0 AND users_households.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':ca_id', $choreAllocationId, SQLITE3_INTEGER);
        $choreAllocation = $stmt->execute()->fetchArray();

        if (empty($choreAllocation)) {
            $data = ["status" => "error", "message" => "Chore does not exist!"];
            echo json_encode($data);
            exit;
        }
        if ($choreAllocation["status"] == 0 || $choreAllocation["status"] == 1) {
            $stmt = self::$db->prepare('UPDATE users_households SET duration_worked = duration_worked - :dur WHERE id = :id');
            $stmt->bindValue(':id', $choreAllocation['users_households_id'], SQLITE3_INTEGER);
            $stmt->bindValue(':dur', $choreAllocation['duration'], SQLITE3_INTEGER);
            $stmt->execute();
        }

        $stmt = self::$db->prepare('DELETE FROM complete_chores_photos WHERE chore_allocation_id = :ca_id');
        $stmt->bindValue(':ca_id', $choreAllocationId, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('DELETE FROM chore_allocations WHERE id = :ca_id');
        $stmt->bindValue(':ca_id', $choreAllocationId, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('DELETE FROM chore_allocations WHERE id = :ca_id');
        $stmt->bindValue(':ca_id', $choreAllocationId, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('SELECT * FROM chore_allocations WHERE chore_id = :c_id');
        $stmt->bindValue(':c_id', $choreAllocation['chore_id'], SQLITE3_INTEGER);
        $choreAllocationsRemainder = $stmt->execute()->fetchArray();

        if (empty($choreAllocationsRemainder)) {
            $stmt = self::$db->prepare('DELETE FROM chores WHERE id = :c_id');
            $stmt->bindValue(':c_id', $choreAllocation['chore_id'], SQLITE3_INTEGER);
            $stmt->execute();
        }

        $data = ["status" => "success", "message" => "Chore deleted!"];
        echo json_encode($data);
    }

    public static function update($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['choreId'])) {
            $data = ["status" => "error", "message" => "Chore to be updated not found!"];
            echo json_encode($data);
            exit;
        }
        if (!isset($_POST['name'])) {
            $data = ["status" => "error", "message" => "Name field is required!"];
            echo json_encode($data);
            exit;
        }

        if (!isset($_POST['start'])) {
            $data = ["status" => "error", "message" => "Start field is required!"];
            echo json_encode($data);
            exit;
        }

        if (!isset($_POST['household'])) {
            $data = ["status" => "error", "message" => "Household not found!"];
            echo json_encode($data);
            exit;
        }

        $choreId = $_POST['choreId'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $frequency = $_POST['frequency'];
        $duration = $_POST['duration'];
        $start = $_POST['start'];
        $household = $_POST['household'];
        $flatmate = $_POST['flatmate'];

        if (strlen($name) < 1 || strlen($name) > 100) {
            $data = ["status" => "error", "message" => "Chore name not within 1 to 100 character range!"];
            echo json_encode($data);
            exit;
        }

        if (strlen($description) > 1000) {
            $data = ["status" => "error", "message" => "Description not within 1 to 1000 character range!"];
            echo json_encode($data);
            exit;
        }

        if (!is_numeric($frequency) || $frequency < 0 || $frequency > 3) {
            $data = ["status" => "error", "message" => "Frequency has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        if (!is_numeric($duration) || $duration < 5 || $duration > 60 || $duration % 5 != 0) {
            $data = ["status" => "error", "message" => "Duration has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        if (!validateDate($start, 'Y-m-d')) {
            $data = ["status" => "error", "message" => "Start date has an incorrect format!"];
            echo json_encode($data);
            exit;
        }
        $start = DateTime::createFromFormat('Y-m-d', $start);
        $now = new DateTime("yesterday");
        if ($start < $now) {
            $data = ["status" => "error", "message" => "Start date before the current date!"];
            echo json_encode($data);
            exit;
        }


        if (!is_numeric($household)) {
            $data = ["status" => "error", "message" => "Household has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE user_id=:u_id AND household_id=:h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $userHousehold = $stmt->execute()->fetchArray();

        if (empty($userHousehold)) {
            $data = ["status" => "error", "message" => "Household does not exist!"];
            echo json_encode($data);
            exit;
        }

        if (!is_numeric($flatmate)) {
            $data = ["status" => "error", "message" => "Flatmate has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE household_id=:h_id AND user_id=:f_id');
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $stmt->bindValue(':f_id', $flatmate, SQLITE3_INTEGER);
        $flatmateArr = $stmt->execute()->fetchArray();

        if (empty($flatmateArr) && $flatmate != 0) {
            $data = ["status" => "error", "message" => "Flatmate does not exist!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT chore_allocations.*, chores.duration AS duration FROM chore_allocations INNER JOIN chores ON chore_allocations.chore_id = chores.id WHERE chore_allocations.chore_id = :c_id AND chore_allocations.status < 2 AND chores.household_id=:h_id');
        $stmt->bindValue(':c_id', $choreId, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household, SQLITE3_INTEGER);
        $choreAllocation = $stmt->execute()->fetchArray();

        if (empty($choreAllocation)) {
            $data = ["status" => "error", "message" => "Chore not found!"];
            echo json_encode($data);
            exit;
        }
        $stmt = self::$db->prepare('UPDATE users_households SET duration_worked = duration_worked - :duration WHERE id=:f_id');
        $stmt->bindValue(':f_id', $choreAllocation['users_households_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':duration', $choreAllocation['duration'], SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('UPDATE users_households SET duration_worked = duration_worked + :duration WHERE id=:f_id');
        $stmt->bindValue(':f_id', $flatmateArr['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('UPDATE chores SET name = :name, description=:description, frequency=:frequency, duration=:duration WHERE id = :c_id');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':frequency', $frequency, SQLITE3_INTEGER);
        $stmt->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $choreId, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('UPDATE chore_allocations SET users_households_id = :f_id, date_complete_by = :date, status = 0 WHERE chore_id = :c_id AND status < 2');
        $stmt->bindValue(':c_id', $choreId, SQLITE3_INTEGER);
        $stmt->bindValue(':f_id', $flatmateArr['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':date', $start->format('Y-m-d'), SQLITE3_TEXT);
        $stmt->execute();

        $data = ["status" => "success", "message" => "Chore $name updated!", "redir" => "back"];
        echo json_encode($data);
    }

    public static function complete($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['chore'])) {
            $data = ["status" => "error", "message" => "Chore not provided!"];
            echo json_encode($data);
            exit;
        }
        $choreId = $_POST['chore'];

        $stmt = self::$db->prepare('SELECT chore_allocations.*, 
                                        chores.users_households_id AS uh_id,
                                        chores.frequency AS frequency, 
                                        chores.household_id AS h_id,
                                        chores.duration AS duration
                                    FROM chore_allocations 
                                    INNER JOIN users_households 
                                        ON chore_allocations.users_households_id = users_households.id
                                    INNER JOIN chores 
                                        ON chore_allocations.chore_id = chores.id
                                    WHERE chore_allocations.id = :id 
                                        AND chore_allocations.status != 2 
                                        AND users_households.user_id = :u_id');

        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $choreId, SQLITE3_INTEGER);
        $chore = $stmt->execute()->fetchArray();

        if (is_bool($chore)) {
            $data = ["status" => "error", "message" => "Incomplete chore not found!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('UPDATE chore_allocations SET status = 2 WHERE id = :id');
        $stmt->bindValue(':id', $choreId, SQLITE3_INTEGER);
        $stmt->execute();

        $nextDate = null;
        if ($chore["frequency"] == 1) {
            $nextDate = new DateTime("tomorrow");
            $nextDate = $nextDate->format('Y-m-d');
        } else if ($chore["frequency"] == 2) {
            $nextDate = date('Y-m-d', strtotime("+1 week"));
        } else if ($chore["frequency"] == 3) {
            $nextDate = date('Y-m-d', strtotime("+2 weeks"));
        }

        $uhId = $chore["uh_id"];
        if ($uhId == null) {
            $stmt = self::$db->prepare('SELECT * FROM users_households WHERE household_id=:h_id ORDER BY duration_worked ASC');
            $stmt->bindValue(':h_id', $chore["h_id"], SQLITE3_INTEGER);
            $flatmates = self::$db->getArray($stmt->execute());
            $uhId = $flatmates[0]['id'];
        }

        if ($nextDate != null) {
            $stmt = self::$db->prepare('INSERT INTO chore_allocations VALUES (NULL, :uh_id, :c_id, 0, :date)');
            $stmt->bindValue(':uh_id', $uhId, SQLITE3_INTEGER);
            $stmt->bindValue(':c_id', $chore["chore_id"], SQLITE3_INTEGER);
            $stmt->bindValue(':date', $nextDate, SQLITE3_TEXT);
            $stmt->execute();

            $stmt = self::$db->prepare('UPDATE users_households SET duration_worked = duration_worked + :duration WHERE id = :uh_id');
            $stmt->bindValue(':uh_id', $uhId, SQLITE3_INTEGER);
            $stmt->bindValue(':duration', $chore["duration"], SQLITE3_INTEGER);
            $stmt->execute();
        }

        $successMsg = "Status of chore updated!";

        // https://www.w3schools.com/php/php_file_upload.asp
        $errorMsg = "";
        $target_dir = "../uploads/";
        foreach ($_FILES as $photo) {
            $target_file = $target_dir . basename($photo["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $check = getimagesize($photo["tmp_name"]);
            if ($check == false) {
                $uploadOk = 0;
                $errorMsg .= ($photo["name"] . " is not an image!");
            }

            if (file_exists($target_file)) {
                $uploadOk = 0;
                $errorMsg .= ($photo["name"] . " already exists!");
            }
            if ($photo["size"] > 500000) {
                $uploadOk = 0;
                $errorMsg .= ($photo["name"] . " exceeds the maximum size!");
            }

            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $uploadOk = 0;
                $errorMsg .= ($photo["name"] . " doesn't have the right format!");
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($photo["tmp_name"], $target_file)) {
                    $stmt = self::$db->prepare('INSERT INTO complete_chores_photos VALUES (NULL, :ca_id, :img, :imgName)');
                    $stmt->bindValue(':ca_id', $choreId, SQLITE3_INTEGER);
                    $stmt->bindValue(':img', $target_file, SQLITE3_TEXT);
                    $stmt->bindValue(':imgName', $photo["name"], SQLITE3_TEXT);
                    $stmt->execute();
                    $successMsg .= ($photo["name"] . " uploaded successfully!");
                } else {
                    $errorMsg .= ("There were unexpected problems with uploading " . $photo["name"] . ".");
                }
            }
        }


        if (strlen($errorMsg) > 0) {
            $data = ["status" => "info", "message" => $successMsg . $errorMsg];
            echo json_encode($data);
            exit;
        }

        $data = ["status" => "success", "message" => $successMsg];
        echo json_encode($data);
    }
}
