<?php

class ListItemController
{
    private static $db;

    public static function store($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['todo'])) {
            $data = ["status" => "error", "message" => "Todo is required!"];
            echo json_encode($data);
            exit;
        }

        if (!isset($_POST['list'])) {
            $data = ["status" => "error", "message" => "List not found!"];
            echo json_encode($data);
            exit;
        }

        $todo = $_POST['todo'];
        $list = $_POST['list'];

        $stmt = self::$db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = :u_id AND lists.id = :l_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':l_id', $list, SQLITE3_INTEGER);
        $listCheck = $stmt->execute()->fetchArray();

        if (empty($listCheck)) {
            $data = ["status" => "error", "message" => "The list does not exist!", "redir" => "back"];
            echo json_encode($data);
            exit;
        }
        if (strlen($todo) < 1 || strlen($todo) > 1000) {
            $data = ["status" => "error", "message" => "2doo not within 1 to 100 character range!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('INSERT INTO list_items VALUES (NULL, :l_id, :t_id, 0)');
        $stmt->bindValue(':l_id', $list, SQLITE3_INTEGER);
        $stmt->bindValue(':t_id', $todo, SQLITE3_TEXT);
        $stmt->execute();

        $listItemId = self::$db->lastInsertRowID();
        $stmt = self::$db->prepare('SELECT * FROM list_items WHERE id=:id');
        $stmt->bindValue(':id', $listItemId, SQLITE3_TEXT);
        $listItem = $stmt->execute()->fetchArray();


        $data = ["status" => "success", "message" => "2doo created!", "listItem" => $listItem];
        echo json_encode($data);
    }

    public static function complete($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        $listItemId = $_POST['listitem'];

        $stmt = self::$db->prepare('SELECT list_items.* FROM list_items INNER JOIN lists ON list_items.list_id INNER JOIN categories ON lists.category_id = categories.id  WHERE list_items.id = :list_item_id AND categories.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':list_item_id', $listItemId, SQLITE3_INTEGER);
        $listItem = $stmt->execute()->fetchArray();

        if (empty($listItem)) {
            $data = ["status" => "error", "message" => "2doo does not exist!"];
            echo json_encode($data);
            exit;
        }
        if ($listItem['checked']) {
            $stmt = self::$db->prepare('UPDATE list_items SET checked = 0 WHERE id = :list_item_id');
        } else {
            $stmt = self::$db->prepare('UPDATE list_items SET checked = 1 WHERE id = :list_item_id');
        }
        $stmt->bindValue(':list_item_id', $listItemId, SQLITE3_INTEGER);
        $stmt->execute();

        $data = ["status" => "success", "message" => "2doo complete state changed!"];
        echo json_encode($data);
    }

    public static function destroy($data = [])
    {
        self::$db = new Database();
        $listItemId = $_POST["listitem"];
        $user_id = $_SESSION['u_id'];

        $stmt = self::$db->prepare('SELECT list_items.* FROM list_items INNER JOIN lists ON list_items.list_id INNER JOIN categories ON lists.category_id = categories.id  WHERE list_items.id = :li_id AND categories.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':li_id', $listItemId, SQLITE3_INTEGER);
        $list_item = $stmt->execute()->fetchArray();

        if (empty($list_item)) {
            $data = ["status" => "error", "message" => "2doo does not exist!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('DELETE FROM list_items WHERE id = :li_id');
        $stmt->bindValue(':li_id', $listItemId, SQLITE3_INTEGER);
        $stmt->execute();

        $data = ["status" => "success", "message" => "2doo removed!"];
        echo json_encode($data);
    }
}
