function formAjaxSubmit(form, ajax, cb) {
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
      if (typeof(r.form) == "object") {
        var el = document.createElement("div");
        jsonToHtml(el, r.form);
        var wrap = form.parentNode;
        wrap.removeChild(form);
        var new_form = el.getElementsByTagName("form")[0];
        wrap.appendChild(new_form);
      }
      else {
        var wrap = form.parentNode;
        wrap.removeChild(form);
        wrap.innerHTML+= r.form;
        var new_form = wrap.getElementsByTagName("form")[0];
      }
      if (new_form.getAttribute("error-focus"))
        formErrorFocus(new_form);
    }
    if (r.replace) {
      jsonToHtml(r.replace[0], r.replace[1], true);
    }
    if (r.eval) {
      eval(r.eval);
    }
    if (cb)
      cb(r);
  };
  if (!ajax)
    ajax = new xajax();
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
function formFileDragOver(el, e) {
  e.stopPropagation();
  e.preventDefault();
  el.addClass("hover");
}
function formFileDragLeave(el, e) {
  e.stopPropagation();
  e.preventDefault();
  el.removeClass("hover");
}
function formFileDrop(el, e, parent_multiple, callback) {
  formFileDragLeave(el, e);
  var item = formGetItem(el);
  var input = item.getElementByClassName("form-file");
  var files = e.dataTransfer.files || e.target.files;
  if (!input || !files.length)
    return;
  var name = input.name.substr(0, input.name.length-6);
  var formData = new FormData(input.form);
  formData.set(input.name, files[0]);
  var token = input.form.elements[name+"[token]"].value;
  var ajax = new xajax();
  var url = BASE_URL+"form/fileupload/"+token;
  var cb = function(r) {
    formFileUploadDone(el, parent_multiple, callback, r);
  };
  var data = {
    method: "POST",
    post: formData,
    errorHandle: true
  };
  item.addClass("loading");
  ajax.send(url, cb, data);
}
function formFileUploadDone(el, parent_multiple, callback, obj) {

  if (typeof(obj.tagName) != "undefined" && obj.tagName == "IFRAME") {
    var iframe = obj;
    var re = iframe.contentDocument.body.innerHTML;
    obj = JSON.parse(re);
    iframe.parentNode.removeChild(iframe);
  }
  var item = formGetItem(el);
  var new_item = null;
  
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
    new_item = wrap.childNodes[0];
    item.parentNode.insertBefore(new_item, item);
    item.parentNode.removeChild(item);
    if (parent_multiple && !new_item.hasClass("form-item-error")) {
      var btns = parent.getElementsByClassName("form-add-button");
      if (btns.length) {
        btns[btns.length-1].trigger("click");
      }
    }
  }

  if (callback)
    callback(el, obj, new_item);

}

function formFileRemove(button, name, parent_multiple, callback) {
  var form = formGetForm(button);
  var id = form.elements[name+"[id]"].value;
  var token = form.elements[name+"[token]"].value;
  var item = formGetItem(button);
  var new_item = null;
  if (!id)
    return;
  var cb = function(r) {
    item.removeClass("loading");
    if (r.error) {
      alert(r.error);
      return;
    }
    if (r.dom) {
      if (parent_multiple) {
        var dels = item.getElementsByClassName("form-delete-button");
        if (dels.length) {
          var del = dels[dels.length-1];
          if (del.parentNode == item)
            del.trigger("click");
        }
      }
      else {
        var wrap = document.createElement("div");
        jsonToHtml(wrap, r.dom);
        new_item = wrap.childNodes[0];
        item.parentNode.insertBefore(new_item, item);
        item.parentNode.removeChild(item);
      }
    }
    if (callback)
      callback(item, r, new_item);
  };
  item.addClass("loading");
  var ajax = new xajax();
  ajax.send(BASE_URL+"form/fileremove/"+token+"/"+id,  cb);
}

function formDeleteButton(el, callback, add_new, max) {
  var item = formGetItem(el);
  var itemwrap = item.parentNode;
  var parent = formGetItem(itemwrap);
  itemwrap.removeChild(item);
  
  // Fetch add button
  var adds = parent.getElementsByClassName("form-add-button");
  var add = null;
  if (adds.length && adds[adds.length-1].parentNode == parent)
    add = adds[adds.length-1];
  
  var readd = false;
  if (max && max > itemwrap.children.length && add && add.style.display) {
    add.style.display = null;
    if (add.add_failed)
      readd = true;
  }
  if (add_new && add && (!itemwrap.children.length || readd))
    add.trigger("click");
  if (callback)
    eval("(function(){ return "+callback+";}())");
}

