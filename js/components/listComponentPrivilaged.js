class ListComponentPrivilaged {
  constructor(bindElement, controller, resources, data) {
    this.resources = resources;
    this.controller = controller;
    this.data = data;
    this.bindElement = $(bindElement);
    this.component = $(`
          <div class="list-component">
              <div class="text">
              </div>
              <div class="buttons">
              </div>
          </div>            
          `);

    this.addButtons();
    this.addText();
    this.events();
    this.bindElement.append(this.component);
  }

  addButtons() {
    var buttons = this.component.find(".buttons");
    var resources = Object.keys(this.resources);
    if (
      resources.includes("show") &&
      this.data.privilages >= this.resources["show"]
    ) {
      buttons.append(
        $(`
          <a id="show" href="../${this.controller}/show.php?${this.controller}=${this.data.id}">
              <i class="fas fa-binoculars"></i>
          </a>
          `)
      );
    }
    if (
      resources.includes("edit") &&
      this.data.privilages >= this.resources["edit"]
    ) {
      buttons.append(
        $(`
          <a id="edit" href="../${this.controller}/edit.php?${this.controller}=${this.data.id}">
              <i class="fas fa-cog"></i>
          </a>
            `)
      );
    }
    if (
      resources.includes("destroy") &&
      this.data.privilages >= this.resources["destroy"]
    ) {
      buttons.append(
        $(`
          <button id="destroy" class="delete">
              <i class="far fa-trash-alt"></i>
          </button>
            `)
      );
    }
  }

  addText() {
    var text = this.component.find(".text");
    for (let item in this.data.printable) {
      if (!isNaN(item)) {
        text.append(`<span>${this.data.printable[item]}</span>`);
      } else {
        text.append(`<span>${item} : ${this.data.printable[item]}</span>`);
      }
    }
  }

  events() {
    this.component
      .find(".buttons #destroy")
      .click(this.destroyClick.bind(this));
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
        this.component.remove();
        var output = JSON.parse(res);
        triggerStatus(output.status, output.message);
        if (this.bindElement.children().length == 0) {
          this.bindElement.trigger("empty");
        }

        if (output.redir != undefined) {
          if (output.redir == "back") {
            window.history.back();
          } else if (
            window.location.href !=
            this.absolute(window.location.href, output.redir)
          ) {
            window.location.href = output.redir;
          }
        }
      }, this)
    );
  }

  //   https://stackoverflow.com/questions/14780350/convert-relative-path-to-absolute-using-javascript
  absolute(base, relative) {
    var stack = base.split("/"),
      parts = relative.split("/");
    stack.pop();
    for (var i = 0; i < parts.length; i++) {
      if (parts[i] == ".") continue;
      if (parts[i] == "..") stack.pop();
      else stack.push(parts[i]);
    }
    return stack.join("/");
  }
}
