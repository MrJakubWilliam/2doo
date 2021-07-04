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
            <h2>Dashboard</h2>
        </div>
        <div class="components">
            <div class="component" id="categories-component">
                <div class="component-top">
                    <h2>My Lists</h2>
                </div>
                <div class="category-buttons">
                    <a href="../category/create.php" class="button" id='add'>
                        <i class="fas fa-plus"></i>
                        <div class="hidden">
                            Add category
                        </div>
                    </a>
                </div>
                <div class="list-components">
                </div>
            </div>
            <div class="component" id="households-component">
                <div class="component-top">
                    <h2>My Upcoming & Pending Chores</h2>
                </div>
                <div class="category-buttons">
                    <a href="../household/create.php" class="button" id='add'>
                        <i class="fas fa-plus"></i>
                        <div class="hidden">
                            Add household
                        </div>
                    </a>
                </div>
                <div class="list-components">
                </div>
            </div>
        </div>
    </div>
    <script src="../js/components/dashboard/interactiveButton.js"></script>
    <script src="../js/components/listComponent.js"></script>
    <script src="../js/components/listComponentPrivilaged.js"></script>

    <script type="application/javascript">
            $.get('../routes/route.php?controller=category&resource=index', function(data) {

                data.forEach(category => {
                    new InteractiveButton("#categories-component .category-buttons", {
                        "id": category.id,
                        "name": category.name,
                        "controller": "category"
                    });
                });
            }, "json");

        $.get('../routes/route.php?controller=list&resource=index', function(data) {

            data.forEach(list => {
                var listComponent = new ListComponent("#categories-component .list-components", "list", ["show", "destroy"], {
                    "id": list.id,
                    "printable": {
                        "category": list.c_name,
                        "name": list.name
                    }
                });
                listComponent.component.addClass("hidden");
                listComponent.component.attr("category", list.category_id);

            });
        }, "json");

        $.get('../routes/route.php?controller=household&resource=index', function(data) {

            data.forEach(household => {
                new InteractiveButton("#households-component .category-buttons", {
                    "id": household.id,
                    "name": household.name,
                    "controller": "household"
                });
            });
        }, "json");

        $.get('../routes/route.php?controller=chore&resource=index', function(data) {
            data.forEach(chore => {
                if (chore.status != 2) {

                    var listComponent = new ListComponentPrivilaged("#households-component .list-components", "chore", {
                        "show": 0,
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
                    listComponent.component.addClass("hidden");
                    listComponent.component.attr("household", chore.h_id);
                    if (chore.status == 0) {
                        listComponent.component.addClass("upcoming");
                    }
                    if (chore.status == 1) {
                        listComponent.component.addClass("pending");
                    }

                }
            });
        }, "json");
    </script>
</body>

</html>