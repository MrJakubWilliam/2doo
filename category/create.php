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
            <h2>Create a new category</h2>
            <form novalidate onsubmit="return sendForm(this)">
                <div class="form-element">
                    <label for="name">Category name: </label>
                    <input type="text" id="name" name="name" minlength="1" maxlength="100" required>
                    <div for="name" class="error-msgs"></div>
                </div>
                <input type="submit" name="" class="button blue-bg" value="Create">
            </form>
        </div>
    </div>
</body>
<script type="application/javascript">
    function sendForm(form) {
        send(form, "category");
        return false;
    }
</script>

</html>