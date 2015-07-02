var _autoselects = [];
function autoselectInit() {
	var els = document.getElementsByClassName("form-autoselect");
	for (var i=0; i<els.length; i++)
		_autoselects.push(new autoselect(els[i]));
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			autoselectObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function autoselectObserve(el) {
	var els = el.getElementsByClassName("form-autoselect");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("autoselect-init"))
			_autoselects.push(new autoselect(els[i]));
	}
}

function autoselect(el) {
	
	this.tags = {
		input: el
	};
	
	this.init = function() {
		var self = this;
		this.optionActive = -1;
		this.tags.item = formGetItem(this.tags.input);
		this.tags.select = this.tags.item.getElementsByTagName("select")[0];
		this.tags.options = this.tags.item.getElementsByClassName("autoselect-options")[0];
		this.tags.input.addEventListener("blur", function(){ self.onBlur(); }, false);
		this.tags.input.addEventListener("focus", function(){ self.onFocus(); }, false);
		this.tags.input.addEventListener("keydown", function(e){ self.onKeyDown(e); }, false);
		this.tags.input.addEventListener("keyup", function(e){ self.onKeyUp(e); }, false);
	}
	
	this.renderOptions = function() {
		var value = this.tags.input.value;
		this.tags.options.innerHTML = "";
		this.tags.option = [];
		this.optionActive = -1;
		for (var i=0; i<this.tags.select.options.length; i++) {
			if (!value.length || this.tags.select.options[i].text.substr(0, value.length).toLowerCase() == value.toLowerCase()) {
				this.tags.option[i] = document.createElement("div");
				this.tags.option[i].className = "autoselect-option";
				this.tags.option[i].innerHTML = this.tags.select.options[i].text;
				this.tags.options.appendChild(this.tags.option[i]);
				(function(self, n) {
					self.tags.option[n].addEventListener("click", function(){ self.optionClick(n); }, false);
				}(this, i));
			}
		}
	}
	
	this.onFocus = function() {
		this.tags.input.value = "";
		this.renderOptions();
	}
	
	this.onBlur = function() {
		if (!this.tags.input.value.length) {
			this.tags.select.selectedIndex = 0;
			this.tags.select.trigger("change");
			this.tags.input.value = this.tags.select.options[this.tags.select.selectedIndex].text;
		}
	}
	
	/*
		e.keyCode
			37 = left
			38 = up
			39 = right
			40 = down
			27 = esc
			13 = enter
	*/
	this.onKeyDown = function(e) {
		var code = e.keyCode;
		if (code == 13) {
			e.preventDefault();
			return false;
		}
		else if (code == 27) {
			this.tags.input.value = this.tags.select.options[this.tags.select.selectedIndex].text;
			this.tags.input.blur();
		}
	}
	this.onKeyUp = function(e) {
		var code = e.keyCode;
		if (code == 13) {
			e.preventDefault();
			this.optionEnter();
			return false;
		}
		else if (code == 40 || code == 38) {
			if (code == 40)
				this.optionDown();
			else
				this.optionUp();
		}
		else {
			this.renderOptions();
		}
	}
	
	this.optionUp = function() {
		for (var i=this.optionActive-1; i >= 0; i--) {
			if (this.tags.option[i]) {
				if (this.optionActive != -1)
					this.tags.option[this.optionActive].removeClass("active");
				this.tags.option[i].addClass("active");
				this.optionActive = i;
				break;
			}
		}
	}
	this.optionDown = function() {
		for (var i=this.optionActive+1; i < this.tags.select.options.length; i++) {
			if (this.tags.option[i]) {
				if (this.optionActive != -1)
					this.tags.option[this.optionActive].removeClass("active");
				this.tags.option[i].addClass("active");
				this.optionActive = i;
				break;
			}
		}
	}
	this.optionEnter = function() {
		if (this.optionActive != -1)
			this.optionClick(this.optionActive);
	}
	
	this.optionClick = function(n) {
		this.tags.input.value = this.tags.select.options[n].text;
		this.tags.input.blur();
		this.tags.select.selectedIndex = n;
		this.tags.select.trigger("change");
	}
	
	this.init();
	
}

window.addEventListener("load", autoselectInit, false);