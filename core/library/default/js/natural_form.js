var _naturalForms = {};

function naturalFormsInit() {
	var els = document.getElementsByClassName("natural-form");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("natural-form-init")) {
			els[i].addClass("natural-form-init");
			var name = els[i].getAttribute("name");
			if (!name)
				name = Math.random().toString(36).substr(2,5);
			_naturalForms[name] = new naturalForm(els[i], name);
		}
	}
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			naturalFormsObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function naturalFormsObserve() {
	var els = document.getElementsByClassName("natural-form");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("natural-form-init")) {
			els[i].addClass("natural-form-init");
			var name = els[i].getAttribute("name");
			if (!name)
				name = Math.random().toString(36).substr(2,5);
			_naturalForms[name] = new naturalForm(els[i], name);
		}
	}
}

function naturalForm(el, name) {

	this.tags = {
		wrap: el
	};

	this.name = name;
	
	this.init = function() {
	
		var self = this;
		this.current = 0;
		this.IE = (window.navigator.userAgent.match("Trident") ? true : false);
		
		this.tags.root = this.tags.wrap.getElementByClassName("form-root-container");
		this.tags.button = this.tags.wrap.getElementByClassName("nf-button");
		this.tags.progress = this.tags.wrap.getElementByClassName("progress");
		this.tags.current = this.tags.wrap.getElementByClassName("current");
		
		this.items = [];
		var items = this.tags.root.children;
		for (var i=0; i<items.length; i++) {
			if (!items[i].hasClass("form-item"))
				continue;
			var inp;
			if (items[i].className.match("form-type-select"))
				inp = items[i].getElementsByTagName("select")[0];
			else
				inp = items[i].getElementsByTagName("input")[0];
			this.items.push({
				wrap: items[i],
				input: inp
			});
			if (items[i].hasClass("active")) {
				if (i == 0 && items[i].hasClass("form-item-error"))
					inp.focus();
				else if (i != 0)
					this.setActive(i);
			}
		}
		
		for (var i=0; i<this.items.length; i++) {
			if (!this.items[i].wrap.className.match("form-type-text"))
				continue;
			this.items[i].input.addEventListener("keydown", function(e){ self.onKey(e); });
		}
		
		this.tags.button.addEventListener("click", function(){ self.next(); });
			
		this.progress();
	
	}
	
	this.reset = function() {
		this.items[this.current].wrap.removeClass("active");
		this.current = 0;
		this.items[this.current].wrap.addClass("active");
		this.progress();
		for (var i=0; i<this.items.length; i++)
			this.items[i].input.value = "";
	}
	this.setActive = function(n) {
		var self = this;
		this.items[this.current].wrap.removeClass("active");
		this.items[this.current].input.blur();
		this.current = n;
		this.items[n].wrap.addClass("active");
		this.progress();
		if (!this.IE) 
			this.items[n].input.focus();
	}
	this.first = function() {
		this.setActive(0);
	}
	this.done = function() {
		this.tags.wrap.nf_submit.click();
	}
	this.values = function() {
		var values = {};
		for (var i=0; i<this.items.length; i++) 
			values[this.items[i].input.name] = this.items[i].input.value;
		return values;
	}
	
	this.validate = function() {
		if (!this.items[this.current].input.value.length)
			return false;
		return true;
	}
	
	this.prev = function() {
		if (this.current == 0)
			return;
		this.setActive(this.current-1);
	}
	this.next = function() {
		if (!this.validate())
			return;
		if (this.current+1 == this.items.length) {
			this.done();
			return;
		}
		this.setActive(this.current+1);
	}
	this.progress = function() {
		var p = Math.round(100*(this.current+1)/this.items.length);
		this.tags.progress.style.width = p+"%";
		this.tags.current.innerHTML = this.current+1;
	}
	
	this.onKey = function(e) {
		// 9	TAB
		// 13	ENTER
		var keyCode = (e ? e.keyCode : window.event.which);
		if (keyCode == 9 || keyCode == 13)
			e.preventDefault();
		if (keyCode == 13 || keyCode == 9)
			this.next();
	}
	
	this.init();

}

window.addEventListener("load", function(){ naturalFormsInit(); });