var _checkboxes_selects = [];
function checkboxesSelectInit() {
	var els = document.getElementsByClassName("checkboxes-select");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("checkboxes-select-init")) {
			els[i].addClass("checkboxes-select-init");
			_checkboxes_selects.push(new checkboxesSelect(els[i]));
		}
	}
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			checkboxesSelectObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function checkboxesSelectObserve(el) {
	var els = el.getElementsByClassName("checkboxes-select");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("checkboxes-select-init")) {
			els[i].addClass("checkboxes-select-init");
			_checkboxes_selects.push(new checkboxesSelect(els[i]));
		}
	}
}

function checkboxesSelect(el) {
	
	this.tags = {
		wrap: el
	};
	
	this.init = function() {
		var self = this;
		this.tags.titleWrap = this.tags.wrap.getElementsByClassName("checkboxes-select-title")[0];
		this.tags.title = this.tags.titleWrap.getElementsByClassName("checkboxes-select-title-inner")[0];
		this.tags.labels = this.tags.wrap.getElementsByTagName("label");
		this.tags.checkboxes = this.tags.wrap.getElementsByTagName("input");
		this.emptyOption = this.tags.wrap.getAttribute("empty_option");
		for (var i=0; i<this.tags.checkboxes.length; i++) {
			(function(n) {
				self.tags.checkboxes[n].addEventListener("click", function(){ self.checkboxClick(n); }, false);
			}(i));
		}
		this.tags.titleWrap.addEventListener("click", function(){ self.toggle(); }, false);
		window.addEventListener("click", function(e){ self.windowClick(e); }, false);
		this.tags.wrap.addClass("checkboxes-select-init");
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
	
	this.checkboxClick = function(n) {
		this.renderTitle();
	}
	
	this.renderTitle = function() {
		var title = "";
		var n = 0;
		for (var i=0; i<this.tags.checkboxes.length; i++) {
			if (this.tags.checkboxes[i].checked) {
				if (n != 0)
					title+= ", ";
				title+=  this.tags.labels[i].textContent.trim();
				n++;
			}
		}
		if (!title.length)
			title = this.emptyOption;
		this.tags.title.innerHTML = title;
	}
	
	this.init();
	
}

window.addEventListener("load", checkboxesSelectInit, false);