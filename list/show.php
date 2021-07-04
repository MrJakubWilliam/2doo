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
            <h2>List: <span></span></h2>
            <div class="buttons">

            </div>
        </div>
        <div class="components">
            <div class="component">
                <div class="component-top">
                    <h2>2doos in <span class="blue-accent"></span></h2>
                </div>

                <form novalidate onsubmit="return createListItem(this)" id="list-item-create">

                    <div class="form-element">
                        <label for="todo">New 2doo: </label>
                        <input type="text" id="todo" name="todo" value="">
                    </div>
                    <input type="hidden" name="list" required>
                    <input type="submit" name="" class="button blue-bg" value="Add">
                </form>
                <div class="list-components">
                </div>

            </div>
        </div>
    </div>
    <script src="../js/components/listComponent.js"></script>
    <script src="../js/components/destroyButton.js"></script>
    <script src="../js/components/completeButton.js"></script>

    <script type="application/javascript">
            $(".list-components").on("empty", function() {
                $(this).append('<span class="grayed-out">No lists to show</span>');
            });

        // // https://www.sitepoint.com/get-url-parameters-with-javascript/
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if (!urlParams.has('list')) {
            triggerStatus("error", "List not provided!");
            window.history.back();
        }
        const list = urlParams.get('list');


        $.get(`../routes/route.php?controller=list&resource=show&list=${list}`, function(data) {
            if (data.status !== undefined)
                triggerStatus(data.status, data.message);

            if (data.redir != undefined) {
                if (data.redir == "back") {
                    window.history.back();
                } else {
                    window.location.href = data.redir;
                }
            }

            if (data.list !== undefined) {
                $(".component .component-top .blue-accent").text(data.list.name);
                $(".top h2 span").text(data.list.name);
                $(".component form input[name='list']").val(data.list.id);

                // create buttons
                new DestroyButton(".top .buttons", "list", {
                    "id": data.list.id
                });
            }

            if (data.listItems !== undefined) {

                if (!data.listItems.length) {
                    $(".list-components").append('<span class="grayed-out">No lists to show</span>');
                } else {
                    data.listItems.forEach(listItem => {
                        var todo = new ListComponent(".list-components", "listitem", ["destroy"], {
                            "id": listItem.id,
                            "printable": {
                                0: listItem.content
                            }
                        });
                        if (listItem.checked == 1) {
                            todo.component.addClass("checked");
                        }

                        new CompleteButton(todo.component.find(".buttons"), {
                            "id": listItem.id
                        });
                    });
                }
            }
        }, "json");

        function createListItem(form) {
            var todo = $(form).serializeArray().filter(inp => inp.name == "todo")[0].value;
            send(form, "listitem")
            return false;
        }

        function successSent(output) {
            if (output.listItem !== undefined) {
                var todo = new ListComponent(".list-components", "listitem", ["destroy"], {
                    "id": output.listItem.id,
                    "printable": {
                        0: output.listItem.content
                    }
                });

                new CompleteButton(todo.component.find(".buttons"), {
                    "id": output.listItem.id
                });
            }
        }
    </script>
</body>

</html>