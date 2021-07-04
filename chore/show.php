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
            <h2>Chore: <span></span></h2>
            <div class="buttons">


            </div>
        </div>
        <div class="components">
            <div class="component" id="chore-info">
                <div class="component-top">
                    <h2><span class="blue-accent"></span></h2>
                </div>
                <div class="info-component">

                </div>

            </div>
            <div class="component" id="chore-images">
                <div class="component-top">
                    <h2>Photos for <span class="blue-accent"></span></h2>
                </div>
                <div class="images-component">

                </div>

            </div>
        </div>
    </div>
    <script src="../js/components/listComponent.js"></script>
    <script src="../js/components/destroyButton.js"></script>
    <script src="../js/components/completeButton.js"></script>
    <script type="application/javascript">
        // https://www.sitepoint.com/get-url-parameters-with-javascript/
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if (!urlParams.has('chore')) {
            triggerStatus("error", "Chore not provided!");
            window.history.back();
        }
        const chore = urlParams.get('chore');


        $.get(`../routes/route.php?controller=chore&resource=show&chore=${chore}`, function(data) {
            if (data.status !== undefined)
                triggerStatus(data.status, data.message);

            if (data.redir != undefined) {
                if (data.redir == "back") {
                    window.history.back();
                } else {
                    window.location.href = data.redir;
                }
            }

            if (data.chore !== undefined) {
                var frequencies = {
                    0: "one-time",
                    1: "every day",
                    2: "every week",
                    3: "every fortnight"
                }
                var statuses = {
                    0: "Upcoming",
                    1: "Pending",
                    2: "Complete"
                }
                $(".component#chore-info .component-top .blue-accent").text(data.chore.name);
                $(".component#chore-images .component-top .blue-accent").text(data.chore.name);
                $(".top h2 span").text(data.chore.name);
                var componentInfo = $(".component#chore-info .info-component");
                componentInfo.append(`<span class="main-text">${data.chore.description}</span>`);
                componentInfo.append(`<span class="sub-text">Flatmate: ${data.chore.flatmate}</span>`);
                componentInfo.append(`<span class="sub-text">Household: ${data.chore.h_name}</span>`);
                componentInfo.append(`<span class="sub-text">Duration: ${data.chore.duration} minutes</span>`);
                componentInfo.append(`<span class="sub-text">Frequency: ${frequencies[data.chore.frequency]}</span>`);
                componentInfo.append(`<span class="sub-text">Status: ${statuses[data.chore.status]}</span>`);

                var componentImages = $(".component#chore-images .images-component");

                data.photos.forEach(photo => {
                    console.log(photo.image);

                    componentImages.append(`<div class="img-wrapper"><img src="${photo.image}" alt="${photo.imageName}"></div>`)
                });
                if (data.chore.privilages > 0) {

                    new DestroyButton(".top .buttons", "chore", {
                        "id": data.chore.id
                    });
                }
            }


        }, "json");

        function createListItem(form) {
            var todo = $(form).serializeArray().filter(inp => inp.name == "todo")[0].value;
            send(form, "listitem")
            return false;
        }
    </script>
</body>

</html>