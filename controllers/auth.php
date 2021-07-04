<?php

class AuthController
{
    private static $db;

    public static function signup()
    {
        self::$db = new Database();
        if (!isset($_POST['name'])) {
            $data = ["status" => "error", "message" => "Name not provided"];
            echo json_encode($data);
            exit;
        }
        $name = $_POST['name'];
        if (!isset($_POST['email'])) {
            $data = ["status" => "error", "message" => "Email not provided"];
            echo json_encode($data);
            exit;
        }
        $email = $_POST['email'];
        if (!isset($_POST['password'])) {
            $data = ["status" => "error", "message" => "Password not provided"];
            echo json_encode($data);
            exit;
        }
        $password = $_POST['password'];

        if (strlen($name) < 1 || strlen($name) > 100 || strlen($email) < 1 || strlen($email) > 100) {
            $data = ["status" => "error", "message" => "Name or email not within the allowed character range of 100 character!"];
            echo json_encode($data);
            exit;
        }

        if (strlen($password) < 8) {
            $data = ["status" => "error", "message" => "Password field requires minimum 8 characters!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $existingUser = $stmt->execute();
        $existingUser = $existingUser->fetchArray();

        if (!empty($existingUser)) {
            $data = ["status" => "error", "message" => "An account with this email exist!"];
            echo json_encode($data);
            exit;
        }

        $pass_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = self::$db->prepare('INSERT INTO users VALUES (NULL, :n, :email, :pass_hash)');
        $stmt->bindValue(':n', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':pass_hash', $pass_hash, SQLITE3_TEXT);
        $stmt->execute();


        $stmt = self::$db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $user = $stmt->execute()->fetchArray();

        $_SESSION['u_id'] = $user['id'];

        $data = ["status" => "success", "message" => "Account created!", "redir" => "../dashboard/index.php"];
        echo json_encode($data);
    }

    public static function authenticate()
    {
        self::$db = new Database();

        if (!isset($_POST['email'])) {
            $data = ["status" => "error", "message" => "Email not provided"];
            echo json_encode($data);
            exit;
        }
        $email = $_POST['email'];
        if (!isset($_POST['password'])) {
            $data = ["status" => "error", "message" => "Password not provided"];
            echo json_encode($data);
            exit;
        }
        $password = $_POST['password'];

        if (strlen($email) < 1 || strlen($email) > 100) {
            $data = ["status" => "error", "message" => "Email not within the allowed character range of 100 character!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $user = $stmt->execute()->fetchArray();

        if (empty($user)) {
            $data = ["status" => "error", "message" => "An account with this email does not exist!"];
            echo json_encode($data);
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $data = ["status" => "error", "message" => "Wrong password!"];
            echo json_encode($data);
            exit;
        }

        $_SESSION['u_id'] = $user['id'];

        $data = ["status" => "success", "message" => "Login successful!", "redir" => "../dashboard/index.php"];
        echo json_encode($data);
        exit;
    }

    public static function logout()
    {
        session_destroy();
        session_unset();
        header('Location: ../index.php');
        exit;
    }
}
