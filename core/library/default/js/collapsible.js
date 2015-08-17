function collapsible(el) {
	
	this.tags = {
		wrap: el
	};
	
	this.init = function() {
		
		var self = this;
		
		this.tags.title = null;
		this.tags.content = null;
		this.tags.inner = null;
		for (var i=0; i<this.tags.wrap.childNodes.length; i++) {
			if (!this.tags.title && this.tags.wrap.childNodes[i].nodeType == 1) {
				this.tags.title = this.tags.wrap.childNodes[i];
			}
			else if (!this.tags.content && this.tags.wrap.childNodes[i].nodeType == 1) {
				this.tags.content = this.tags.wrap.childNodes[i];
				break;
			}
		}
		for (var i=0; i<this.tags.content.childNodes.length; i++) {
			if (this.tags.content.childNodes[i].nodeType == 1) {
				this.tags.inner = this.tags.content.childNodes[1];
				break;
			}
		}
		
		this.tags.title.addEventListener("click", function(){ self.toggle(); }, false);
		
		this.tags.title.addClass("collapsible-title");
		this.tags.content.addClass("collapsible-content");
		this.tags.wrap.addClass("collapsible-init");
		
	}
	
	this.getTransition = function() {
		var duration = getStyle(this.tags.content, "transition-duration");
		if (!duration)
			duration = getStyle(this.tags.content, "-webkit-transition-duration");
		return parseFloat(duration)*1000;
	}
	
	this.toggle = function() {
		if (this.isOpen())
			this.close();
		else
			this.open();
	}
	this.open = function() {
		var self = this;
		this.tags.wrap.removeClass("collapsed");
		this.tags.wrap.addClass("expanded");
		this.tags.content.style.height = "0px";
		setTimeout(function() {
			self.tags.content.style.height = self.tags.inner.offsetHeight+"px";
			setTimeout(function(){
				self.tags.content.addClass("no-transition");
				self.tags.content.style.height = "auto";
				setTimeout(function() {
					self.tags.content.removeClass("no-transition");
				}, 1);
			}, self.getTransition());
		}, 1);
	}
	this.close = function() {
		var self = this;
		this.tags.wrap.removeClass("expanded");
		this.tags.wrap.addClass("collapsed");
		this.tags.content.addClass("no-transition");
		this.tags.content.style.height = this.tags.inner.offsetHeight+"px";
		setTimeout(function() {
			self.tags.content.removeClass("no-transition");
			setTimeout(function(){
				self.tags.content.style.height = "0";
			}, 1);
		}, 1);
	}
	this.isOpen = function() {
		return (this.tags.wrap.className.match("expanded") ? true : false);
	}
	
	this.init();
	
}