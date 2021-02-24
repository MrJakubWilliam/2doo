<?php 

session_start();
if (!isset($_SESSION['u_id']))
{
  header('Location: ./');
}
$user_id = $_SESSION['u_id'];

$id = $_GET["list"];

include("database.php");
$db = new Database();

// $list = $db->querySingle("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.id = $id AND categories.user_id = $user_id");

$stmt = $db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.id = :l_id AND categories.user_id = :u_id');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':l_id', $id, SQLITE3_INTEGER);
$list = $stmt->execute();
$list = $list->fetchArray();


if (empty($list)) {
    header('Location: ./lists_index.php?status=error&message=List%20does%20not%20exist!');
    exit;
}

$name = $list['name'];
// $db->exec("DELETE FROM lists WHERE id = $id");

$stmt = $db->prepare('DELETE FROM lists WHERE id = :l_id');
// $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':l_id', $id, SQLITE3_INTEGER);
$stmt->execute();

header('Location: ./lists_index.php?status=success&message=List%20' . $name . '%20deleted!');

?>