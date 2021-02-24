<?php
include("database.php");

session_start();
if (!isset($_SESSION['u_id']))
{
  header('Location: ./');
}
$user_id = $_SESSION['u_id'];

$db = new Database();

$todo = $db->escapeString($_POST['todo']);
$list = $_POST['list'];


// $listCheck = $db->querySingle("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = $user_id AND lists.id = $list");


$stmt = $db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = :u_id AND lists.id = :l_id');
$stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':l_id', $list, SQLITE3_INTEGER);
$listCheck = $stmt->execute();
$listCheck = $listCheck->fetchArray();


if (empty($listCheck)) {
    header("Location: ./lists_index.php?status=error&message=The%20list%20does%20not%20exist!");
    exit;
}

if (strlen($todo) < 1 || strlen($todo)>1000) {
    header("Location: ./lists_show.php?list=$list&status=error&message=2doo%20not%20within%201%20to%20100%20character%20range!");
    exit;
}

// $db->exec("INSERT INTO list_items VALUES (NULL, $list, '$todo', 0)");

$stmt = $db->prepare('INSERT INTO list_items VALUES (NULL, :l_id, :t_id, 0)');
$stmt->bindValue(':l_id', $list, SQLITE3_INTEGER);
$stmt->bindValue(':t_id', $todo, SQLITE3_TEXT);
$stmt->execute();

header("Location: ./lists_show.php?list=$list&status=success&message=2doo%20created!");
?>