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
                    <label for="name">Name: </label>
                    <input type="text" id="name" name="name" value="" required maxlength="100">
                    <div for="name" class="error-msgs"></div>

                </div>


                <div class="form-element">
                    <label for="email">Email: </label>
                    <input type="email" id="email" name="email" value="" required maxlength="100">
                    <div for="email" class="error-msgs"></div>

                </div>

                <div class="form-element">
                    <label for="password">Password: </label>
                    <input type="password" id="password" name="password" value="" required minlength="8">
                    <div id="passwd-strength"></div>
                    <div for="password" class="error-msgs"></div>

                </div>

                <input type="submit" name="" class="button blue-bg" value="Register">
            </form>
        </section>
    </div>
    <script type="application/javascript">
        $("input#password").on("input", function() {
            var strengthIndicator = $("#passwd-strength");
            var passLength = $(this).val().length;
            strengthIndicator.css("width", 100 * (passLength / 8) + "%");
            if (passLength < 4) {
                strengthIndicator.css("background-color", "#ff4a4a");
            } else if (passLength >= 4 && passLength < 8) {
                strengthIndicator.css("background-color", "#ffae4a");
            } else {
                strengthIndicator.css("background-color", "#3cce4e");
            }
        });

        function sendForm(form) {
            send(form, "auth", "signup");
            return false;
        }
    </script>
</body>

</html>