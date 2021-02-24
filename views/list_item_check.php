<?php
include("database.php");

session_start();
if (!isset($_SESSION['u_id']))
{
  header('Location: ./');
}
$user_id = $_SESSION['u_id'];
// UPDATE table_name
// SET column1 = value1, column2 = value2, ...
// WHERE condition;

$db = new Database();

$list_item_id = $_GET['list_item'];

// $list_item = $db->querySingle("SELECT list_items.* FROM list_items INNER JOIN lists ON list_items.list_id INNER JOIN categories ON lists.category_id = categories.id  WHERE list_items.id = $list_item_id AND categories.user_id = $user_id");
// $list_item = $db->querySingle("SELECT * FROM list_items WHERE id = $list_item_id");
$stmt = $db->prepare('SELECT list_items.* FROM list_items INNER JOIN lists ON list_items.list_id INNER JOIN categories ON lists.category_id = categories.id  WHERE list_items.id = :list_item_id AND categories.user_id = :u_id');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':list_item_id', $list_item_id, SQLITE3_INTEGER);
$list_item = $stmt->execute();
$list_item = $list_item->fetchArray();



if (empty($list_item)) {
    header("Location: ./lists_index.php?status=error&message=2doo%20does%20not%20exist!");
    exit;
}

if ($list_item['checked']) {
    $db->exec("UPDATE list_items SET checked = 0 WHERE id = $list_item_id");
}else {
    $db->exec("UPDATE list_items SET checked = 1 WHERE id = $list_item_id");
}

header("Location: ./lists_show.php?list=" . $list_item['list_id']);
?>