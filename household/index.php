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
            <h2>Households</h2>
            <div class="buttons">
                <a href="./create.php" class="button" id='add'>
                    <i class="fas fa-plus"></i>
                    <div class="hidden">
                        Add household
                    </div>
                </a>

                <a href="../joinrequest/create.php" class="button" id='join'>
                    <i class="fas fa-user-plus"></i>
                    <div class="hidden">
                        Join household
                    </div>
                </a>
            </div>
        </div>
        <div class="components">
            <div class="component">
                <div class="component-top">
                    <h2>My households</h2>
                </div>
                <div class="list-components">
                </div>
            </div>
        </div>
    </div>
    <script src="../js/components/listComponentPrivilaged.js"></script>
    <script type="application/javascript">
            $(".list-components").on("empty", function() {
                $(this).append('<span class="grayed-out">No households to show</span>');
            });

        $.get('../routes/route.php?controller=household&resource=index', function(data) {
            if (!data.length) {

                $(".list-components").append('<span class="grayed-out">No households to show</span>');
            } else {
                data.forEach(household => {
                    new ListComponentPrivilaged(".list-components", "household", {
                        "show": 0,
                        "edit": 2,
                        "destroy": 2
                    }, {
                        "id": household.id,
                        "privilages": household.privilages,
                        "printable": {
                            "name": household.name,
                            "code": household.code
                        }
                    })
                });
            }
        }, "json");
    </script>

</body>

</html>