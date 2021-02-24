<?php 

session_start();
if (!isset($_SESSION['u_id']))
{
  header('Location: ./');
}
$user_id = $_SESSION['u_id'];

$id = $_GET["category"];

include("database.php");
$db = new Database();
// $category = $db->querySingle("SELECT * FROM categories WHERE id = $id AND user_id = $user_id");

$stmt = $db->prepare('SELECT * FROM categories WHERE id = :c_id AND user_id = :u_id');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':c_id', $id, SQLITE3_INTEGER);
$category = $stmt->execute();
$category = $category->fetchArray();

if (empty($category)) {
    header('Location: ./categories_index.php?status=error&message=Category%20does%20not%20exist!');
    exit;
}

$name = $category['name'];
// $db->exec("DELETE FROM categories WHERE id = $id");

$stmt = $db->prepare('DELETE FROM categories WHERE id = :c_id');
$stmt->bindValue(':c_id', $id, SQLITE3_INTEGER);
$stmt->execute();


header('Location: ./categories_index.php?status=success&message=Category%20' . htmlspecialchars($name) . '%20deleted!');

?>