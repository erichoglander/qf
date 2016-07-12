/**
 * Check if browser is IE9
 * @return bool
 */
function isIE9() {
  return navigator.appVersion.indexOf("MSIE 9") > 0;
}

// Enable prototypes for IE9
if (isIE9()) {
  Object.setPrototypeOf = function(obj, proto) {
    for (var prop in proto)
      obj[prop] = proto[prop];
    return obj;
  }
}

/**
 * Add a css class to element
 * @param string cname
 */
Element.prototype.addClass = function(cname) {
  if (!this.className) {
    this.className = cname;
  }
  else {
    var a = this.className.split(" ");
    if (a.indexOf(cname) == -1) {
      a.push(cname);
      this.className = a.join(" ");
    }
  }
}

/**
 * Remove a css class from element
 * @param string cname
 */
Element.prototype.removeClass = function(cname) {
  if (this.className) {
    if (this.className == cname) {
      this.className = "";
    }
    else {
      var a = this.className.split(" ");
      var i = a.indexOf(cname);
      if (i != -1) {
        a.splice(i, 1);
        this.className = a.join(" ");
      }
    }
  }
}

/**
 * Check if element has specified css class
 * @param  string cname
 * @return bool
 */
Element.prototype.hasClass = function(cname) {
  return (this.className.match("(^| )"+cname+"( |$)") ? true : false);
}

// Add getElementsByClassName() to older browsers
if (typeof(document.getElementsByClassName) != "function") {
  if (typeof(HTMLDocument) != "undefined") {
    /**
     * Get children with class name
     * @param string cname
     * @return array
     */

    HTMLDocument.prototype.getElementsByClassName = function(cname) {
      var a = [];
      var re = new RegExp('(^| )'+cname+'( |$)');
      var els = this.getElementsByTagName("*");
      for(var i=0,j=els.length; i<j; i++)
          if(re.test(els[i].className))a.push(els[i]);
      return a;
    }
  }
  if (typeof(Document) != "undefined") {
    /**
     * Get children with class name
     * @param string cname
     * @return array
     */
    Document.prototype.getElementsByClassName = function(cname) {
      var a = [];
      var re = new RegExp('(^| )'+cname+'( |$)');
      var els = this.getElementsByTagName("*");
      for(var i=0,j=els.length; i<j; i++)
          if(re.test(els[i].className))a.push(els[i]);
      return a;
    }
  }
  /**
   * Get children with class name
   * @param string cname
   * @return array
   */
  Element.prototype.getElementsByClassName = function(cname) {
    var a = [];
    var re = new RegExp('(^| )'+cname+'( |$)');
    var els = this.getElementsByTagName("*");
    for(var i=0,j=els.length; i<j; i++)
        if(re.test(els[i].className))a.push(els[i]);
    return a;
  }
}

if (typeof(HTMLDocument) != "undefined") {
  /**
   * Get first child with class name
   * @param string cname
   * @return \Element
   */
  HTMLDocument.prototype.getElementByClassName = function(cname) {
    var els = this.getElementsByClassName(cname);
    if (els.length)
      return els[0];
    return null;
  }
}
if (typeof(Document) != "undefined") {
  /**
   * Get first child with class name
   * @param  string cname
   * @return \Element
   */
  Document.prototype.getElementByClassName = function(cname) {
    var els = this.getElementsByClassName(cname);
    if (els.length)
      return els[0];
    return null;
  }
}

/**
 * Get first child with class name
 * @param string cname
 * @return \Element
 */
Element.prototype.getElementByClassName = function(cname) {
  var els = this.getElementsByClassName(cname);
  if (els.length)
    return els[0];
  return null;
}

/**
 * Trigger an event manually
 * @param string type
 */
Element.prototype.trigger = function(type) {
  if ("createEvent" in document) {
    var evt = document.createEvent("HTMLEvents");
    evt.initEvent(type, false, true);
    this.dispatchEvent(evt);
  }
  else {
    this.fireEvent("on"+type);
  }
}

