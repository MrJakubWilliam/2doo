<?php
session_start();
require_once "../middleware/auth.php";
authCheck();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php
include("../views/partials/head.php");
?>

<body class="auth">
    <?php
    include("../views/partials/main/navbar.php");
    ?>
    <div class="container">
        <section>
            <form novalidate onsubmit="return sendForm(this)" class="auth-form">

                <div class="buttons">
                    <a href="../"><i class="fas fa-chevron-circle-left"></i> Back</a>
                </div>
                <div class="form-element">
                    <label for="email">Email: </label>
                    <input type="email" id="email" name="email" value="" required maxlength="100">
                    <div for="email" class="error-msgs"></div>
                </div>

                <div class="form-element">
                    <label for="password">Password: </label>
                    <input type="password" id="password" name="password" value="" required>
                    <div for="password" class="error-msgs"></div>
                </div>

                <input type="submit" name="" class="button blue-bg" value="Login">
            </form>
        </section>
    </div>
    <script type="application/javascript">
        function sendForm(form) {
            send(form, "auth", "authenticate");
            return false;
        }
    </script>

</body>

</html>