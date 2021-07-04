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
            <h2>Join a household</h2>
            <form novalidate onsubmit="return sendForm(this)">

                <div class="form-element">
                    <label for="code">Household code: </label>
                    <input type="text" id="code" name="code" value="" required numerical>
                    <div for="code" class="error-msgs"></div>
                </div>
                <input type="submit" name="" class="button blue-bg" value="Create">
            </form>
        </div>
    </div>
    <script type="application/javascript">
            function sendForm(form) {
                send(form, "joinrequest");
                return false;
            }
    </script>
</body>

</html>