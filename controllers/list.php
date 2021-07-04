<?php

class ListController
{

    private static $db;

    public static function index($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        $stmt = self::$db->prepare('SELECT lists.*, categories.name AS c_name FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $lists = self::$db->getArray($stmt->execute());
        echo json_encode($lists);
    }

    public static function store($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];
        if (!isset($_POST['name'])) {
            $data = ["status" => "error", "message" => "List name is required!"];
            echo json_encode($data);
            exit;
        }
        if (!isset($_POST['category'])) {
            $data = ["status" => "error", "message" => "Category is required!"];
            echo json_encode($data);
            exit;
        }
        $name = $_POST['name'];
        $category = $_POST['category'];

        $stmt = self::$db->prepare('SELECT * FROM categories WHERE categories.user_id = :u_id AND categories.id = :c_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $category, SQLITE3_INTEGER);
        $categoryCheck = $stmt->execute()->fetchArray();

        if (empty($categoryCheck)) {
            $data = ["status" => "error", "message" => "The category does not exist!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = :u_id AND lists.category_id = :c_id AND lists.name = :n');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $category, SQLITE3_INTEGER);
        $stmt->bindValue(':n', $name, SQLITE3_TEXT);
        $existingList = $stmt->execute()->fetchArray();

        if (!empty($existingList)) {
            $data = ["status" => "error", "message" => "List $name exists!"];
            echo json_encode($data);

            exit;
        }

        if (strlen($name) < 1 || strlen($name) > 100) {
            $data = ["status" => "error", "message" => "List name not within 1 to 100 character range!"];
            echo json_encode($data);

            exit;
        }


        $stmt = self::$db->prepare('INSERT INTO lists VALUES (NULL, :c_id, :n)');
        $stmt->bindValue(':c_id', $category, SQLITE3_INTEGER);
        $stmt->bindValue(':n', $name, SQLITE3_TEXT);
        $stmt->execute();

        $data = ["status" => "success", "message" => "List $name created!", "redir" => "back"];
        echo json_encode($data);
    }

    public static function show($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['list'])) {
            $data = ["status" => "error", "message" => "Cannot find the list!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }

        $list_id = $data['list'];


        $stmt = self::$db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.id = :l_id AND categories.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':l_id', $list_id, SQLITE3_INTEGER);
        $list = $stmt->execute()->fetchArray();

        if (empty($list)) {
            $data = ["status" => "error", "message" => "List does not exist!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }
        $stmt = self::$db->prepare('SELECT * FROM list_items WHERE list_id = :l_id');
        $stmt->bindValue(':l_id', $list_id, SQLITE3_INTEGER);
        $listItems = self::$db->getArray($stmt->execute());

        echo json_encode(["list" => $list, "listItems" => $listItems]);
    }

    public static function destroy($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];
        $list_id = $_POST["list"];

        $stmt = self::$db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.id = :l_id AND categories.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':l_id', $list_id, SQLITE3_INTEGER);
        $list = $stmt->execute()->fetchArray();

        if (empty($list)) {
            $data = ["status" => "error", "message" => "List does not exist!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }
        $stmt = self::$db->prepare('DELETE FROM list_items WHERE list_id = :l_id');
        $stmt->bindValue(':l_id', $list_id, SQLITE3_INTEGER);
        $stmt->execute();

        $name = $list['name'];
        $stmt = self::$db->prepare('DELETE FROM lists WHERE id = :l_id');
        $stmt->bindValue(':l_id', $list_id, SQLITE3_INTEGER);
        $stmt->execute();

        $data = ["status" => "success", "message" => "List $name deleted!"];
        echo json_encode($data);
    }
}