/**
 * Animate an element with a class and display none/block at start/end
 * @param string cname
 */
Element.prototype.blockAnimate = function(cname) {
  var el = this;
  if (this.hasClass(cname)) {
    var t = this.getStyle("transition-duration");
    if (!t)
      t = 300;
    else
      t = parseFloat(t)*1000;
    this.removeClass(cname);
    this.setAttribute("block-animate", "");
    setTimeout(function() {
      if (el.getAttribute("block-animate") !== null) {
        el.style.display = "none";
        el.removeAttribute("block-animate");
      }
    },t);
  }
  else {
    this.removeAttribute("block-animate");
    this.style.display = "block";
    setTimeout(function() {
      el.addClass(cname);
    },10);
  }
}

/**
 * Expand element
 */
Element.prototype.expand = function() {
  var t = this.getStyle("transition-duration");
  if (!t)
    t = this.getStyle("-webkit-transition-duration");
  t = (t ? parseInt(parseFloat(t.replace("s", ""))*1000) : 0);
  var inner;
  for (var i=0; i<this.childNodes.length; i++) {
    if (this.childNodes[i].nodeType == 1) {
      inner = this.childNodes[i];
      break;
    }
  }
  if (!inner)
    return;
  var el = this;
  this.style.height = "0px";
  this.addClass("active");
  setTimeout(function() {
    el.style.height = inner.offsetHeight+"px";
    setTimeout(function(){
      el.addClass("no-transition");
      el.style.height = "auto";
      el.style.overflow = "visible";
      setTimeout(function() {
        el.removeClass("no-transition");
      }, 1);
    }, t);
  }, 10);
}

/**
 * Collapse element
 */
Element.prototype.collapse = function(height) {
  if (!height)
    height = 0;
  else if (typeof(height) == "number")
    height = height+"px";
  var t = this.getStyle("transition-duration");
  if (!t)
    t = this.getStyle("-webkit-transition-duration");
  t = (t ? parseInt(parseFloat(t.replace("s", ""))*1000) : 0);
  var inner;
  for (var i=0; i<this.childNodes.length; i++) {
    if (this.childNodes[i].nodeType == 1) {
      inner = this.childNodes[i];
      break;
    }
  }
  if (!inner)
    return;
  var el = this;
  this.removeClass("active");
  this.addClass("no-transition");
  this.style.height = inner.offsetHeight+"px";
  this.style.overflow = "hidden";
  setTimeout(function() {
    el.removeClass("no-transition");
    setTimeout(function(){
      el.style.height = height;
    }, 10);
  }, 1);
}

/**
 * Toggle for expand/collapse
 * @see Element::expand()
 * @see Element::collapse()
 */
Element.prototype.expandCollapse = function(height) {
  if (this.hasClass("active"))
    this.collapse(height);
  else
    this.expand();
}

// Add addEventListener prototype for older browsers
if (typeof(window.addEventListener) != "function") {
  /**
   * Add an event listener
   * @param \Event   ev
   * @param function func
   * @param bool     nothing Not used in IE implementation
   */
  Element.prototype.addEventListener = function(ev, func, nothing) {
    window.attachEvent("on"+ev, func);
  }
}

/**
 * Get specific element style
 * @param  string prop
 * @return string
 */
Element.prototype.getStyle = function(prop) {
  if (this.currentStyle) {
    var x = prop.indexOf("-");
    while (x != -1) {
      if (x === 0) 
        prop = prop.substr(1);
      else
        prop = prop.substr(0, x)+prop.substr(x+1,1).toUpperCase()+prop.substr(x+2);
      x = prop.indexOf("-");
    }
    var y = this.currentStyle[prop];
  }
  else if (window.getComputedStyle)
    var y = document.defaultView.getComputedStyle(this, null).getPropertyValue(prop);
  else
    var y = this.style[prop];
  return y;
}

