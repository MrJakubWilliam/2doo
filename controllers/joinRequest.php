<?php

class JoinRequestController
{
    private static $db;

    public static function index($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['household'])) {
            $data = ["status" => "error", "message" => "Household not provided!"];
            echo json_encode($data);
            exit;
        }

        $household_id = $data['household'];

        $stmt = self::$db->prepare('SELECT join_requests.id, users.name, users.email 
                                    FROM join_requests
                                    INNER JOIN households 
                                        ON join_requests.household_id = households.id 
                                    INNER JOIN users_households
                                        ON users_households.household_id = households.id
                                    INNER JOIN users 
                                        ON join_requests.user_id = users.id 
                                                    WHERE join_requests.household_id=:h_id 
                                                    AND users_households.user_id = :u_id 
                                                    AND users_households.privilages > 0');

        $stmt->bindValue(':h_id', $household_id, SQLITE3_INTEGER);
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);

        $joinRequests = self::$db->getArray($stmt->execute());
        echo json_encode($joinRequests);
    }

    public static function store($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['code'])) {
            $data = ["status" => "error", "message" => "Household code is required!"];
            echo json_encode($data);
            exit;
        }

        $code = $_POST['code'];

        if (!is_numeric($code)) {
            $data = ["status" => "error", "message" => "Code has an incorrect format!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM households WHERE code = :code');
        $stmt->bindValue(':code', $code, SQLITE3_INTEGER);
        $household = $stmt->execute()->fetchArray();

        if (empty($household)) {
            $data = ["status" => "error", "message" => "Household with this code does not exist!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM join_requests WHERE user_id = :u_id AND household_id=:h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household['id'], SQLITE3_INTEGER);
        $joinRequest = $stmt->execute()->fetchArray();

        if (!empty($joinRequest)) {
            $data = ["status" => "error", "message" => "Household request already sent!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users_households WHERE user_id = :u_id AND household_id=:h_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household['id'], SQLITE3_INTEGER);
        $flatmate = $stmt->execute()->fetchArray();

        if (!empty($flatmate)) {
            $data = ["status" => "error", "message" => "Already part of the {$household['name']} household!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare("INSERT INTO join_requests VALUES (NULL, :h_id, :u_id)");
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $household['id'], SQLITE3_INTEGER);
        $stmt->execute();

        $data = ["status" => "success", "message" => "Household request sent!", "redir" => "back"];
        echo json_encode($data);
    }

    public static function accept($data = [])
    {
        self::$db = new Database();

        if (!isset($_POST['request'])) {
            $data = ["status" => "error", "message" => "Join request id is required!"];
            echo json_encode($data);
            exit;
        }

        $request = $_POST['request'];
        $user_id = $_SESSION['u_id'];


        $stmt = self::$db->prepare('SELECT join_requests.* FROM join_requests 
                                    INNER JOIN households ON join_requests.household_id = households.id 
                                    INNER JOIN users_households ON users_households.household_id = households.id 
                                    WHERE join_requests.id = :id 
                                    AND users_households.user_id=:u_id 
                                    AND users_households.privilages > 0');
        $stmt->bindValue(':id', $request, SQLITE3_INTEGER);
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $joinRequest = $stmt->execute()->fetchArray();

        if (empty($joinRequest)) {
            echo json_encode(["status" => "error", "message" => "Join request does not exist!"]);
            exit;
        }

        $stmt = self::$db->prepare('DELETE FROM join_requests WHERE id = :id');
        $stmt->bindValue(':id', $joinRequest['id'], SQLITE3_INTEGER);
        $stmt->execute();

        $stmt = self::$db->prepare('SELECT duration_worked FROM users_households WHERE household_id = :h_id ORDER BY duration_worked ASC');
        $stmt->bindValue(':h_id', $joinRequest['household_id'], SQLITE3_INTEGER);
        $durations = $stmt->execute()->fetchArray();
        $shortest = $durations[0];

        $stmt = self::$db->prepare("UPDATE users_households SET duration_worked = duration_worked - :shortest WHERE household_id=:h_id");
        $stmt->bindValue(':shortest', $shortest, SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $joinRequest['household_id'], SQLITE3_INTEGER);
        $stmt->execute();


        $dateJoined = new DateTime('now');
        $stmt = self::$db->prepare("INSERT INTO users_households VALUES (NULL, :u_id, :h_id, 0, 0, :date)");
        $stmt->bindValue(':u_id', $joinRequest['user_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':h_id', $joinRequest['household_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':date', $dateJoined->format("Y-m-d"), SQLITE3_TEXT);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Join request accepted!"]);
    }

    public static function destroy($data = [])
    {
        self::$db = new Database();

        if (!isset($_POST['joinrequest'])) {
            $data = ["status" => "error", "message" => "Join request id is required!"];
            echo json_encode($data);
            exit;
        }

        $request = $_POST['joinrequest'];
        $user_id = $_SESSION['u_id'];

        $stmt = self::$db->prepare('SELECT join_requests.* FROM join_requests 
                                    INNER JOIN households ON join_requests.household_id = households.id 
                                    INNER JOIN users_households ON users_households.household_id = households.id 
                                    WHERE join_requests.id = :id 
                                    AND users_households.user_id=:u_id 
                                    AND users_households.privilages > 0');
        $stmt->bindValue(':id', $request, SQLITE3_INTEGER);
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $joinRequest = $stmt->execute()->fetchArray();

        if (empty($joinRequest)) {
            echo json_encode(["status" => "error", "message" => "Join request does not exist!"]);
            exit;
        }

        $stmt = self::$db->prepare('DELETE FROM join_requests WHERE id = :id');
        $stmt->bindValue(':id', $joinRequest['id'], SQLITE3_INTEGER);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Join request deleted!"]);
    }
}
