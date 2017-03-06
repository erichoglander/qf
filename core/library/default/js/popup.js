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
  this.loader = FontAwesome.icon("refresh fa-spin popup-loader");
  this.adaptive_height = false;
  this.margin = 20;
  
  this.onResize = function() {
    if (this.adaptive && this.tags.inner) {
      var h = window.innerHeight;
      h-= parseInt(document.body.getStyle("margin-top"));
      h-= parseInt(this.tags.inner.getStyle("margin-top"));
      h-= parseInt(this.tags.inner.getStyle("margin-bottom"));
      h-= this.margin*2;
      this.tags.inner.style.maxHeight = h+"px";
    }
  }
  
  this.move = function(el) {
    el.removeClass("popup");
    if (el.getAttribute("temporary"))
      this.temporary = true;
    this.create(el.getAttribute("name"), el.getAttribute("close"));
    this.setContent(el);
    if (el.getAttribute("size"))
      this.setSize(el.getAttribute("size"));
    if (el.getAttribute("auto-open") !== null)
      this.open();
    this.on_close = el.getAttribute("onclose");
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
    if (this.adaptive)
      this.tags.wrap.addClass("popup-adaptive");
    this.tags.dark.className = "popup-dark";
    this.tags.light.className = "popup-light";
    this.tags.inner.className = "popup-inner";
    this.tags.close.className = "popup-close";
    if (close)
      this.tags.close.innerHTML = close;
    this.tags.close.appendChild(FontAwesome.icon("times"));
    this.tags.wrap.appendChild(this.tags.dark);
    this.tags.wrap.appendChild(this.tags.light);
    this.tags.light.appendChild(this.tags.inner);
    this.tags.light.appendChild(this.tags.close);
    this.tags.dark.addEventListener("click", function(){ self.close(); }, false);
    this.tags.close.addEventListener("click", function(){ self.close(); }, false);
    document.body.appendChild(this.tags.wrap);
    this.onResize();
    if (this.adaptive)
      window.addEventListener("resize", function(){ self.onResize(); }, false);
  }
  
  this.destroy = function() {
    document.body.removeChild(this.tags.wrap);
  }
  
  this.setSize = function(size) {
    this.tags.wrap.className = this.tags.wrap.className.replace(/popup\-size\-[a-z]+/, "popup-size-"+size);
  }
  
  this.setContent = function(content) {
    if (typeof(content) == "object") {
      this.tags.inner.innerHTML = "";
      this.tags.inner.appendChild(content);
    }
    else {
      this.tags.inner.innerHTML = content;
    }
  }
  
  this.loadContent = function(content) {
    var self = this;
    if (typeof(content) == "string") {
      var x = content.lastIndexOf(".");
      if (x != -1) {
        var ext = content.substr(x+1).toLowerCase();
        var img_ext = ["jpg", "jpeg", "gif", "png"];
        if (img_ext.indexOf(ext) != -1) {
          var img = document.createElement("img");
          img.src = content;
          img.alt = "";
          content = img;
        }
      }
    }
    var wrap = document.createElement("div");
    wrap.appendChild(content);
    var imgs = wrap.getElementsByTagName("img");
    var total = imgs.length;
    if (total > 0) {
      this.setContent(this.loader);
      var loaded = 0;
      var callback = function() {
        loaded++;
        if (loaded == total) {
          self.setContent(content);
          self.adjustPosition();
        }
      };
      for (var i=0; i<imgs.length; i++) {
        (function(im) {
          im.addEventListener("load", function(){ callback(); }, false);
        }(imgs[i]));
      }
    }
    else {
      this.setContent(content);
      this.adjustPosition();
    }
    this.open();
  }
  
  this.adjustPosition = function() {
    var top = 
      scrollTop() +
      Math.max(this.margin, 
        ( window.innerHeight -
          this.tags.light.offsetHeight + 
          parseInt(document.body.getStyle("margin-top"))
        )/2);
    this.tags.light.style.top = top+"px";
  }
  
  this.isOpen = function() {
    return this.tags.wrap.hasClass("open");
  }
  this.open = function() {
    var self = this;
    this.tags.wrap.style.display = "block";
    setTimeout(function() {
      self.adjustPosition();
      self.tags.wrap.addClass("open");
      var focus = self.tags.wrap.getElementByClassName("popup-focus");
      if (focus)
        focus.focus();
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
    if (this.on_close) {
      if (typeof(this.on_close) == "string")
        eval(this.on_close);
      else
        this.on_close(this);
    }
  }
  
  if (el)
    this.move(el);
  
}

function popupLoad(content, opt) {
  if (!content)
    return;
  if (!opt)
    opt = {};
  var p = new popup();
  p.temporary = true;
  p.adaptive = true;
  p.create("load", opt.close ? opt.close : null);
  if (opt.size)
    p.setSize(opt.size);
  p.loadContent(content);
}

window.addEventListener("load", popupInit, false);