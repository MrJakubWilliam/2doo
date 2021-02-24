<?php
include("database.php");

$db = new Database();

// $name = $_POST['name'];
$email = $db->escapeString($_POST['email']);
$password = $_POST['password'];

if (strlen($email) < 1 || strlen($email)>100) {
    header("Location: ./login.php?status=error&message=Email%20not%20within%20the%20allowed%20character%20range%20of%20100%20character!");
    exit;
}

// $user = $db->querySingle("SELECT * FROM users WHERE email = '$email'");


$stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$user = $stmt->execute();
$user = $user->fetchArray();


if (empty($user)) {
    header("Location: ./login.php?status=error&message=An%20account%20with%20this%20email%20does%20not%20exist!");
    exit;
}

if (!password_verify($password, $user['password'])) {
    header("Location: ./login.php?status=error&message=Wrong%20password!");
    exit;
}
session_start();
$_SESSION['u_id'] = $user['id'];
header('Location: ./dashboard.php?status=success&message=Login%20successful!');

// if (!empty($existingUser)) {
//     header("Location: ./register.php?status=error&message=An&account&with&this&email&exists!");
//     exit;
// }



// $pass_hash = password_hash($password, PASSWORD_DEFAULT);

// $db->exec("INSERT INTO users VALUES (NULL, '$name', '$email', '$pass_hash')");
// header('Location: ./dashboard.php?status=success&message=Account%20created!');
?>