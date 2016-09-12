function jsonToHtml(parent, json, replace, ns) {
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
        if (json.tagName == "svg")
          ns = "http://www.w3.org/2000/svg";
        if (ns)
          var el = document.createElementNS(ns, json.tagName);
        else
          var el = document.createElement(json.tagName);
        if (json.attributes) {
          for (var attr in json.attributes) {
            if (typeof(json.attributes[attr]) != "undefined") {
              if (json.attributes[attr] === null)
                el.setAttribute(attr, "");
              else {
                try {
                  el.setAttribute(attr, json.attributes[attr]);
                }
                catch (e) {}
              }
            }
          }
        }
        if (json.children) {
          for (var i in json.children)
            jsonToHtml(el, json.children[i], false, ns);
        }
        parent.appendChild(el);
      }
      else if (json.script) {
        eval(json.script);
      }
      else if (json.comment) {
        var comment = document.createComment(json.comment);
        parent.appendChild(comment);
      }
    }
  }
  else if (json.length) {
    var e = document.createElement("div");
    e.innerHTML = json.replace("&nbsp;", "\u00A0");
    parent.appendChild(document.createTextNode(e.childNodes[0].nodeValue));
  }
}