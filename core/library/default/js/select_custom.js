var _select_customs = [];
function selectCustomInit() {
	var els = document.getElementsByClassName("select-custom");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("select-custom-init")) {
			els[i].addClass("select-custom-init");
			_select_customs.push(new selectCustom(els[i]));
		}
	}
	if (typeof(MutationObserver) == "function") {
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				selectCustomObserve(mutation.target);
			});    
		});
		var config = { childList: true, subtree: true };
		observer.observe(document.body, config);
	}
	else {
		setInterval(function() {
			selectCustomObserve(document.body);
		}, 1000);
	}
}
function selectCustomObserve(el) {
	var els = el.getElementsByClassName("select-custom");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("select-custom-init")) {
			els[i].addClass("select-custom-init");
			_select_customs.push(new selectCustom(els[i]));
		}
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
		this.tags.itemsWrap = this.tags.wrap.getElementByClassName("select-custom-options");
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
		self.selectChange();
		if (typeof(MutationObserver) == "function") {
			var observer = new MutationObserver(function(mutations) {
				mutations.forEach(function(mutation) {
					self.selectChange(mutation.target);
				});
			});
			var config = { childList: true, subtree: true };
			observer.observe(this.tags.select, config);
		}
	}

	this.selectChange = function() {
		this.renderOptions();
	}
	
	this.renderOptions = function() {
		this.tags.itemsWrap.innerHTML = "";
		this.tags.items = [];
		for (var i=0; i<this.tags.select.options.length; i++) {
			this.tags.items[i] = document.createElement("div");
			this.tags.items[i].className = "select-custom-option";
			this.tags.items[i].innerHTML = this.tags.select.options[i].innerHTML;
			this.tags.itemsWrap.appendChild(this.tags.items[i]);
			(function(self, n) {
				self.tags.items[n].addEventListener("click", function(){ self.itemClick(n); }, false);
			}(this, i));
		}
		this.onChange();
	}
	
	this.toggle = function() {
		if (this.isOpen())
			this.close();
		else
			this.open();
	}
	this.open = function() {
		var self = this;
		this.tags.itemsWrap.style.display = "block";
		setTimeout(function() {
			self.tags.wrap.addClass("active");
			if (typeof(self.tags.items[self.tags.select.selectedIndex]) != "undefined") {
				self.tags.itemsWrap.scrollTop = 
						self.tags.items[self.tags.select.selectedIndex].offsetTop +
						self.tags.items[self.tags.select.selectedIndex].offsetHeight/2 -
						self.tags.itemsWrap.offsetHeight/2;
			}
		}, 1);
	}
	this.close = function() {
		var self = this;
		var duration = getStyle(this.tags.itemsWrap, "transition-duration");
		var t = parseFloat(duration)*1000;
		this.tags.wrap.removeClass("active");
		setTimeout(function() {
			self.tags.itemsWrap.style.display = "none";
		},t);
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