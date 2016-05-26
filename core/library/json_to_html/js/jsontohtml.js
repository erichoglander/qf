function jsonToHtml(parent, json, replace) {
  if (replace)
    parent.innerHTML = "";
  if (typeof(json) == "object") {
    // Array
    if (typeof(json.length) != "undefined") {
      for (var i in json)
        jsonToHtml(parent, json[i]);
    }
    // Object
    else {
      if (json.tagName) {
        var el = document.createElement(json.tagName);
        if (json.attributes) {
          for (var attr in json.attributes) {
            if (typeof(json.attributes[attr]) != "undefined") {
              if (json.attributes[attr] === null)
                el.setAttribute(attr, "");
              else
                el.setAttribute(attr, json.attributes[attr]);
            }
          }
        }
        if (json.children) {
          for (var i in json.children)
            jsonToHtml(el, json.children[i]);
        }
        parent.appendChild(el);
      }
      else if (json.script) {
        eval(json.script);
      }
    }
  }
  else if (json.length) {
    var e = document.createElement("div");
    e.innerHTML = json.replace("&nbsp;", "\u00A0");
    parent.appendChild(document.createTextNode(e.childNodes[0].nodeValue));
  }
}