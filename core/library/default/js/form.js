function formFileUpload(el, callback) {

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
	el.form.action = "/file/upload/"+token;
	el.form.onsubmit = "";
	el.form.enctype = "multipart/form-data";
	el.form.target = iframe.name;
	
	// Insert frame and add listener
	el.parentNode.appendChild(iframe);
	iframe.addEventListener("load", function(){ formFileUploadDone(el, callback, iframe); }, false);
	
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

function formFileUploadDone(el, callback, iframe) {

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
		var wrap = document.createElement("div");
		jsonToHtml(wrap, obj.dom);
		item.parentNode.insertBefore(wrap.childNodes[0], item);
		item.parentNode.removeChild(item);
	}

	if (callback)
		callback(el, obj);

}

function formFileRemove(button, name, callback) {
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
			var wrap = document.createElement("div");
			jsonToHtml(wrap, r.dom);
			item.parentNode.insertBefore(wrap.childNodes[0], item);
			item.parentNode.removeChild(item);
		}
	};
	item.addClass("loading");
	var ajax = new xajax();
	ajax.send("/file/remove/"+token+"/"+id,	callback);
}

function formGetItem(el) {
	for (el; el && !el.className.match("form-item"); el = el.parentNode);
	return el;
}
function formGetForm(el) {
	for (el; el && el.tagName != "FORM"; el = el.parentNode);
	return el;
}