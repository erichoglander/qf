var _sliders = [];
function sliderInit() {
  var els = document.getElementsByClassName("slider");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("slider-init")) {
      els[i].addClass("slider-init");
      _sliders.push(new slider(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        sliderObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      sliderObserve(document.body);
    }, 1000);
  }
}
function sliderObserve(el) {
  var els = el.getElementsByClassName("slider");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("slider-init")) {
      els[i].addClass("slider-init");
      _sliders.push(new slider(els[i]));
    }
  }
}


function slider(el) {
  
  this.tags = {
    wrap: el
  };
  
  this.init = function() {
    
    // Config
    this.config = {
      swipe: true,
      timeout: null,
      pauseHover: false,
      transition: "slide"
    };
    for (var k in this.config) {
      var val = this.tags.wrap.getAttribute(k);
      if (val) {
        if (typeof(this.config[k]) == "boolean")
          val = !!parseInt(val);
        this.config[k] = val;
      }
    }
    
    // DOM
    this.animationFrame();
    this.adjustDOM();
    this.bindEvents();
    
    // Defaults
    this.autoTimeout = null;
    this.sliding = false;
    this.dragging = false;
    this.page = 0;
    this.part = 0;
    this.oldpage = 0;
    this.oldpart = 0;
    this.partFirst = 0;
    this.partOffset = 0;
    this.mouseIsDown = false;
    this.mouseX = 0;
    this.mouseStartX = 0;
    this.values = {
      left: 0
    };
    this.pages = this.tags.parts[0].slides.length;
    
  }
  
  this.adjustDOM = function() {
    
    // Parts and slides
    this.tags.slides = this.tags.wrap.getElementByClassName("slides");
    this.tags.parts = [
      { wrap: this.tags.slides.cloneNode(true) },
      { wrap: this.tags.slides.cloneNode(true) }
    ];
    this.tags.slides.innerHTML = "";
    this.tags.inner = document.createElement("div");
    this.tags.inner.className = "inner";
    this.tags.slides.appendChild(this.tags.inner);
    for (var i=0; i<this.tags.parts.length; i++) {
      this.tags.parts[i].wrap.className = "part part-"+i;
      this.tags.inner.appendChild(this.tags.parts[i].wrap);
      this.tags.parts[i].slides = this.tags.parts[i].wrap.getElementsByClassName("slide");
      for (var j=0; j<this.tags.parts[i].slides.length; j++) {
        this.tags.parts[i].slides[j].className = "slide slide-"+j;
        if (this.config.transition == "slide")
          this.tags.parts[i].slides[j].style.left = (j*100)+"%";
      }
    }
    this.tags.parts[1].wrap.style.left = (100*this.tags.parts[0].slides.length)+"%";
    if (this.config.transition == "fade") {
      this.tags.parts[0].slides[0].addClass("active");
      this.tags.wrap.addClass("fade");
    }
    
    // Pagination
    this.tags.prev = this.tags.wrap.getElementByClassName("prev");
    this.tags.next = this.tags.wrap.getElementByClassName("next");
    this.tags.pager = {
      wrap: this.tags.wrap.getElementByClassName("pager")
    };
    if (this.tags.pager.wrap)
      this.tags.pager.pages = this.tags.pager.wrap.getElementsByClassName("page");
  }
  
  this.bindEvents = function() {
    var self = this;
    this.tags.wrap.addEventListener("mousedown", function(e){ self.mouseDown(e); });
    this.tags.wrap.addEventListener("touchstart", function(e){ self.mouseDown(e); });
    window.addEventListener("mouseup", function(e) { self.mouseUp(e); });
    window.addEventListener("touchend", function(e) { self.mouseUp(e); });
    window.addEventListener("mousemove", function(e) { self.mouseMove(e); });
    window.addEventListener("touchmove", function(e) { self.mouseMove(e); });
    if (this.tags.prev)
      this.tags.prev.addEventListener("click", function(){ self.prev(); });
    if (this.tags.next)
      this.tags.next.addEventListener("click", function(){ self.next(); });
    if (this.config.pauseHover) {
      this.tags.wrap.addEventListener("mouseover", function(){ self.mouseOver(); }, false);
      this.tags.wrap.addEventListener("mouseout", function(){ self.mouseOut(); }, false);
    }
    if (this.tags.pager.wrap) {
      for (var i=0; i<this.tags.pager.pages.length; i++) {
        (function(n) {
          self.tags.pager.pages[n].addEventListener("click", function(){ self.goto(n); }, false);
        }(i));
      }
    }
  }
  
  this.mouseDown = function(e) {
    if (e.button && e.button == 2)
      return;
    var trgt = e.target || e.srcElement;
    if (trgt.tagName && (trgt.tagName == "IMG" || trgt.tagName == "A") && e.preventDefault) {
      if (e.type != "touchstart" || this.dragging)
        e.preventDefault();
    }
    this.mouseIsDown = true;
    this.mouseStartX = this.mouseX = this.getX(e);
  }
  this.mouseUp = function(e) {  
    if (!this.mouseIsDown)
      return;
    this.mouseIsDown = false;
    var x = this.getX(e);
    if (this.dragging)
      this.dragStop();
    if (this.config.transition == "fade" && this.config.swipe) {
      var dx = x-this.mouseStartX;
      if (dx < -30)
        this.next();
      else if (dx > 30)
        this.prev();
    }
  }
  this.mouseMove = function(e) {
    if (!this.mouseIsDown || this.config.transition != "slide")
      return;
    if (e.preventDefault && (e.type != "touchmove" || this.dragging)) 
      e.preventDefault();
    var x = this.getX(e);
    var dx = Math.abs(x-this.mouseStartX);
    if (!this.dragging && dx > 20) {
      this.mouseStartX = x;
      this.dragStart();
    }
    if (this.dragging)
      this.dragMove(x);
  }
  this.mouseOver = function(e) {
    if (this.config.pauseHover) 
      clearTimeout(this.autoTimeout);
  }
  this.mouseOut = function(e) {
    if (this.config.pauseHover)
      this.auto();
  }
  
  /**
   * DRAGGING
   */
  this.dragStart = function() {
    if (this.sliding || !this.config.swipe)
      return;
    this.dragging = true;
  }
  this.dragStop = function() {
    
    this.dragging = false;
    var dx = this.mouseX - this.mouseStartX;
    this.oldpage = this.page;
    this.oldpart = this.part;
    
    // Slide back
    if (Math.abs(dx) < 30) 
      this.slide(Math.round(this.values.left/100)*100, "easeOut", 100);
    else {
    
      // If we slided forward
      if (dx < 0) {
        var x = Math.floor(this.values.left/100)*100;
        var n = 1;
      }
      // Backwards
      else {
        var x = Math.ceil(this.values.left/100)*100;
        var n = -1;
      }
      
      var p = this.page+n;
      
      // If we skipped forward a part
      if (p > this.pages-1) {
        this.part = (this.part+1)%2;
        this.page = p%this.pages;
      }
      // Backwards
      else if (p < 0) {
        this.part = (this.part+1)%2;
        this.page = (p+this.pages)%this.pages;
      }
      else
        this.page = p;
        
      this.slide(x, "easeOut");
    }
  }
  this.dragMove = function(x) {

    var p = ((x-this.mouseX)/this.tags.wrap.offsetWidth)*100;
    var val = this.values.left+p;
    // Forward
    if (p < 0) {
      var max = -((this.partOffset+2)*this.pages-1)*100;
      if (val < max)
        this.movePart(this.partFirst, 1);
    }
    // Backwards
    else if (p > 0) {
      var max = -this.partOffset*this.pages*100;
      if (val > max)
        this.movePart((this.partFirst+1)%2, -1);
    }

    this.mouseX = x;
    this.setLeft(val);
  }

  /*
  * BUTTON BROWSING
  */
  this.auto = function(t) {
    if (!this.config.timeout)
      return;
    var self = this;
    if (this.autoTimeout)
      clearTimeout(this.autoTimeout);
    this.autoTimeout = setTimeout(function(){ self.next(); }, (t ? t : self.config.timeout));
  }
  this.goto = function(n) {
    if (this.sliding || this.page == n || this.dragging)
      return;
    if (this.config.transition == "slide") {
      var stop = this.values.left + 100*(this.page-n);
      this.oldpage = this.page;
      this.oldpart = this.part;
      this.page = n;
      this.slide(stop, "ease");
    }
    else if (this.config.transition == "fade") {
      this.tags.parts[0].slides[this.page].removeClass("active");
      if (this.tags.pager.wrap)
        this.tags.pager.pages[this.page].removeClass("active");
      this.page = n;
      this.tags.parts[0].slides[this.page].addClass("active");
      if (this.tags.pager.wrap)
        this.tags.pager.pages[this.page].addClass("active");
      this.auto();
    }
  }
  this.next = function() {
    if (this.sliding || this.dragging)
      return;
    if (this.config.transition == "slide") {
      this.oldpage = this.page;
      this.oldpart = this.part;
      if (this.page == this.pages-1) {
        var n = 0;
        if (this.part != this.partFirst) 
          this.movePart(this.partFirst, 1);
        this.part = (this.part+1)%2;
      }
      else
        n = this.page+1;
      this.page = n;
      this.slide(this.values.left-100, "ease");
    }
    else if (this.config.transition == "fade") {
      this.tags.parts[0].slides[this.page].removeClass("active");
      if (this.tags.pager.wrap)
        this.tags.pager.pages[this.page].removeClass("active");
      this.page = (this.page+1)%this.pages;
      this.tags.parts[0].slides[this.page].addClass("active");
      if (this.tags.pager.wrap)
        this.tags.pager.pages[this.page].addClass("active");
      this.auto();
    }
  }
  this.prev = function() {
    if (this.sliding || this.dragging)
      return;
    if (this.config.transition == "slide") {
      this.oldpage = this.page;
      this.oldpart = this.part;
      if (this.page == 0) {
        var n = this.pages-1;
        if (this.part == this.partFirst) 
          this.movePart((this.partFirst+1)%2, -1);
        this.part = (this.part+1)%2;
      }
      else
        n = this.page-1;
      this.page = n;
      this.slide(this.values.left+100, "ease");
    }
    else if (this.config.transition == "fade") {
      this.tags.parts[0].slides[this.page].removeClass("active");
      if (this.tags.pager.wrap)
        this.tags.pager.pages[this.page].removeClass("active");
      this.page = (this.page+this.pages-1)%this.pages;
      this.tags.parts[0].slides[this.page].addClass("active");
      if (this.tags.pager.wrap)
        this.tags.pager.pages[this.page].addClass("active");
      this.auto();
    }
  }

  /*
  * MOVEMENT
  */
  this.movePart = function(n, dir) {
    this.tags.parts[n].wrap.style.left = (this.pages*100*(this.partOffset+(dir == 1 ? 2*dir : dir)))+"%";
    this.partOffset+= dir;
    this.partFirst = (this.partFirst+1)%2;
  }
  this.slide = function(value, anim, d) {
    if (this.sliding || this.values.left == value)
      return;
    this.sliding = true;
    Date.now = Date.now || function() { return +new Date; };
    var self = this;
    var current = this.values.left;
    var start = Date.now();
    if (!d)
      d = 500;
    function animLeft(t) {
      var now = Date.now();
      var time = Math.min(1, (now-start)/d);
      var a = self[anim](time);
      var set = a*(value-current)+current;
      self.setLeft(set);
      if (time < 1)
        requestAnimationFrame(animLeft);
      else
        self.slideDone();
    }
    requestAnimationFrame(animLeft);
  }
  this.slideDone = function() {
    this.sliding = false;
    if (this.tags.pager.wrap) {
      if (this.oldpage != this.page)
        this.tags.pager.pages[this.oldpage].removeClass("active");
      this.tags.pager.pages[this.page].addClass("active");
    }
    this.tags.parts[this.oldpart].slides[this.oldpage].removeClass("active");
    this.tags.parts[this.part].slides[this.page].addClass("active");
    if (this.config.timeout)
      this.auto();
  }
  this.setLeft = function(value) {
    this.values.left = value;
    var s = Array("transform", "webkitTransform");
    for (var i=0; i<s.length; i++) {
      if (s[i] in document.body.style) {
        this.tags.inner.style[s[i]] = "translate3d("+value+"%, 0, 0)";
        return;
      }
    }
    this.tags.inner.style.left = value+"%";
  }
  this.ease = function(t) {
    return (t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1);
  }
  this.easeOut = function(t) {
    return (--t)*t*t+1;
  }
  
  
  /**
   * HELPERS
   */
  this.animationFrame = function() {
    if (window.requestAnimationFrame)
      return;
    var lastTime = 0;
    var vendors = ['ms', 'moz', 'webkit', 'o'];
    for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
      window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
      window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
        || window[vendors[x]+'CancelRequestAnimationFrame'];
    }
    if (!window.requestAnimationFrame)
      window.requestAnimationFrame = function(callback, element) {
        var currTime = new Date().getTime();
        var timeToCall = Math.max(0, 16 - (currTime - lastTime));
        var id = window.setTimeout(function() { callback(currTime + timeToCall); },
        timeToCall);
        lastTime = currTime + timeToCall;
        return id;
      };
    if (!window.cancelAnimationFrame)
      window.cancelAnimationFrame = function(id) {
      clearTimeout(id);
    };
  }
  
  this.getX = function(e) {
    evt = (e.type == "touchmove" || e.type == "touchstart" ? e.touches[0] : (e.type == "touchend" ? e.changedTouches[0] : e));
    return (evt.pageX ? evt.pageX : evt.clientX + document.documentElement.scrollLeft);
  }
  
  this.init();
  
}

window.addEventListener("load", sliderInit, false);