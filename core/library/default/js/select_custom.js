var _select_customs = [];
function selectCustomInit() {
	var els = document.getElementsByClassName("select-custom");
	for (var i=0; i<els.length; i++)
		_select_customs.push(new selectCustom(els[i]));
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			selectCustomObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function selectCustomObserve(el) {
	var els = el.getElementsByClassName("select-custom");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("select-custom-init"))
			_select_customs.push(new selectCustom(els[i]));
	}
}

function selectCustom(el) {
	
	this.tags = {
		wrap: el
	};
	
	this.init = function() {
		var self = this;
		this.tags.titleWrap = this.tags.wrap.getElementsByClassName("select-custom-title")[0];
		this.tags.title = this.tags.titleWrap.getElementsByClassName("select-custom-title-inner")[0];
		this.tags.items = this.tags.wrap.getElementsByClassName("select-custom-option");
		this.tags.select = this.tags.wrap.getElementsByTagName("select")[0];
		this.tags.select.addEventListener("change", function(){ self.onChange(); }, false);
		for (var i=0; i<this.tags.items.length; i++) {
			(function(n) {
				self.tags.items[n].addEventListener("click", function(){ self.itemClick(n); }, false);
			}(i));
		}
		this.tags.titleWrap.addEventListener("click", function(){ self.toggle(); }, false);
		window.addEventListener("click", function(e){ self.windowClick(e); }, false);
		this.tags.wrap.addClass("select-custom-init");
	}
	
	this.toggle = function() {
		if (this.isOpen())
			this.close();
		else
			this.open();
	}
	this.open = function() {
		this.tags.wrap.addClass("active");
	}
	this.close = function() {
		this.tags.wrap.removeClass("active");
	}
	this.isOpen = function() {
		if (this.tags.wrap.hasClass("active"))
			return true;
		return false;
	}
	
	this.windowClick = function(e) {
		if (!this.isOpen())
			return;
		for (var i=0, el = e.target; i<5 && el != null && el != this.tags.wrap; i++, el = el.parentNode);
		if (!el || i == 5)
			this.close();
	}
	
	this.itemClick = function(n) {
		this.tags.select.selectedIndex = n;
		this.tags.select.trigger("change");
		this.close();
	}
	
	this.onChange = function() {
		for (var i=0; i<this.tags.items.length; i++) {
			if (i == this.tags.select.selectedIndex)
				this.tags.items[i].addClass("active");
			else
				this.tags.items[i].removeClass("active");
		}
		if (this.tags.select.selectedIndex != -1) 
			this.tags.title.innerHTML = this.tags.select.options[this.tags.select.selectedIndex].text;
		else 
			this.tags.title.innerHTML = "";
	}
	
	this.init();
	
}

window.addEventListener("load", selectCustomInit, false);