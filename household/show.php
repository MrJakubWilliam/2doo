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
            <div>
                <h2>Household: <span></span></h2>
                <span class="code">Code: <span></span></span>
            </div>
            <div class="buttons">

                <a class="button" id='add'>
                    <i class="fas fa-plus"></i>
                    <div class="hidden">
                        Add chore
                    </div>
                </a>
                <a class="button" id='edit'>
                    <i class="fas fa-cog"></i>
                    <div class="hidden">
                        Edit household
                    </div>
                </a>

            </div>
        </div>
        <div class="components">
            <div class="component">
                <div class="component-top">
                    <h2>All chores</h2>
                </div>
                <div class="list-components" for="chores">
                </div>
            </div>

            <div class="component">
                <div class="component-top">
                    <h2>My chores </h2>
                </div>
                <div class="list-components" for="mychores">
                </div>
            </div>

            <div class="component">
                <div class="component-top">
                    <h2>Flatmates</h2>
                </div>
                <div class="list-components" for="flatmates">
                </div>
            </div>


            <div class="component hidden">
                <div class="component-top">
                    <h2>Join requests</h2>
                </div>
                <div class="list-components" for="joinrequests">
                </div>
            </div>



        </div>


    </div>
    <script src="../js/components/listComponentPrivilaged.js"></script>
    <script src="../js/components/destroyButton.js"></script>
    <script src="../js/components/acceptButton.js"></script>
    <script src="../js/components/completeChoreButton.js"></script>

    <script type="application/javascript">
        var choresComponent = $(".list-components[for='chores']");
        var myChoresComponent = $(".list-components[for='mychores']");
        var joinRequestsComponent = $(".list-components[for='joinrequests']");
        var flatmatesComponent = $(".list-components[for='flatmates']");


        choresComponent.on("empty", function() {
            $(this).append('<span class="grayed-out">No chores to show</span>');
        });

        myChoresComponent.on("empty", function() {
            $(this).append('<span class="grayed-out">No chores to show</span>');
        });

        joinRequestsComponent.on("empty", function() {
            $(this).closest(".component").remove();
        });

        // https://www.sitepoint.com/get-url-parameters-with-javascript/
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if (!urlParams.has('household')) {
            triggerStatus("error", "Household not provided!");
            window.history.back();
        }
        const household = urlParams.get('household');


        $.get(`../routes/route.php?controller=household&resource=show&household=${household}`, function(data) {
            if (data.status !== undefined)
                triggerStatus(data.status, data.message);

            if (data.redir != undefined) {
                if (data.redir == "back") {
                    window.history.back();
                } else {
                    window.location.href = data.redir;
                }
            }

            if (data.household !== undefined) {
                $(".top h2 span").text(data.household.name);
                $(".top .code span").text(data.household.code);

                if (data.household.privilages > 0) {
                    $(".top .buttons .button#add").attr("href", `../chore/create.php?household=${data.household.id}`);
                } else {
                    $(".top .buttons .button#add").remove();
                }
                if (data.household.privilages == 2) {
                    $(".top .buttons .button#edit").attr("href", `../household/edit.php?household=${data.household.id}`);
                    new DestroyButton(".top .buttons", "household", {
                        "id": data.household.id
                    });
                } else {
                    $(".top .buttons .button#edit").remove();
                }

            }

            $.get(`../routes/route.php?controller=chore&resource=index&household=${household}`, function(datac) {
                var chores = {};
                datac.forEach(chore => {
                    if (chores[chore.status] === undefined) {
                        chores[chore.status] = [];
                    }
                    chores[chore.status].push(chore);
                });

                if (!Object.keys(chores).length) {
                    choresComponent.append('<span class="grayed-out">No chores to show</span>');
                    myChoresComponent.append('<span class="grayed-out">No chores to show</span>');
                } else {

                    var statuses = {
                        0: "Upcoming",
                        1: "Pending",
                        2: "Complete"
                    }

                    var frequencies = {
                        0: "one-time",
                        1: "every day",
                        2: "every week",
                        3: "every fortnight"
                    }
                    for (choreStatus in chores) {
                        choresComponent.append(`<span class="descriptor">${statuses[choreStatus]}</span>`);
                        myChoresComponent.append(`<span class="descriptor">${statuses[choreStatus]}</span>`);


                        chores[choreStatus].forEach(chore => {
                            var listComponent = new ListComponentPrivilaged(choresComponent, "chore", {
                                "show": 0,
                                "edit": 1,
                                "destroy": 1,
                            }, {
                                "id": chore.ca_id,
                                "privilages": data.household.privilages,
                                "printable": {
                                    "chore": chore.name,
                                    "flatmate": chore.u_name,
                                    "frequency": frequencies[chore.frequency],
                                    "duration": chore.duration + " minutes",
                                    "date": chore.date_complete_by
                                }
                            });

                            if (chore.status == 1) {
                                listComponent.component.addClass("pending");
                            };

                            if (chore.status == 0) {
                                listComponent.component.addClass("upcoming");
                            };

                            if (chore.status == 2) {
                                listComponent.component.addClass("complete");
                            };

                            if (data.user == chore.u_id && chore.status != 2) {
                                new CompleteChoreWithImagesButton(listComponent.component.find(".buttons"), {
                                    "userId": data.user,
                                    "flatmateId": chore.u_id,
                                    "choreId": chore.ca_id
                                });
                                new CompleteChoreButton(listComponent.component.find(".buttons"), {
                                    "userId": data.user,
                                    "flatmateId": chore.u_id,
                                    "choreId": chore.ca_id
                                });
                            }
                            if (data.user == chore.u_id) {
                                var listComponent = new ListComponentPrivilaged(myChoresComponent, "chore", {
                                    "show": 0,
                                    "edit": 1,
                                    "destroy": 1
                                }, {
                                    "id": chore.ca_id,
                                    "privilages": data.household.privilages,
                                    "printable": {
                                        "chore": chore.name,
                                        "frequency": frequencies[chore.frequency],
                                        "duration": chore.duration + " minutes",
                                        "date": chore.date_complete_by
                                    }
                                });
                                if (chore.status == 1) {
                                    listComponent.component.addClass("pending");
                                };

                                if (chore.status == 0) {
                                    listComponent.component.addClass("upcoming");
                                };

                                if (chore.status == 2) {
                                    listComponent.component.addClass("complete");
                                };

                                if (chore.status != 2) {

                                    new CompleteChoreWithImagesButton(listComponent.component.find(".buttons"), {
                                        "userId": data.user,
                                        "flatmateId": chore.u_id,
                                        "choreId": chore.ca_id
                                    });
                                    new CompleteChoreButton(listComponent.component.find(".buttons"), {
                                        "userId": data.user,
                                        "flatmateId": chore.u_id,
                                        "choreId": chore.ca_id
                                    });
                                }
                            }
                        });
                    }
                }
            }, "json");

            $.get(`../routes/route.php?controller=flatmate&resource=index&household=${household}`, function(dataf) {
                var flatmates = {};
                dataf.forEach(flatmate => {
                    if (flatmates[flatmate.privilages] === undefined) {
                        flatmates[flatmate.privilages] = [];
                    }
                    flatmates[flatmate.privilages].push(flatmate);
                });

                var privilages = {
                    0: "standard",
                    1: "admin",
                    2: "super admin",
                }

                for (privilagesLevel in flatmates) {
                    flatmatesComponent.append(`<span class="descriptor">${privilages[privilagesLevel]}</span>`);

                    flatmates[privilagesLevel].forEach(flatmate => {

                        var hours = Math.floor(flatmate.duration_worked / 60);
                        var minutes = flatmate.duration_worked % 60;

                        new ListComponentPrivilaged(flatmatesComponent, "flatmate", {
                            "destroy": 2
                        }, {
                            "id": flatmate.id,
                            "privilages": data.household.privilages,
                            "printable": {
                                "name": flatmate.u_name,
                                "time worked": `${hours} hours and ${minutes} minutes`,
                                "date joined": flatmate.date_time_joined
                            }
                        });
                    });
                }
            }, "json");

            $.get(`../routes/route.php?controller=joinrequest&resource=index&household=${household}`, function(datar) {
                if (datar.length > 0) {
                    joinRequestsComponent.closest(".component").removeClass("hidden")
                }
                datar.forEach(joinrequest => {


                    var listComponent = new ListComponentPrivilaged(joinRequestsComponent, "joinrequest", {
                        "destroy": 1
                    }, {
                        "id": joinrequest.id,
                        "privilages": data.household.privilages,
                        "printable": {
                            "name": joinrequest.name,
                            "email": joinrequest.email
                        }
                    });

                    new AcceptButton(listComponent.component.find(".buttons"), {
                        "id": joinrequest.id,
                        "privilages": data.household.privilages
                    });
                });
            }, "json");
        }, "json");
    </script>
</body>

</html>