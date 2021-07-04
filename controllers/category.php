<?php

class CategoryController
{
    private static $db;

    public static function index($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        $stmt = self::$db->prepare('SELECT * FROM categories WHERE user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $categories = self::$db->getArray($stmt->execute());

        echo json_encode($categories);
    }

    public static function store($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];
        if (!isset($_POST['name'])) {
            $data = ["status" => "error", "message" => "Category name is required!"];
            echo json_encode($data);
            exit;
        }
        $name = $_POST['name'];

        if (strlen($name) < 1 || strlen($name) > 100) {
            $data = ["status" => "error", "message" => "Category name not within 1 to 100 character range!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM categories WHERE user_id = :u_id AND name = :n');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':n', $name, SQLITE3_TEXT);
        $existingCategory = $stmt->execute()->fetchArray();

        if (!empty($existingCategory)) {
            $data = ["status" => "error", "message" => "Category with this name exists!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('INSERT INTO categories VALUES (NULL, :u_id, :n)');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':n', $name, SQLITE3_TEXT);
        $stmt->execute();

        $data = ["status" => "success", "message" => "Category $name created!", "redir" => "./index.php"];
        echo json_encode($data);
    }

    public static function show($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($data['category'])) {
            $data = ["status" => "error", "message" => "Could not find the category!", "redir" => "./index.php"];
            echo json_encode($data);
            exit;
        }

        $category_id = $data['category'];

        $stmt = self::$db->prepare('SELECT * FROM categories WHERE id = :c_id AND user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $category_id, SQLITE3_INTEGER);
        $category = $stmt->execute()->fetchArray();

        if (empty($category)) {
            $data = ["status" => "error", "message" => "Category does not exist!", "redir" => "./index.php"];
            echo json_encode($data);

            exit;
        }


        $stmt = self::$db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.category_id = :c_id AND categories.user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $category_id, SQLITE3_INTEGER);
        $lists = self::$db->getArray($stmt->execute());

        echo json_encode(["category" => $category, "lists" => $lists]);
    }

    public static function destroy($data = [])
    {
        self::$db = new Database();
        $user_id = $_SESSION['u_id'];

        if (!isset($_POST['category'])) {
            $data = ["status" => "error", "message" => "Category not provided!"];
            echo json_encode($data);
            exit;
        }

        $categoryId = $_POST["category"];

        $stmt = self::$db->prepare('SELECT * FROM categories WHERE id = :c_id AND user_id = :u_id');
        $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':c_id', $categoryId, SQLITE3_INTEGER);
        $category = $stmt->execute()->fetchArray();

        if (empty($category)) {
            $data = ["status" => "error", "message" => "Category does not exist!"];
            echo json_encode($data);
            exit;
        }

        $stmt = self::$db->prepare('SELECT * FROM lists WHERE category_id = :c_id');
        $stmt->bindValue(':c_id', $categoryId, SQLITE3_INTEGER);
        $listsToDelete = self::$db->getArray($stmt->execute());


        foreach ($listsToDelete as $listToDelete) {
            $stmt = self::$db->prepare('DELETE FROM list_items WHERE list_id = :l_id');
            $stmt->bindValue(':l_id', $listToDelete['id'], SQLITE3_INTEGER);
            $stmt->execute();
        }

        $stmt = self::$db->prepare('DELETE FROM lists WHERE category_id = :c_id');
        $stmt->bindValue(':c_id', $categoryId, SQLITE3_INTEGER);
        $stmt->execute();

        $name = $category['name'];
        $stmt = self::$db->prepare('DELETE FROM categories WHERE id = :c_id');
        $stmt->bindValue(':c_id', $categoryId, SQLITE3_INTEGER);
        $stmt->execute();

        $data = ["status" => "success", "message" => "Category $name deleted!", "redir" => "./index.php"];
        echo json_encode($data);
    }
}
