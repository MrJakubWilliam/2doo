<?php
session_start();
require_once "../middleware/auth.php";
authValidate();

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<?php include("../views/partials/head.php"); ?>

<body class="dashboard">
    <div class="container">
        <?php include("../views/partials/dashboard/navbar.php"); ?>

        <div class="create-form">
            <h2>Create a new household</h2>
            <form novalidate onsubmit="return sendForm(this)">

                <div class="form-element">
                    <label for="name">Household name: </label>
                    <input type="text" id="name" name="name" value="" required minlength="1" maxlength="100">
                    <div for="name" class="error-msgs"></div>
                </div>
                <input type="submit" name="" class="button blue-bg" value="Create">
            </form>
        </div>
    </div>
    <script type="application/javascript">
        function sendForm(form) {
            send(form, "household");
            return false;
        }
    </script>
</body>

</html>