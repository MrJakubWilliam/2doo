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
// $existingCategory = $db->querySingle("SELECT * FROM categories WHERE user_id = $user_id AND name = '$name'");
// print_r(count($existingCategory));

$stmt = $db->prepare('SELECT * FROM categories WHERE user_id = :u_id AND name = :n');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':n', $name, SQLITE3_TEXT);
$existingCategory = $stmt->execute();
$existingCategory = $existingCategory->fetchArray();


if (!empty($existingCategory)) {
    header('Location: ./categories_create.php?status=error&message=Category%20with%20this%20name%20exists!');
    exit;
}

if (strlen($name) < 1 || strlen($name)>100) {
    header('Location: ./categories_create.php?status=error&message=Category%20name%20not%20within%201%20to%20100%20character%20range!');
    exit;
}

// $db->exec("INSERT INTO categories VALUES (NULL, $user_id, '$name')");

$stmt = $db->prepare('INSERT INTO categories VALUES (NULL, :u_id, :n)');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':n', $name, SQLITE3_TEXT);
$stmt->execute();

header('Location: ./categories_index.php?status=success&message=Category%20created!');
?>