<?php
include("database.php");

session_start();

if (!isset($_SESSION['u_id']))
{
  header('Location: ./');
}

$user_id = $_SESSION['u_id'];

$db = new Database();

$name = $db->escapeString($_POST['name']);
$category = $_POST['category'];

// $categoryCheck = $db->querySingle("SELECT * FROM categories WHERE categories.user_id = $user_id AND categories.id = $category");

$stmt = $db->prepare('SELECT * FROM categories WHERE categories.user_id = :u_id AND categories.id = :c_id');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':c_id', $category, SQLITE3_INTEGER);
$categoryCheck = $stmt->execute();
$categoryCheck = $categoryCheck->fetchArray();


if (empty($categoryCheck)) {
    header("Location: ./categories_index.php?status=error&message=The%20category%20does%20not%20exist!");
    exit;
}

// $existingList = $db->querySingle("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = $user_id AND lists.category_id = $category AND lists.name = '$name'");

$stmt = $db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = :u_id AND lists.category_id = :c_id AND lists.name = :n');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':c_id', $category, SQLITE3_INTEGER);
$stmt->bindValue(':n', $name, SQLITE3_TEXT);

$existingList = $stmt->execute();
$existingList = $existingList->fetchArray();

if (!empty($existingList)) {
    header("Location: ./lists_create.php?category=$category&status=error&message=List%20$name%20exists!");
    exit;
}

if (strlen($name) < 1 || strlen($name)>100) {
    header("Location: ./categories_create.php?category=$category&status=error&message=List%20name%20not%20within%201%20to%20100%20character%20range!");
    exit;
}

// $db->exec("INSERT INTO lists VALUES (NULL, $category, '$name')");

$stmt = $db->prepare('INSERT INTO lists VALUES (NULL, :c_id, :n)');
// $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':c_id', $category, SQLITE3_INTEGER);
$stmt->bindValue(':n', $name, SQLITE3_TEXT);

$stmt->execute();


header('Location: ./lists_index.php?status=success&message=Category%20created!');
?>