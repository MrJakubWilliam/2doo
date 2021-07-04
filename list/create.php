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
            <h2>Create a new list</h2>
            <form novalidate onsubmit="return sendForm(this)">

                <div class="form-element">
                    <label for="name">List name: </label>
                    <input type="text" id="name" name="name" value="" minlength="1" maxlength="100" required>
                    <div for="name" class="error-msgs"></div>
                </div>

                <div class="form-element">
                    <label for="category">Category: </label>
                    <select name="category" id="category">
                    </select>
                </div>

                <input type="submit" name="" class="button blue-bg" value="Create">
            </form>
        </div>
    </div>
    <script type="application/javascript">
        $.get('../routes/route.php?controller=category&resource=index', function(data) {
            if (!data.length) {
                window.history.back();
            } else {
                data.forEach(category => {
                    $("form #category").append(new Option(category.name, category.id));
                });

                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                if (urlParams.has('category')) {
                    var catId = urlParams.get("category");
                    if (data.some(c => c.id == catId)) {
                        $("form #category").val(catId);
                    }
                }
            }
        }, "json");

        function sendForm(form) {
            send(form, "list");
            return false;
        }
    </script>
</body>

</html>