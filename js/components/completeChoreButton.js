class CompleteChoreWithImagesButton {
  constructor(bindElement, data) {
    this.data = data;
    this.bindElement = bindElement;
    this.component = $(`
            <div class='img-upload'>
                    <label for='img'><i class="fas fa-images"></i></label>
                    <input type='file' id='img' name='img[]' multiple accept="image/x-png,image/jpeg" >
            </div>  
            `);
    this.component.find("input").change(this.inputChange.bind(this));
    this.bindElement.prepend(this.component);
  }

  inputChange() {
    var files = this.component.find("input").prop("files");
    var filesString = "";
    var errorFlag = false;
    if (files.length > 5) {
      triggerStatus("error", "maximum number of files is 5!");
      errorFlag = true;
    } else {
      // https://stackoverflow.com/questions/40902437/cant-use-foreach-with-filelist

      Array.prototype.forEach.call(
        files,
        function (file) {
          filesString += file.name + ", ";
          if (file.size > 500000) {
            triggerStatus("error", "maximum size of file is 0.5mb");
            errorFlag = true;
          }
        }.bind(this)
      );

      if (!errorFlag) {
        triggerStatus("info", `Uploading ${filesString}!`);

        var data = new FormData();
        $.each(this.component.find("input")[0].files, function (i, file) {
          data.append("photo" + i, file);
        });
        data.append("chore", this.data.choreId);

        $.ajax({
          url: "../routes/route.php?controller=chore&resource=complete",
          data: data,
          cache: false,
          contentType: false,
          processData: false,
          method: "POST",
          type: "POST",
          dataType: "json",
          success: function (data) {
            triggerStatus(data.status, data.message);
            location.reload();
          },
        });
      }
    }
  }
}

class CompleteChoreButton {
  constructor(bindElement, data) {
    this.data = data;
    this.bindElement = bindElement;

    this.component = $(`
            <button>
                <i class="fas fa-check"></i>
            </button>      
                `);
    this.component.click(this.completeClick.bind(this));
    this.bindElement.prepend(this.component);
  }

  completeClick() {
    var postObject = { chore: this.data.choreId };
    $.post(
      "../routes/route.php?controller=chore&resource=complete",
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
