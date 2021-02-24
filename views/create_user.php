<?php
include("database.php");

$db = new Database();

$name = $db->escapeString($_POST['name']);
$email = $db->escapeString($_POST['email']);
$password = $_POST['password'];

if (strlen($name) < 1 || strlen($name)>100 || strlen($email) < 1 || strlen($email)>100) {
    header("Location: ./register.php?status=error&message=Email%20or%20name%20not%20within%20the%20allowed%20character%20range%20of%20100%20character!");
    exit;
}

// $existingUser = $db->querySingle("SELECT * FROM users WHERE email = '$email'");

$stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$existingUser = $stmt->execute();
$existingUser = $existingUser->fetchArray();

if (!empty($existingUser)) {
    header("Location: ./register.php?status=error&message=An%20account%20with%20this%20email%20exists!");
    exit;
}

$pass_hash = password_hash($password, PASSWORD_DEFAULT);

// $db->exec("INSERT INTO users VALUES (NULL, '$name', '$email', '$pass_hash')");


$stmt = $db->prepare('INSERT INTO users VALUES (NULL, :n, :email, :pass_hash)');
$stmt->bindValue(':n', $name, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':pass_hash', $pass_hash, SQLITE3_TEXT);
$stmt->execute();
// $existingUser = $existingUser->fetchArray();


// $user = $db->querySingle("SELECT * FROM users WHERE email = '$email'");

$stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$user = $stmt->execute();
$user = $user->fetchArray();

session_start();
$_SESSION['u_id'] = $user['id'];
header('Location: ./dashboard.php?status=success&message=Account%20created!');
?>