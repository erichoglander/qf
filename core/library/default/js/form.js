function formFileUpload(el, upload_callback) {

	var item = formGetItem(el);
	var simpleName = el.name.replace("[", "--").replace("]", "");
	
	// Save current values
	var action = el.form.action;
	var onsubmit = el.form.onsubmit;
	var enctype = el.form.enctype;
	var target = el.form.target;
	var path = "/file/upload/"+el.form.elements[el.name.substr(0, el.name.length-6)+"[token]"].value;
	
	// Create iframe
	var iframe = document.createElement("iframe");
	iframe.name = "form-file-iframe-"+simpleName;
	iframe.className = "form-file-iframe";
	iframe.id = iframe.name;
	iframe.style.display = "none";
	
	// Set form options
	el.form.action = path+"/"+name;
	el.form.onsubmit = "";
	el.form.enctype = "multipart/form-data";
	el.form.target = iframe.name;
	
	// Insert frame and add listener
	el.parentNode.appendChild(iframe);
	iframe.addEventListener("load", function(){ formFileUploadDone(el, upload_callback, iframe); }, false);
	
	// Send form
	if (typeof(el.form.submit) == "function")
		el.form.submit();
	else
		el.form.submit.click();
	
	item.addClass("uploading");
	
	// Reset form options
	el.form.action = action;
	el.form.onsubmit = onsubmit;
	el.form.enctype = enctype;
	el.form.target = target;
}

function formFileUploadDone(el, upload_callback, iframe) {

	var re = iframe.contentDocument.body.innerHTML;
	var obj = JSON.parse(re);
	var item = formGetItem(el);

	iframe.parentNode.removeChild(iframe);
	el.value = "";
	item.removeClass("uploading");
	
	if (obj.error) {
		alert(obj.error);
		return;
	}
	if (obj.dom) {
		console.log(obj.dom[0]);
		jsonToHtml(item, obj.dom[0], true);
	}

	if (upload_callback)
		upload_callback(el, obj);

}

function formGetItem(el) {
	for (el; el && !el.className.match("form-item"); el = el.parentNode);
	return el;
}