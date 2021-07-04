class EditFlatmate {
  constructor(bindElement, data) {
    this.data = data;
    this.component = $(` 
    <div class="edit-flatmate">
          <span class="flatmate-name"></span>
          <div class="buttons">
              <div class="privilage-buttons">
                  <div class="pill"></div>
                  <button class="privilage-button" privilage="0">
                      Standard
                  </button>
                  <button class="privilage-button" privilage="1">
                      Admin
                  </button>
              </div>
              <button class="button" id="delete">
                  <i class="far fa-trash-alt"></i>
              </button>
          </div>
      </div>
      `);

    this.events();
    $(bindElement).append(this.component);
    this.boot();
  }

  events() {
    this.component
      .find(".privilage-button")
      .mouseover(this.privilageButtonMouseOver);
    this.component
      .find(".privilage-button")
      .mouseout(this.privilageButtonMouseOut);
    this.component
      .find(".privilage-button")
      .bind(
        "click",
        { id: this.data.id, household: this.data.household },
        this.privilageButtonClick
      );
    this.component.find("#delete").click(this.destroyFlatmate.bind(this));
  }

  boot() {
    this.nameBoot();
    this.privilageBoot();
  }

  nameBoot() {
    this.component.find(".flatmate-name").text(this.data.name);
  }

  privilageBoot() {
    var editFlatmate = this.component;
    var pill = editFlatmate.find(".pill");
    var privilageButton = this.component.find(
      `.privilage-button[privilage=${this.data.privilages}]`
    );
    privilageButton.css("background-color", "inherit");
    editFlatmate.find(".privilage-button").removeClass("highlighted");
    privilageButton.addClass("highlighted");
    pill.css("width", privilageButton.outerWidth());
    pill.css("left", privilageButton.position().left);
  }

  privilageButtonMouseOver() {
    if (!$(this).hasClass("highlighted")) {
      $(this).css("background-color", "#292828");
    }
  }

  privilageButtonMouseOut() {
    if (!$(this).hasClass("highlighted")) {
      $(this).css("background-color", "inherit");
    }
  }

  privilageButtonClick(e) {
    var editFlatmate = $(this).closest(".edit-flatmate");
    var pill = editFlatmate.find(".pill");
    var privilageButton = $(this);

    var postObject = {
      flatmate: e.data.id,
      household: e.data.household,
      privilage: $(this).attr("privilage"),
    };
    $.post(
      "../routes/route.php?controller=flatmate&resource=updateprivilage",
      postObject,
      function (data) {
        if (data.status == "success") {
          privilageButton.css("background-color", "inherit");
          editFlatmate.find(".privilage-button").removeClass("highlighted");
          privilageButton.addClass("highlighted");
          pill.css("width", privilageButton.outerWidth());
          pill.css("left", privilageButton.position().left);
        }
      },
      "json"
    );
  }

  destroyFlatmate() {
    $.post(
      "../routes/route.php?controller=flatmate&resource=destroy",
      {
        flatmate: this.data.id,
      },
      "json"
    ).done(
      $.proxy(function (res) {
        this.component.css("display", "none");
      }, this)
    );
  }
}
