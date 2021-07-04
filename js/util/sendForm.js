function send(form, controller, resource = "store") {
  var errors = {};

  $(form).find(".error-msgs").html("");

  $(form)
    .find("input[required], textarea[required]")
    .each(function () {
      if (!$(this).val()) {
        var inputName = $(this).attr("name");

        if (errors[inputName] == undefined) errors[inputName] = [];

        errors[inputName].push(`${inputName} field is required`);
      }
    });

  $(form)
    .find("input[minlength], textarea[minlength]")
    .each(function () {
      var min = $(this).attr("minlength");
      if ($(this).val().length < min) {
        var inputName = $(this).attr("name");

        if (errors[inputName] == undefined) errors[inputName] = [];

        errors[inputName].push(
          `${inputName} field requires minimum length of ${min}`
        );
      }
    });

  $(form)
    .find("input[maxlength], textarea[maxlength]")
    .each(function () {
      var max = $(this).attr("maxlength");
      if ($(this).val().length > max) {
        var inputName = $(this).attr("name");

        if (errors[inputName] == undefined) errors[inputName] = [];

        errors[inputName].push(
          `${inputName} field requires maximum length of ${max}`
        );
      }
    });

  $(form)
    .find("input[numerical]")
    .each(function () {
      if (isNaN($(this).val())) {
        var inputName = $(this).attr("name");

        if (errors[inputName] == undefined) errors[inputName] = [];

        errors[inputName].push(`${inputName} field requires a numerical value`);
      }
    });

  var errorKeys = Object.keys(errors);

  if (errorKeys.length == 0) {
    $.post(
      `../routes/route.php?controller=${controller}&resource=${resource}`,
      $(form).serializeArray(),
      "json"
    ).done(function (res) {
      var output = JSON.parse(res);
      triggerStatus(output.status, output.message);
      if (output.redir != undefined) {
        if (output.redir == "back") {
          window.history.back();
        } else {
          window.location.href = output.redir;
        }
      }
      if (typeof successSent === "function") successSent(output);
    });
  }

  errorKeys.forEach((errorKey) => {
    errors[errorKey].forEach((err) => {
      $(`.error-msgs[for=${errorKey}]`).append(`<span>${err}</span>`);
    });
  });
  return false;
}
