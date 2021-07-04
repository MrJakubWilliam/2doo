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
            <h2>Category: <span></span></h2>
            <div class="buttons">
                <a class="button" id='add'>
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
                    <h2>Lists in <span class="blue-accent"></span></h2>

                </div>
                <div class="list-components">
                </div>
            </div>
        </div>
    </div>
    <script src="../js/components/listComponent.js"></script>
    <script src="../js/components/destroyButton.js"></script>
    <script type="application/javascript">
        $(".list-components").on("empty", function() {
            $(this).append('<span class="grayed-out">No lists to show</span>');
        });

        // https://www.sitepoint.com/get-url-parameters-with-javascript/
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if (!urlParams.has('category')) {
            triggerStatus("error", "Category not provided!");
            window.history.back();
        }
        const category = urlParams.get('category');


        $.get(`../routes/route.php?controller=category&resource=show&category=${category}`, function(data) {
            if (data.status !== undefined)
                triggerStatus(data.status, data.message);

            if (data.redir != undefined) {
                if (data.redir == "back") {
                    window.history.back();
                } else {
                    window.location.href = data.redir;
                }
            }

            if (data.category !== undefined) {
                $(".component .component-top .blue-accent").text(data.category.name);
                $(".top h2 span").text(data.category.name);

                $(".top .buttons .button#add").attr("href", `../list/create.php?category=${data.category.id}`);
                // create buttons
                new DestroyButton(".top .buttons", "category", {
                    "id": data.category.id
                });
            }

            if (data.lists !== undefined) {

                if (!data.lists.length) {
                    $(".list-components").append('<span class="grayed-out">No lists to show</span>');
                } else {
                    data.lists.forEach(list => {
                        new ListComponent(".list-components", "list", ["show", "destroy"], {
                            "id": list.id,
                            "printable": {
                                "name": list.name
                            }
                        })
                    });
                }
            }
        }, "json");
    </script>
</body>

</html>