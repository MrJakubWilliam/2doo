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
        <div class="top">
            <h2>Chores</h2>
            <div class="buttons">
                <a href="./create.php" class="button" id='add'>
                    <i class="fas fa-plus"></i>
                    <div class="hidden">
                        Add chore
                    </div>
                </a>

            </div>
        </div>
        <div class="components">
            <div class="component">
                <div class="component-top">
                    <h2>My chores</h2>
                </div>
                <div class="list-components">
                </div>
            </div>
        </div>
    </div>
    <script src="../js/components/listComponentPrivilaged.js"></script>
    <script type="application/javascript">
        $.get('../routes/route.php?controller=chore&resource=index', function(data) {
            data.forEach(chore => {

                var listComponent = new ListComponentPrivilaged(".list-components", "chore", {
                    "show": 0,
                    "edit": 1,
                    "destroy": 1
                }, {
                    "id": chore.id,
                    "privilages": chore.privilages,
                    "printable": {
                        "chore": chore.c_name,
                        "household": chore.h_name,
                        "duration": chore.duration + " minutes",
                        "date": chore.date_complete_by
                    }
                });
                if (chore.status == 0) {
                    listComponent.component.addClass("upcoming");
                }
                if (chore.status == 1) {
                    listComponent.component.addClass("pending");
                }
                if (chore.status == 2) {
                    listComponent.component.addClass("complete");
                }
            });
        }, "json");
    </script>
</body>

</html>