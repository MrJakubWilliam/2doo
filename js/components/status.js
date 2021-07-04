function triggerStatus(status, message) {
  var statusMsg = $(`
    <div class="status-msg ${status}">
        <div class="msg"></div>
        <div class="close">
            <i class="fas fa-times"></i>
        </div>
    </div>
    `);
  statusMsg.find(".msg").text(message);
  statusClose = statusMsg.find(".close");
  $(".container").append(statusMsg);
  statusClose.click(function () {
    statusMsg.addClass("hidden");
  });
}