/**
 * Get specific element style
 * Deprecated, exists for backwards compatibility
 * @param  string prop
 * @return string
 */
function getStyle(el, prop) {
  return el.getStyle(prop);
}

// Add animation support
(function() {
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
}());

/**
 * Smoothly scrolls the window
 * @param  int stop
 * @param  int d
 */
function smoothScroll(stop, d) {
  Date.now = Date.now || function(){ return +new Date; };
  var y = scrollTop();
  var start = Date.now();
  if (!d)
    d = 750;
  if (y == stop)
    return;
  function scroll(t) {
    var now = Date.now();
    var time = Math.min(1, (now-start)/d);
    var easedT = ease(time);
    window.scrollTo(0, easedT*(stop-y)+y);
    if (time < 1)
      requestAnimationFrame(scroll);
  }
  requestAnimationFrame(scroll);
}

/**
 * Smoothly scroll to element
 * @see smoothScroll()
 * @param \Element el
 * @param int      d
 */
function scrollToEl(el, d) {
  if (typeof(el) == "string")
    el = document.getElementById(el);
  if (!el)
    return;
  var y = getTopPos(el);
  var mt = document.body.getStyle("margin-top");
  y-= parseInt(mt);
  smoothScroll(y, d);
}

/**
 * Animation function for an "easing"-curve
 * @param  float t
 * @return float
 */
function ease(t) {
  return (t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1);
}

/**
 * Compatiblity function for body.scrollTop
 * @return int
 */
function scrollTop() {
  var doc = document.documentElement, body = document.body;
  return (doc && doc.scrollTop || body && body.scrollTop  || 0);
}

/**
 * Compatibility function for document.body.offsetHeight
 * @return int
 */
function bodyHeight() {
  return Math.max( 
    document.body.scrollHeight, 
    document.body.offsetHeight, 
    document.documentElement.clientHeight, 
    document.documentElement.scrollHeight, 
    document.documentElement.offsetHeight);
}

/**
 * Get y-position of element relative to the document
 * @return int
 */
Element.prototype.getTopPos = function() {
  if (this.getBoundingClientRect)
    return this.getBoundingClientRect().top + document.documentElement.scrollTop;
  var y = 0;
  var el = this;
  while (el != null) {
    y+= el.offsetTop;
    el = el.offsetParent;
    if (el)
      y+= parseFloat(el.getStyle("border-top-width"));
  }
  return y;
}
/**
 * Get y-position of element relative to the document
 * Deprecated, use Element::getTopPos()
 * @param  \Element el
 * @return int
 */
function getTopPos(el) {
  return el.getTopPos();
}

/**
 * Get position of element relative to the document
 * @return object
 */
Element.prototype.getPos = function() {
  if (this.getBoundingClientRect) {
    var rect = this.getBoundingClientRect();
    return {
      x: rect.left + document.documentElement.scrollLeft,
      y: rect.top + document.documentElement.scrollTop
    };
  }
  var pos = {
    x: 0, 
    y: 0
  };
  var el = this;
  while (el != null) {
    pos.x+= el.offsetLeft;
    pos.y+= el.offsetTop;
    el = el.offsetParent;
    if (el) {
      pos.x+= parseFloat(el.getStyle("border-left-width"));
      pos.y+= parseFloat(el.getStyle("border-top-width"));
    }
  }
  return pos;
}

/**
 * Get position of an element relative to the document
 * Deprecated, use Element::getPos()
 * @param  \Element el
 * @return object
 */
function getPos(el) {
  return el.getPos();
}

/**
 * Get coordinates of an event
 * @param  mixed  e
 * @return object
 */
function getXY(e) {
  var evt = (e.type == "touchmove" || e.type == "touchstart" ? e.touches[0] : (e.type == "touchend" ? e.changedTouches[0] : e));
  return {x: evt.clientX, y: evt.clientY};
}