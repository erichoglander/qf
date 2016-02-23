var _tabs = [];
function tabsInit() {
  var els = document.getElementsByClassName("tabs");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("tabs-init")) {
      els[i].addClass("tabs-init");
      _tabs.push(new tabs(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        tabsObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function(){
      tabsObserve(document.body);
    }, 1000);
  }
}
function tabsObserve(el) {
  var els = el.getElementsByClassName("tabs");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("tabs-init")) {
      els[i].addClass("tabs-init");
      _tabs.push(new tab(els[i]));
    }
  }
}

function tabs(el) {
	
	this.tags = {
		wrap: el
	};
	
	this.init = function() {
		this.active = -1;
		this.tags.btn = this.tags.wrap.getElementsByClassName("tab-btn");
		this.tags.body = this.tags.wrap.getElementsByClassName("tab-body");
		for (var i=0; i<this.tags.btn.length; i++) {
      if (this.tags.btn[i].hasClass("active"))
        this.active = i;
			if (this.tags.btn[i].tagName != "A") {
				if (this.tags.btn[i].getAttribute("name") && window.location.hash == "#"+this.tags.btn[i].getAttribute("name"))
					this.onClick(i);
				(function(self, n) {
					self.tags.btn[n].addEventListener("click", function(){ self.onClick(n); }, false);
				}(this, i));
			}
		}
    if (this.active == -1)
      this.onClick(0);
	}
	
	this.onClick = function(n) {
    if (this.active != -1) {
      this.tags.btn[this.active].removeClass("active");
      this.tags.body[this.active].removeClass("active");
    }
		this.tags.btn[n].addClass("active");
		this.tags.body[n].addClass("active");
		this.active = n;
	}
	
	this.init();
	
}

window.addEventListener("load", tabsInit, false);