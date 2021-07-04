class CompleteButton {
  constructor(bindElement, data) {
    this.data = data;
    this.bindElement = bindElement;

    this.component = $(`
        <button>
            <i class="fas fa-check"></i>
        </button>      
            `);

    this.component.click(this.checkClick.bind(this));
    this.bindElement.prepend(this.component);
  }

  checkClick() {
    var postObject = {};
    postObject["listitem"] = this.data.id;
    $.post(
      `../routes/route.php?controller=listitem&resource=complete`,
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
          this.bindElement.closest(".list-component").toggleClass("checked");
        }
      }, this)
    );
  }
}
