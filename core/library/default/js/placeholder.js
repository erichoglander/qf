var _placeholders = [];

function placeholderInit() {
	if ("placeholder" in document.createElement("input"))
		return;
	var tags = ["input", "textarea"];
	for (var i=0; i<tags.length; i++) {
		var inputs = document.getElementsByTagName(tags[i]);
		for (var j=0; j<inputs.length; j++) {
			if (inputs[j].getAttribute("placeholder")) {
				_placeholders.push(new placeholder(inputs[j]));
			}
		}
	}
}

function placeholder(el) {
	
	this.tags = {
		input: el
	};
	
	this.init = function() {
		var self = this;
		this.placeholder = this.tags.input.getAttribute("placeholder");
		this.tags.placeholder = document.createElement("div");
		this.tags.placeholder.className = "ie-placeholder";
		if (this.tags.input.value.length == 0)
			this.tags.placeholder.className+= " active";
		this.tags.placeholder.innerHTML = this.placeholder;
		this.tags.placeholder.style.fontSize = getStyle(this.tags.input, "font-size");
		this.tags.placeholder.style.lineHeight = getStyle(this.tags.input, "line-height");
		this.tags.placeholder.style.paddingLeft = getStyle(this.tags.input, "padding-left");
		this.tags.placeholder.style.paddingRight = getStyle(this.tags.input, "padding-right");
		this.tags.placeholder.style.paddingTop = getStyle(this.tags.input, "padding-top");
		this.tags.placeholder.style.paddingBottom = getStyle(this.tags.input, "padding-bottom");
		this.tags.placeholder.style.borderWidth = getStyle(this.tags.input, "border-width");
		this.tags.placeholder.style.borderColor = "transparent"; 
		this.tags.placeholder.style.textAlign = getStyle(this.tags.input, "text-align");
		this.tags.placeholder.style.width = getStyle(this.tags.input, "width");
		this.tags.placeholder.style.left = this.tags.input.offsetLeft+"px";
		this.tags.placeholder.style.top = this.tags.input.offsetTop+"px";
		this.tags.placeholder.addEventListener("click", function(){ self.placeholderClick(); }, false);
		this.tags.input.addEventListener("focus", function(){ self.inputFocus(); }, false);
		this.tags.input.addEventListener("blur", function(){ self.inputBlur(); }, false);
		this.tags.input.parentNode.appendChild(this.tags.placeholder);
	}
	
	this.hide = function() {
		this.tags.placeholder.removeClass("active");
	}
	this.show = function() {
		this.tags.placeholder.addClass("active");
	}
	
	this.placeholderClick = function() {
		this.tags.input.focus();
	}
	this.inputFocus = function() {
		this.hide();
	}
	this.inputBlur = function() {
		if (this.tags.input.value.length == 0)
			this.show();
	}
	
	this.init();
	
}

window.addEventListener("load", placeholderInit, false);