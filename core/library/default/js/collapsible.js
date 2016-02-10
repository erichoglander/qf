var _collapsibles = [];
function collapsibleInit() {
  var els = document.getElementsByClassName("collapsible");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("collapsible-init")) {
      els[i].addClass("collapsible-init");
      _collapsibles.push(new collapsible(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        collapsibleObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function(){
      collapsibleObserve(document.body);
    }, 1000);
  }
}
function collapsibleObserve(el) {
  var els = el.getElementsByClassName("collapsible");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("collapsible-init")) {
      els[i].addClass("collapsible-init");
      _collapsibles.push(new collapsible(els[i]));
    }
  }
}

function collapsible(el) {
  
  this.tags = {
    wrap: el
  };
  
  this.init = function() {
    
    var self = this;
    
    this.name = this.tags.wrap.getAttribute("name");
    this.remember = this.name && this.tags.wrap.hasClass("remember");
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
        this.tags.inner = this.tags.content.childNodes[i];
        break;
      }
    }
    
    if (this.remember) {
      var cookie = document.cookie;
      var r = new RegExp("collapsible-"+this.name+"=([a-z]+)");
      var m = cookie.match(r);
      if (m) {
        if (m[1] == "open" && !this.isOpen() || m[1] == "closed" && this.isOpen())
          this.toggleFast();
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
  
  this.toggleFast = function() {
    if (this.isOpen())
      this.closeFast();
    else
      this.openFast();
  }
  this.openFast = function() {
    var self = this;
    this.tags.wrap.removeClass("collapsed");
    this.tags.wrap.addClass("expanded");
    this.tags.content.addClass("no-transition");
    setTimeout(function() {
      self.tags.content.style.height = "auto";
      self.tags.content.style.overflow = "visible";
      self.tags.content.removeClass("no-transition");
      setTimeout(function() {
        self.tags.content.removeClass("no-transition");
      }, 1);
    }, 1);
  }
  this.closeFast = function() {
    var self = this;
    this.tags.wrap.removeClass("expanded");
    this.tags.wrap.addClass("collapsed");
    this.tags.content.addClass("no-transition");
    this.tags.content.style.height = "0";
    this.tags.content.style.overflow = "hidden";
    setTimeout(function() {
      self.tags.content.removeClass("no-transition");
    }, 1);
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
        self.tags.content.style.overflow = "visible";
        setTimeout(function() {
          self.tags.content.removeClass("no-transition");
        }, 1);
      }, self.getTransition());
    }, 1);
    if (this.remember) 
      document.cookie = "collapsible-"+this.name+"=open";
  }
  this.close = function() {
    var self = this;
    this.tags.wrap.removeClass("expanded");
    this.tags.wrap.addClass("collapsed");
    this.tags.content.addClass("no-transition");
    this.tags.content.style.height = this.tags.inner.offsetHeight+"px";
    this.tags.content.style.overflow = "hidden";
    setTimeout(function() {
      self.tags.content.removeClass("no-transition");
      setTimeout(function(){
        self.tags.content.style.height = "0";
      }, 1);
    }, 1);
    if (this.remember) 
      document.cookie = "collapsible-"+this.name+"=closed";
  }
  this.isOpen = function() {
    return (this.tags.wrap.className.match("expanded") ? true : false);
  }
  
  this.init();
  
}

window.addEventListener("load", collapsibleInit, false);