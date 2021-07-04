<?php
function authValidate()
{
    if (!isset($_SESSION['u_id'])) {
        header('Location: ../');
        exit;
    }

    $user_id = $_SESSION['u_id'];
}

function authCheck()
{
    if (isset($_SESSION['u_id'])) {
        header("Location: ../dashboard/index.php");
        $user_id = $_SESSION['u_id'];
        exit;
    }
}
