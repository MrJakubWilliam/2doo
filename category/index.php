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
            <h2>Categories</h2>
            <div class="buttons">
                <a href="./create.php" class="button" id='add'>
                    <i class="fas fa-plus"></i>
                    <div class="hidden">
                        Add category
                    </div>
                </a>

            </div>
        </div>
        <div class="components">
            <div class="component">
                <div class="component-top">
                    <h2>My Categories</h2>
                </div>
                <div class="list-components">
                </div>
            </div>
        </div>
    </div>
    <script src="../js/components/listComponent.js"></script>
    <script type="application/javascript">
        $(".list-components").on("empty", function() {
            $(this).append('<span class="grayed-out">No categories to show</span>');
        });

        $.get('../routes/route.php?controller=category&resource=index', function(data) {
            if (!data.length) {

                $(".list-components").append('<span class="grayed-out">No categories to show</span>');
            } else {
                data.forEach(category => {
                    new ListComponent(".list-components", "category", ["show", "destroy"], {
                        "id": category.id,
                        "printable": {
                            "name": category.name
                        }
                    })
                });
            }
        }, "json");
    </script>
</body>

</html>