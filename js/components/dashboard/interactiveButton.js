class InteractiveButton {
  constructor(bindElement, data) {
    this.data = data;
    this.bindElement = $(bindElement);
    this.component = $(`
      <button class="button" for='${this.data.id}'></button>   
              `);

    this.component.text(this.data.name);
    this.component.click(this.clickEvent.bind(this));
    this.bindElement.prepend(this.component);
  }

  clickEvent() {
    var list_items = $(
      `.list-components .list-component[${this.data.controller}=${this.data.id}]`
    );

    this.component.toggleClass("clicked");
    list_items.each(function () {
      $(this).toggleClass("hidden");
    });
  }
}
