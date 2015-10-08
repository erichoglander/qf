var _popups = {};

function popupInit() {
	var els = document.getElementsByClassName("popup");
	while(els.length) {
		var name = els[0].getAttribute("name");
		_popups[name] = new popup(els[0]);
	}
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			popupObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function popupObserve(el) {
	var els = el.getElementsByClassName("popup");
	while(els.length) {
		var name = els[0].getAttribute("name");
		_popups[name] = new popup(els[0]);
	}
}

function popup(el) {
	
	this.tags = {};
	
	this.move = function(el) {
		el.removeClass("popup");
		this.create(el.getAttribute("name"));
		this.setContent(el);
		if (el.getAttribute("size"))
			this.setSize(el.getAttribute("size"));
	}
	
	this.create = function(name) {
		var self = this;
		this.tags = {
			wrap: document.createElement("div"),
			dark: document.createElement("div"),
			light: document.createElement("div"),
			inner: document.createElement("div"),
			close: document.createElement("div")
		};
		this.tags.wrap.className = "popup-wrap popup-size-large";
		if (name) {
			this.name = name;
			this.tags.wrap.addClass("popup-name-"+name);
			this.tags.wrap.setAttribute("name", name);
		}
		this.tags.dark.className = "popup-dark";
		this.tags.light.className = "popup-light";
		this.tags.inner.className = "popup-inner";
		this.tags.close.className = "popup-close";
		this.tags.close.appendChild(FontAwesome.icon("times"));
		this.tags.wrap.appendChild(this.tags.dark);
		this.tags.wrap.appendChild(this.tags.light);
		this.tags.light.appendChild(this.tags.inner);
		this.tags.light.appendChild(this.tags.close);
		this.tags.dark.addEventListener("click", function(){ self.close(); }, false);
		this.tags.close.addEventListener("click", function(){ self.close(); }, false);
		document.body.appendChild(this.tags.wrap);
	}
	
	this.setSize = function(size) {
		this.tags.wrap.className = this.tags.wrap.className.replace(/popup\-size\-[a-z]+/, "popup-size-"+size);
	}
	
	this.setContent = function(content) {
		if (typeof(content) == "object")
			this.tags.inner.appendChild(content);
		else
			this.tags.inner.innerHTML = content;
	}
	
	this.isOpen = function() {
		return this.tags.wrap.hasClass("open");
	}
	this.open = function() {
		var t = scrollTop()+Math.max(20, (window.innerHeight-this.tags.light.offsetHeight)/2);
		this.tags.light.style.top = t+"px";
		this.tags.wrap.addClass("open");
	}
	this.close = function() {
		this.tags.wrap.removeClass("open");
	}
	
	if (el)
		this.move(el);
	
}

window.addEventListener("load", popupInit, false);