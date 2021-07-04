class DestroyButton {
  constructor(bindElement, controller, data) {
    this.controller = controller;
    this.data = data;
    this.bindElement = $(bindElement);
    this.component = $(`
    <button class="button" id="delete">
        <i class="far fa-trash-alt"></i>
        <div class="hidden">
            Delete ${controller}
        </div>
    </button>          
          `);

    this.component.click(this.destroyClick.bind(this));
    this.bindElement.append(this.component);
  }

  destroyClick() {
    var postObject = {};
    postObject[this.controller] = this.data.id;
    $.post(
      `../routes/route.php?controller=${this.controller}&resource=destroy`,
      postObject,
      "json"
    ).done(
      $.proxy(function (res) {
        var output = JSON.parse(res);
        triggerStatus(output.status, output.message);
        if (output.redir != undefined) {
          if (output.redir != "back") {
            window.history.back();
          } else {
            window.location.href = output.redir;
          }
        } else {
          window.history.back();
        }
      }, this)
    );
  }
}