var _formAdding = false;
function formAddButton(el, structure) {
  if (_formAdding)
    return;
  var item = formGetItem(el);
  var items = item.getElementByClassName("form-items").getElementByClassName("inner");
  var max = (structure.multiple_max ? structure.multiple_max : 0);
  var nth = items.children.length+1;
  if (max && nth > max) {
    el.add_failed = true;
    return;
  }
  el.add_failed = false;
  _formAdding = true;
  var n = parseInt(el.getAttribute("last_item"))+1;
  el.setAttribute("last_item", n);
  var callback = function(r) {
    _formAdding = false;
    item.removeClass("loading");
    var new_item;
    if (typeof(r.dom) != "undefined") {
      jsonToHtml(items, r.dom);
      for (var i=items.children.length-1; i>=0; i--) {
        if (items.children[i].hasClass("form-item"))
          break;
      }
      if (i >= 0)
        new_item = items.children[i];
    }
    if (max && nth == max)
      el.style.display = "none";
    if (typeof(r.callback) != "undefined")
      eval("(function(){ return "+r.callback+";}())");
  };
  structure.name = n;
  var data = {
    method: "POST",
    obj: {
      structure: structure
    }
  };
  var ajax = new xajax();
  item.addClass("loading");
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
    form.elements[i].trigger("reset");
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


function formPopupAddOpen(wrap, new_item, r) {
  new_item.getElementByClassName("form-popup-button").trigger("click");
}
function formPopup(item, structure) {
  var p = new popup();
  p.temporary = true;
  var f = formGetForm(item);
  var form = document.createElement("form");
  form.setAttribute("name", item.getAttribute("name"));
  form.setAttribute("size", item.getAttribute("size"));
  if (item.getAttribute("close"))
    form.setAttribute("close", item.getAttribute("close"));
  form.method = "POST";
  var pw = item.getElementByClassName("form-popup-wrap");
  var html = pw.innerHTML;
  // Remove all init-classes
  html = html.replace(/\s*[a-z\-]+\-init/g, "");
  form.innerHTML = html;
  form.className = "form-popup "+f.className+"-popup";
  form.action = BASE_URL+"form/validateitem";
  form.addEventListener("submit", function(e) {
    e.preventDefault();
    formAjaxSubmit(form, null, function(r) {
      formPopupSubmit(r, item, form, p);
    });
  }, false);
  var hidden = document.createElement("input");
  hidden.type = "hidden";
  hidden.name = "__form_popup_structure";
  hidden.value = JSON.stringify(structure);
  form.appendChild(hidden);
  p.move(form);
  p.open();
}
function formPopupButton(btn, structure) {
  formPopup(btn.parentNode, structure);
}
function formPopupSubmit(r, item, form, p) {
  if (form.action.indexOf("upload") !== -1)
    return;
  
  if (typeof(r.dom) != "undefined") {
    var div = document.createElement("div");
    jsonToHtml(div, r.dom);
    if (r.validated)  {
      var d = document.createElement("div");
      jsonToHtml(d, r.dom);
      var new_item = d.children[0];
      item.innerHTML = "";
      item.parentNode.insertBefore(new_item, item);
      item.parentNode.removeChild(item);
      item = new_item;
    }
    var pw = div.getElementByClassName("form-popup-wrap");
    var structure = form.elements['__form_popup_structure'].value;
    form.innerHTML = pw.innerHTML;
    var hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.name = "__form_popup_structure";
    hidden.value = structure;
    form.appendChild(hidden);
  }
  if (r.validated) {
    if (item.getAttribute("callback")) 
      eval("(function(){ return "+item.getAttribute("callback")+";}())");
    p.close();
  }
}

function formTinymceLoad(src) {
  var scripts = document.getElementsByTagName("script");
  for (var i=0; i<scripts.length; i++) {
    if (scripts[i].src == src)
      return;
  }
  var script = document.createElement("script");
  script.src = src;
  document.head.appendChild(script);
}

function formErrorFocus(form) {
  var el = form.getElementByClassName("form-item-error");
  if (el) {
    for (el; el && el.tagName != "FORM" && (!el.offsetParent || !el.hasClass("form-item")); el = el.parentNode);
    var y = getTopPos(el);
    var am = document.getElementById("menu-admin");
    if (am)
      y+= am.offsetHeight;
    var margin = (window.innerHeight-el.offsetHeight)/4;
    var sy = scrollTop();
    if (y < sy)
      smoothScroll(y-margin);
    else if (y > sy + window.innerHeight)
      smoothScroll(y-window.innerHeight+margin);
    var t = el.getElementByClassName("form-textfield");
    if (t && typeof(t.value) != "undefined" && !t.value.length)
      t.focus();
    return true;
  }
  return false;
}

function formOnLoad() {
  var forms = document.getElementsByTagName("form");
  for (var i=0; i<forms.length; i++) {
    if (forms[i].getAttribute("error-focus")) {
      if (formErrorFocus(forms[i]))
        break;
    }
  }
}

window.addEventListener("load", function(){ formOnLoad(); }, false);