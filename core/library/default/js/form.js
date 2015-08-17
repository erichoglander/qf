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
	el.form.action = "/form/fileupload/"+token;
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
	ajax.send("/form/fileremove/"+token+"/"+id,	callback);
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
	var cname = el.previousElementSibling.className.match(/form\-name\-([0-9]+)/);
	var n = (cname ? parseInt(cname[1])+1 : 1);
	var callback = function(r) {
		_formAdding = false;
		item.removeClass("loading");
		if (r.dom) {
			var wrap = document.createElement("div");
			jsonToHtml(wrap, r.dom);
			item.insertBefore(wrap.childNodes[0], el);
		}
	};
	structure.name = n;
	var data = {
		method: "POST",
		obj: {
			structure: structure
		}
	};
	var ajax = new xajax();
	ajax.send("/form/additem", callback, data);
}

function formGetItem(el) {
	for (el; el && !el.hasClass("form-item"); el = el.parentNode);
	return el;
}
function formGetForm(el) {
	for (el; el && el.tagName != "FORM"; el = el.parentNode);
	return el;
}


var _formCollapsibles = [];
function formCollapsibleInit() {
	var els = document.getElementsByClassName("form-collapsible");
	for (var i=0; i<els.length; i++)
		_formCollapsibles.push(new collapsible(els[i]));
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			formCollapsibleObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function formCollapsibleObserve(el) {
	var els = el.getElementsByClassName("form-collapsible");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("collapsible-init"))
			_formCollapsibles.push(new collapsible(els[i]));
	}
}
window.addEventListener("load", formCollapsibleInit, false);