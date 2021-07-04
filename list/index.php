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
            <h2>Lists</h2>
            <div class="buttons">
                <a href="../routes/route.php?controller=list&resource=create" class="button" id='add'>
                    <i class="fas fa-plus"></i>
                    <div class="hidden">
                        Add list
                    </div>
                </a>

            </div>
        </div>
        <div class="components">
            <div class="component">
                <div class="component-top">
                    <h2>My lists</h2>

                </div>
                <div class="list-components">

                </div>
            </div>
        </div>
    </div>
    <script src="../js/components/listComponent.js"></script>
    <script type="application/javascript">
        $(".list-components").on("empty", function() {
            $(this).append('<span class="grayed-out">No lists to show</span>');
        });

        $.get('../routes/route.php?controller=list&resource=index', function(data) {
            if (!data.length) {

                $(".list-components").append('<span class="grayed-out">No lists to show</span>');
            } else {
                data.forEach(list => {
                    new ListComponent(".list-components", "list", ["show", "destroy"], {
                        "id": list.id,
                        "printable": {
                            "category": list.c_name,
                            "name": list.name
                        }
                    })
                });
            }
        }, "json");
    </script>

</body>

</html>