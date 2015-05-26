function formFileUpload(el, upload_callback) {

	var item = xformGetItem(el);
	var simpleName = el.name.replace("[", "--").replace("]", "");
	
	// Save current values
	var action = el.form.action;
	var onsubmit = el.form.onsubmit;
	var enctype = el.form.enctype;
	var target = el.form.target;
	var path = "/file/upload";
	
	// Create iframe
	var iframe = document.createElement("iframe");
	iframe.name = "form-file-iframe-"+simpleName;
	iframe.className = "form-file-iframe";
	iframe.id = iframe.name;
	iframe.style.display = "none";
	
	// Set form options
	form.action = path+"/"+name;
	form.onsubmit = "";
	form.enctype = "multipart/form-data";
	form.target = iframe.name;
	
	// Insert frame and add listener
	el.parentNode.appendChild(iframe);
	if (addEvent())
		iframe.addEventListener("load", function(){ formFileUploadDone(el, upload_callback, iframe); }, false);
	else
		iframe.attachEvent("onload", function(){ formFileUploadDone(el, upload_callback, iframe); });
	
	// Send form
	if (typeof(form.submit) == "function")
		form.submit();
	else
		form.submit.click();
	
	item.addClass("uploading");
	
	// Reset form options
	form.action = action;
	form.onsubmit = onsubmit;
	form.enctype = enctype;
	form.target = target;
}

function formFileUploadDone(el, upload_callback, iframe) {

	iframe.parentNode.removeChild(iframe);
	el.value = "";
	
	var callback = function(obj) { 
		
		var item = formGetItem(el);
		item.removeClass("uploading");
		
		if (obj.status == "success") {
			// TODO: json to html library
		}
		if (upload_callback)
			upload_callback(el, obj);
		
	};
	
	var ajax = new xajax();
	ajax.send(
		"/file/upload/"+form.elements[el.name.substr(0,-6)+"[token]"].value,
		callback
	);

}

function formGetItem(el) {
	for (el; el && !el.className.match("form-item"); el = el.parentNode);
	return el;
}