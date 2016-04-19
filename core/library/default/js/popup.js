var _popups = {};

function popupInit() {
  var els = document.getElementsByClassName("popup");
  while(els.length) {
    var name = els[0].getAttribute("name");
    _popups[name] = new popup(els[0]);
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        popupObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      popupObserve(document.body);
    }, 1000);
  }
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
  this.temporary = false;
  
  this.move = function(el) {
    el.removeClass("popup");
    if (el.getAttribute("temporary"))
      this.temporary = true;
    this.create(el.getAttribute("name"), el.getAttribute("close"));
    this.setContent(el);
    if (el.getAttribute("size"))
      this.setSize(el.getAttribute("size"));
  }
  
  this.create = function(name, close) {
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
    this.tags.close.innerHTML = close;
    this.tags.close.appendChild(FontAwesome.icon("times"));
    this.tags.wrap.appendChild(this.tags.dark);
    this.tags.wrap.appendChild(this.tags.light);
    this.tags.light.appendChild(this.tags.inner);
    this.tags.light.appendChild(this.tags.close);
    this.tags.dark.addEventListener("click", function(){ self.close(); }, false);
    this.tags.close.addEventListener("click", function(){ self.close(); }, false);
    document.body.appendChild(this.tags.wrap);
  }
  
  this.destroy = function() {
    document.body.removeChild(this.tags.wrap);
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
    var self = this;
    this.tags.wrap.style.display = "block";
    setTimeout(function() {
      var top = scrollTop()+Math.max(20, (window.innerHeight-self.tags.light.offsetHeight)/2);
      self.tags.light.style.top = top+"px";
      self.tags.wrap.addClass("open");
    }, 1);
  }
  this.close = function() {
    var self = this;
    var t = getStyle(this.tags.wrap, "transition-duration");
    t = parseInt(parseFloat(t.replace("s", ""))*1000);
    self.tags.wrap.removeClass("open");
    setTimeout(function() {
      self.tags.wrap.style.display = "none";
      self.onClose();
    },t);
  }
  this.onClose = function() {
    if (this.temporary)
      this.destroy();
  }
  
  if (el)
    this.move(el);
  
}

window.addEventListener("load", popupInit, false);