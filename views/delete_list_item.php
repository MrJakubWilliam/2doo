<?php 

session_start();
if (!isset($_SESSION['u_id']))
{
  header('Location: ./');
}
$user_id = $_SESSION['u_id'];

$id = $_GET["list_item"];

include("database.php");
$db = new Database();

// $list_item = $db->querySingle("SELECT list_items.* FROM list_items INNER JOIN lists ON list_items.list_id INNER JOIN categories ON lists.category_id = categories.id  WHERE list_items.id = $id AND categories.user_id = $user_id");

$stmt = $db->prepare('SELECT list_items.* FROM list_items INNER JOIN lists ON list_items.list_id INNER JOIN categories ON lists.category_id = categories.id  WHERE list_items.id = :li_id AND categories.user_id = :u_id');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':li_id', $id, SQLITE3_INTEGER);
$list_item = $stmt->execute();
$list_item = $list_item->fetchArray();




if (empty($list_item)) {
    header("Location: ./lists_index.php?status=error&message=2doo%20does%20not%20exist!");
    exit;
}
$list = $list_item['list_id'];

// $db->exec("DELETE FROM list_items WHERE id = $id");

$stmt = $db->prepare('DELETE FROM list_items WHERE id = :li_id');
// $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':li_id', $id, SQLITE3_INTEGER);
$stmt->execute();


header("Location: ./lists_show.php?list=$list&status=success&message=2doo%20removed!");

?>