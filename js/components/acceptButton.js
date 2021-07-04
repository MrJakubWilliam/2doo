class AcceptButton {
  constructor(bindElement, data) {
    this.data = data;
    this.bindElement = bindElement;

    this.component = $(`
        <button>
            <i class="fas fa-plus"></i>
        </button>      
              `);

    this.component.click(this.acceptClick.bind(this));
    this.bindElement.prepend(this.component);
  }

  acceptClick() {
    if (this.data.privilages < 1) {
      triggerStatus("error", "You don't have privilages for this operation");
    } else {
      var postObject = {
        request: this.data.id,
      };
      $.post(
        `../routes/route.php?controller=joinrequest&resource=accept`,
        postObject,
        "json"
      ).done(
        $.proxy(function (res) {
          var output = JSON.parse(res);
          triggerStatus(output.status, output.message);
          if (output.redir != undefined) {
            if (output.redir == "back") {
              window.history.back();
            } else {
              window.location.href = output.redir;
            }
          }
          if (output.status == "success") {
            location.reload();
          }
        }, this)
      );
    }
  }
}
