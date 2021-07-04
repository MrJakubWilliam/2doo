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
        <div class="create-form">
            <h2>Create a new chore</h2>
            <form novalidate onsubmit="return sendForm(this)">
                <div class="form-element">
                    <label for="name">Chore name: </label>
                    <input type="text" id="name" name="name" value="" required minlength="1" maxlength="100">
                    <div for="name" class="error-msgs"></div>
                </div>

                <div class="form-element">
                    <label for="description">Chore description</label>
                    <textarea id="description" name="description" rows="4" cols="50" maxlength="1000"></textarea>
                    <div for="description" class="error-msgs"></div>
                </div>

                <div class="form-element">
                    <label for="frequency">Chore frequency</label>
                    <select name="frequency" id="frequency">
                        <option value="0" selected>One time</option>
                        <option value="1">Every day</option>
                        <option value="2">Every week</option>
                        <option value="3">Every fortnight</option>
                    </select>
                </div>

                <div class="form-element">
                    <label for="duration">Chore duration:</label>
                    <input type="range" id="duration" name="duration" min="5" max="60" value="5" step="5" oninput="showRangeVal(this.value)">
                    <p id="durationValue"><span id="mins">5</span> minutes</p>
                </div>

                <div class="form-element">
                    <label for="start">Chore start date:</label>
                    <input type="date" id="start" name="start" required>
                    <div for="start" class="error-msgs"></div>
                </div>
                <div class="form-element">
                    <label for="household">Household: </label>
                    <select name="household" id="household" onchange="getFlatmates(this.value)"></select>
                </div>

                <div class="form-element">
                    <label for="flatmate">Flatmate: </label>
                    <select name="flatmate" id="flatmate"></select>
                </div>

                <input type="submit" name="" class="button blue-bg" value="Create">
            </form>
        </div>
    </div>
    <script type="application/javascript">
        function sendForm(form) {
            send(form, "chore");
            return false;
        }

        $.get('../routes/route.php?controller=household&resource=index', function(data) {
            if (!data.length) {
                window.history.back();
            } else {
                data.forEach(household => {
                    $("form #household").append(new Option(household.name, household.id));
                });

                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                if (urlParams.has('household')) {
                    var hId = urlParams.get("household");
                    if (data.some(h => h.id == hId)) {
                        $("form #household").val(hId);
                    }
                }

                const householdId = urlParams.get('household');

                var household = $('.form-element #household');
                if (householdId != null) {
                    household.val(householdId);
                    getFlatmates(householdId);
                } else {
                    var firstHousehold = $(".form-element #household option:first");
                    $(".form-element #household").val(firstHousehold.val());
                    getFlatmates(firstHousehold.val());
                }
                household.on("change", function(e) {
                    getFlatmates($(e.target).val());
                });
            }
        }, "json");





        var flatmateSelect = $('.form-element #flatmate');
        var durationIndicator = $('.form-element #durationValue #mins');

        // https://stackoverflow.com/questions/32378590/set-date-input-fields-max-date-to-today
        var today = new Date();
        var day = today.getDate();
        var month = today.getMonth() + 1;
        var year = today.getFullYear();
        if (day < 10) {
            day = '0' + day
        }
        if (month < 10) {
            month = '0' + month
        }
        today = year + '-' + month + '-' + day;
        // ------------------------------------------

        $(".form-element #start").attr("min", today);

        function showRangeVal(val) {
            durationIndicator.text(val);
        }

        function getFlatmates(h) {
            $.get(`../routes/route.php?controller=flatmate&resource=index&household=${ h }`, function(data) {
                flatmateSelect.find('option').remove();
                flatmateSelect.append(new Option("Random but fair allocation", 0));
                data.forEach(flatmate => {
                    flatmateSelect.append(new Option(flatmate["u_name"], flatmate["u_id"]));
                });
            }, "json");
        }
    </script>
</body>

</html>