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
            <h2>Edit chore <span class="blue-accent"></span></h2>
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
                    <div for="date" class="error-msgs"></div>
                </div>

                <div class="form-element">
                    <label for="flatmate">Flatmate: </label>
                    <select name="flatmate" id="flatmate"></select>
                </div>

                <input type="hidden" id="choreId" name="choreId" value="">
                <input type="hidden" id="household" name="household" value="">


                <input type="submit" name="" class="button blue-bg" value="Create">
            </form>
        </div>
    </div>
    <script type="application/javascript">
            function sendForm(form) {
                send(form, "chore", "update");
                return false;
            }


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

        // // https://www.sitepoint.com/get-url-parameters-with-javascript/
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
                $(".create-form h2 span").text(data.chore.name);
                var form = $("form");
                form.find("#name").val(data.chore.name);
                form.find("#description").val(data.chore.description);
                form.find("#frequency").val(data.chore.frequency);
                form.find("#duration").val(data.chore.duration);
                showRangeVal(data.chore.duration);
                form.find("#start").val(data.chore.date_complete_by);

                $.get(`../routes/route.php?controller=flatmate&resource=index&household=${ data.chore.h_id }`, function(dataf) {
                    flatmateSelect.find('option').remove();

                    dataf.forEach(flatmate => {
                        flatmateSelect.append(new Option(flatmate["u_name"], flatmate["u_id"]));
                    });
                    form.find("#flatmate").val(data.chore.f_id);

                }, "json");
                form.find("#choreId").val(data.chore.c_id);
                form.find("#household").val(data.chore.h_id);
            }


        }, "json");
    </script>
</body>

</html>