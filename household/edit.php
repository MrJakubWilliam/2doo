<?php
session_start();
require_once "../middleware/auth.php";
authValidate();

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<?php include("../views/partials/head.php"); ?>

<body class="dashboard">
    <script src="../js/components/editFlatmate.js"></script>

    <div class="container">
        <?php include("../views/partials/dashboard/navbar.php"); ?>

        <div class="edit-household">
            <h2>Edit household <span></span></h2>
            <div class="edit-flatmates-component"></div>
        </div>

    </div>
    <script type="application/javascript">
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if (!urlParams.has('household')) {
            triggerStatus("error", "Household not provided!");
            window.history.back();
        }
        const household = urlParams.get('household');

        $.get(`../routes/route.php?controller=flatmate&resource=index&household=${household}`, function(data) {
            data.forEach(flatmate => {
                if (flatmate.privilages < 2) {
                    new EditFlatmate(".edit-flatmates-component", {
                        "id": flatmate.id,
                        "name": flatmate.u_name,
                        "privilages": flatmate.privilages,
                        "household": household
                    });
                }
            });
        }, "json");
    </script>
</body>

</html>