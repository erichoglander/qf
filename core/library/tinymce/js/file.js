function tinymceFileBrowserCallback(field_name, url, type, win) {
  var el = win.document.getElementById(field_name);
  var form = win.document.getElementById("tinymce-file-browser");
  if (!form) {
    form = win.document.createElement("form");
    form.style.overflow = "hidden";
    form.style.width = "0";
    form.style.height = "0";
    form.id = "tinymce-file-browser";
    var inp = document.createElement("input");
    inp.type = "file";
    inp.name = "file";
    form.appendChild(inp);
    win.document.body.appendChild(form);
  }
  
  var url = tinyMCE.activeEditor.settings.images_upload_url;
  form.file.addEventListener("change", function() {
    var fd = new FormData();
    fd.append("file", form.file.files[0]);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url);
    xhr.onload = function() {
      if (xhr.status == 200) {
        var r = JSON.parse(xhr.responseText);
        if (r && typeof(r) == "object" && r.location)
          el.value = r.location;
      }
    };
    xhr.send(fd);
    if (form && form.parentNode)
      form.parentNode.removeChild(form);
  }, false);
  
  form.file.click();
}