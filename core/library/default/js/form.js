function formAjaxSubmit(form) {
  if (form.hasClass("loading"))
    return false;
  var obj = {
    method: "POST",
    errorHandle: true
  };
  if (typeof(FormData) != "undefined") {
    obj.post = new FormData(form);
  }
  else {
    var id = formId();
    obj.post = oldFormData(form, id);
    obj.headers = oldFormHeaders(id);
  }
  var url = (form.getAttribute("action") ? form.getAttribute("action") : BASE_URL+REQUEST_PATH);
  var callback = function(r) {
    form.removeClass("loading");
    if (r.status == "error") {
      alert(r.error);
      return;
    }
    if (r.form) {
      var el = document.createElement("div");
      jsonToHtml(el, r.form);
      var wrap = form.parentNode;
      wrap.removeChild(form);
      wrap.appendChild(el.getElementsByTagName("form")[0]);
    }
    if (r.replace) {
      jsonToHtml(r.replace[0], r.replace[1], true);
    }
    if (r.eval) {
      eval(r.eval);
    }
  };
  var ajax = new xajax();
  form.addClass("loading");
  ajax.send(url, callback, obj);
  return false;
}
function formId() {
  var id = "1";
  for (var i=0; i<12; i++)
    id+= parseInt(Math.random()*10);
  return id;
}
function oldFormData(form, id) {
  if (!id)
    id = formId();
  var data = '';
  for (var i=0; form[i]; i++) {
    data+= '-----------------------------'+id+'\n';
    data+= 'Content-Disposition: form-data; name="'+form[i].name+'"\n\n';
    data+= form[i].value;
    data+= "\n";
  }
  data+= '-----------------------------'+id+'--\n';
  return data;
}
function oldFormHeaders(id) {
  return { "Content-type": "multipart/form-data; boundary=---------------------------"+id };
}

function formFileUpload(el, parent_multiple, callback) {

  var item = formGetItem(el);
  var simpleName = el.name.replace("[", "--").replace("]", "");
  var name = el.name.substr(0, el.name.length-6);
  
  // Save current values
  var action = el.form.action;
  var onsubmit = el.form.onsubmit;
  var enctype = el.form.enctype;
  var target = el.form.target;
  var token = el.form.elements[name+"[token]"].value;
  
  // Create iframe
  var iframe = document.createElement("iframe");
  iframe.name = "form-file-iframe-"+simpleName;
  iframe.className = "form-file-iframe";
  iframe.id = iframe.name;
  iframe.style.display = "none";
  
  // Set form options
  el.form.action = BASE_URL+"form/fileupload/"+token;
  el.form.onsubmit = "";
  el.form.enctype = "multipart/form-data";
  el.form.target = iframe.name;
  
  // Insert frame and add listener
  el.parentNode.appendChild(iframe);
  iframe.addEventListener("load", function(){ formFileUploadDone(el, parent_multiple, callback, iframe); }, false);
  
  // Send form
  if (typeof(el.form.submit) == "function")
    el.form.submit();
  else
    el.form.submit.click();
  
  item.addClass("loading");
  
  // Reset form options
  el.form.action = action;
  el.form.onsubmit = onsubmit;
  el.form.enctype = enctype;
  el.form.target = target;
}

function formFileUploadDone(el, parent_multiple, callback, iframe) {

  var re = iframe.contentDocument.body.innerHTML;
  var obj = JSON.parse(re);
  var item = formGetItem(el);

  iframe.parentNode.removeChild(iframe);
  el.value = "";
  item.removeClass("loading");
  
  if (obj.error) {
    alert(obj.error);
    return;
  }
  if (obj.dom) {
    if (parent_multiple) 
      var parent = formGetItem(item.parentNode);
    var wrap = document.createElement("div");
    jsonToHtml(wrap, obj.dom);
    item.parentNode.insertBefore(wrap.childNodes[0], item);
    item.parentNode.removeChild(item);
    if (parent_multiple) {
      var btns = parent.getElementsByClassName("form-add-button");
      if (btns.length) {
        btns[btns.length-1].trigger("click");
      }
    }
  }

  if (callback)
    callback(el, obj);

}

