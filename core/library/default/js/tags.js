var _autocompleteTags = [];
function autocompleteTagsInit() {
	var els = document.getElementsByClassName("form-tags");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("autocomplete-tags-init")) {
			els[i].addClass("autocomplete-tags-init");
			_autocompleteTags.push(new autocompleteTags(els[i]));
		}
	}
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			autocompleteTagsObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function autocompleteTagsObserve(el) {
	var els = el.getElementsByClassName("form-tags");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("autocomplete-tags-init")) {
			els[i].addClass("autocomplete-tags-init");
			_autocompleteTags.push(new autocompleteTags(els[i]));
		}
	}
}

function autocompleteTags(el) {
	
	this.tags = {
		input: el
	};
	
	this.init = function() {
		
		this.timeoutTime = 400;
		this.timeout = null;
		this.itemActive = -1;
		this.items = [];
		this.uri = this.tags.input.getAttribute("uri");
		this.lastLength = this.tags.input.value.length;
		
		this.ajax = new xajax();
		
		this.tags.wrap = formGetItem(this.tags.input);
		this.tags.itemsWrap = this.tags.wrap.getElementsByClassName("autocomplete-items")[0];
		this.tags.items = [];
		
		var self = this;
		this.tags.input.addEventListener("keydown", function(e){ self.onKeydown(e); }, false);
		this.tags.input.addEventListener("keyup", function(e){ self.onKeyup(e); }, false);
		this.tags.input.addEventListener("blur", function(){ self.onBlur(); }, false);
		this.tags.input.addEventListener("focus", function(){ self.onFocus(); }, false);
		
		this.tags.wrap.addClass("autocomplete-tags-init");
		
	}
	
	this.lastValue = function() {
		var val = this.tags.input.value;
		var x = val.lastIndexOf(",");
		if (x == -1)
			return val;
		else
			return val.substr(x+1).trim();
	}
	
	/*
		e.keyCode
			37 = left
			38 = up
			39 = right
			40 = down
			13 = enter
	*/
	this.onKeydown = function(e) {
		var code = e.keyCode;
		if (code == 13) {
			e.preventDefault();
			return false;
		}
	}
	this.onKeyup = function(e) {
		var self = this;
		var code = e.keyCode;
		if (code == 13) {
			e.preventDefault();
			this.itemEnter();
			return false;
		}
		else if (code == 40 || code == 38) {
			if (code == 40)
				this.itemDown();
			else
				this.itemUp();
		}
		else if (this.lastLength != this.tags.input.value.length) {
			this.lastLength = this.tags.input.value.length;
			this.deleteItems();
			if (this.timeout)
				clearTimeout(this.timeout);
			if (this.ajax.xmlhttp.readyState != 0)
				this.ajax.xmlhttp.abort();
			this.timeout = setTimeout(function() {
				self.request();
			}, this.timeoutTime);
		}
	}
	this.onBlur = function() {
		this.hideItems();
	}
	this.onFocus = function() {
		this.showItems();
	}
	
	this.request = function() {
		var self = this;
		var q = this.lastValue();
		if (!q.length)
			return;
		var url = BASE_URL+this.uri+"/"+q;
		this.ajax.send(
			url,
			function(r){ 
				self.handleRequest(r); 
			}
		);
	}
	this.handleRequest = function(r) {
		if (r && r.items)
			this.renderItems(r.items);
	}
	this.showItems = function() {
		if (this.tags.items.length)
			this.tags.itemsWrap.addClass("active");
	}
	this.hideItems = function() {
		this.tags.itemsWrap.removeClass("active");
	}
	this.deleteItems = function() {
		this.tags.itemsWrap.removeClass("active");
		this.tags.itemsWrap.innerHTML = "";
		this.itemSetActive(-1);
		this.tags.items = [];
	}
	this.renderItems = function(items) {
		var self = this;
		this.items = items;
		if (items.length)
			this.tags.itemsWrap.addClass("active");
		for (var i=0; i<items.length; i++) {
			this.tags.items[i] = document.createElement("div");
			this.tags.items[i].className = "autocomplete-item";
			this.tags.items[i].textContent = items[i];
			this.tags.itemsWrap.appendChild(this.tags.items[i]);
			(function(n) {
				self.tags.items[n].addEventListener("click", function(){ self.itemClick(n); }, false);
				self.tags.items[n].addEventListener("mouseover", function(){ self.itemOver(n); }, false);
			}(i));
		}
	}
	
	this.itemUp = function() {
		if (this.itemActive == -1)
			var n = this.items.length-1;
		else
			var n = (this.itemActive-1+this.items.length)%this.items.length;
		this.itemSetActive(n);
	}
	this.itemDown = function() {
		var n = (this.itemActive+1)%this.items.length;
		this.itemSetActive(n);
	}
	this.itemEnter = function() {
		if (this.items[this.itemActive])
			this.itemChoose(this.itemActive);
	}
	this.itemChoose = function(n) {
		var val = this.tags.input.value;
		var x = val.lastIndexOf(",");
		if (x == -1)
			val = this.items[n];
		else
			val = val.substr(0, x)+", "+this.items[n];
		this.tags.input.value = val;
		this.deleteItems();
	}
	this.itemSetActive = function(n) {
		if (this.itemActive != -1 && this.items[this.itemActive])
			this.tags.items[this.itemActive].removeClass("active");
		if (n != -1 && this.items[n])
			this.tags.items[n].addClass("active");
		this.itemActive = n;
	}
	this.itemOver = function(n) {
		this.itemSetActive(n);
	}
	this.itemClick = function(n) {
		this.itemChoose(n);
	}
	
	this.init();
	
}

window.addEventListener("load", autocompleteTagsInit, false);