function formFileRemove(button, name, parent_multiple, callback) {
  var form = formGetForm(button);
  var id = form.elements[name+"[id]"].value;
  var token = form.elements[name+"[token]"].value;
  var item = formGetItem(button);
  if (!id)
    return;
  var callback = function(r) {
    item.removeClass("loading");
    if (r.error) {
      alert(r.error);
      return;
    }
    if (r.dom) {
      if (parent_multiple) {
        formDeleteButton(button);
      }
      else {
        var wrap = document.createElement("div");
        jsonToHtml(wrap, r.dom);
        item.parentNode.insertBefore(wrap.childNodes[0], item);
        item.parentNode.removeChild(item);
      }
    }
  };
  item.addClass("loading");
  var ajax = new xajax();
  ajax.send(BASE_URL+"form/fileremove/"+token+"/"+id,  callback);
}

function formDeleteButton(el) {
  var item = formGetItem(el);
  item.parentNode.removeChild(item);
  var parent = formGetItem(el.parentNode);
  if (parent.getElementsByClassName("form-item").length < 1) {
    var adds = parent.getElementsByClassName("form-add-button");
    if (adds.length) 
      adds[adds.length-1].trigger("click");
  }
}

var _formAdding = false;
function formAddButton(el, structure) {
  if (_formAdding)
    return;
  _formAdding = true;
  var item = formGetItem(el);
  item.addClass("loading");
  var items = item.getElementByClassName("form-items").getElementByClassName("inner");
  var n = parseInt(el.getAttribute("last_item"))+1;
  el.setAttribute("last_item", n);
  var callback = function(r) {
    _formAdding = false;
    item.removeClass("loading");
    if (r.dom) 
      jsonToHtml(items, r.dom);
  };
  structure.name = n;
  var data = {
    method: "POST",
    obj: {
      structure: structure
    }
  };
  var ajax = new xajax();
  ajax.send(BASE_URL+"form/additem", callback, data);
}

function formGetItem(el) {
  for (el; el && !el.hasClass("form-item"); el = el.parentNode);
  return el;
}
function formGetForm(el) {
  for (el; el && el.tagName != "FORM"; el = el.parentNode);
  return el;
}

function formReset(form) {
  var skip = ["submit", "button", "image", "hidden"];
  var box = ["radio", "checkbox"];
  for (var i=0; i<form.elements.length; i++) {
    if (box.indexOf(form.elements[i].type) != -1) {
      if (form.elements[i].checked) {
        form.elements[i].checked = false;
        form.elements[i].trigger("click");
      }
    }
    else if (skip.indexOf(form.elements[i].type) == -1) {
      if (form.elements[i].value.length) {
        form.elements[i].value = "";
        form.elements[i].trigger("change");
      }
    }
  }
}

function formGeneratePassword(el, copy) {
  var item = formGetItem(el);
  el = item.getElementsByTagName("input")[0];
  var str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789%=!?&/#-_.,:;*+[]()";
  var pass = "";
  var len = 10+Math.floor(Math.random()*5);
  for (var i=0; i<len; i++)
    pass+= str[Math.floor(Math.random()*str.length)];
  el.value = pass;
  el.form.elements[copy].value = pass;
  var inp = document.createElement("input");
  inp.type = "text";
  inp.value = pass;
  inp.style.position = "fixed";
  inp.style.left = "-10000px";
  document.body.appendChild(inp);
  inp.select();
  if (!document.execCommand("copy")) 
    prompt("Copy password: Ctrl+C, Enter", pass);
  document.body.removeChild(inp);
}


var _formCollapsibles = [];
function formCollapsibleInit() {
  var els = document.getElementsByClassName("form-collapsible");
  for (var i=0; i<els.length; i++)
    _formCollapsibles.push(new collapsible(els[i]));
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        formCollapsibleObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      formCollapsibleObserve(document.body);
    }, 500);
  }
}
function formCollapsibleObserve(el) {
  var els = el.getElementsByClassName("form-collapsible");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("collapsible-init"))
      _formCollapsibles.push(new collapsible(els[i]));
  }
}
window.addEventListener("load", formCollapsibleInit, false);