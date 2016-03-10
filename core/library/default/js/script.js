function isIE9() {
  return navigator.appVersion.indexOf("MSIE 9") > 0;
}
if (isIE9()) {
  Object.setPrototypeOf = function(obj, proto) {
    for (var prop in proto)
      obj[prop] = proto[prop];
    return obj;
  }
}

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

Element.prototype.hasClass = function(cname) {
  return (this.className.match("(^| )"+cname+"( |$)") ? true : false);
}

if (typeof(document.getElementsByClassName) != "function") {
  HTMLDocument.prototype.getElementsByClassName = function(cname) {
    var a = [];
    var re = new RegExp('(^| )'+cname+'( |$)');
    var els = this.getElementsByTagName("*");
    for(var i=0,j=els.length; i<j; i++)
        if(re.test(els[i].className))a.push(els[i]);
    return a;
  }
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
  HTMLDocument.prototype.getElementByClassName = function(cname) {
    var els = this.getElementsByClassName(cname);
    if (els.length)
      return els[0];
    return null;
  }
}
Element.prototype.getElementByClassName = function(cname) {
  var els = this.getElementsByClassName(cname);
  if (els.length)
    return els[0];
  return null;
}

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

Element.prototype.expand = function() {
  var t = getStyle(this, "transition-duration");
  if (!t)
    t = getStyle(this, "-webkit-transition-duration");
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
  }, 1);
}
Element.prototype.collapse = function(height) {
  if (!height)
    height = 0;
  else if (typeof(height) == "number")
    height = height+"px";
  var t = getStyle(this, "transition-duration");
  if (!t)
    t = getStyle(this, "-webkit-transition-duration");
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
    }, 1);
  }, 1);
}
Element.prototype.expandCollapse = function(height) {
  if (this.hasClass("active"))
    this.collapse(height);
  else
    this.expand();
}

if (typeof(window.addEventListener) != "function") {
  Element.prototype.addEventListener = function(ev, func, nothing) {
    window.attachEvent("on"+ev, func);
  }
}

function getStyle(el, prop) {
  if (el.currentStyle) {
    var x = prop.indexOf("-");
    while (x != -1) {
      if (x === 0) 
        prop = prop.substr(1);
      else
        prop = prop.substr(0, x)+prop.substr(x+1,1).toUpperCase()+prop.substr(x+2);
      x = prop.indexOf("-");
    }
    var y = el.currentStyle[prop];
  }
  else if (window.getComputedStyle)
    var y = document.defaultView.getComputedStyle(el, null).getPropertyValue(prop);
  else
    var y = el.style[prop];
  return y;
}

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
function scrollToEl(el, d) {
  if (typeof(el) == "string")
    el = document.getElementById(el);
  if (!el)
    return;
  var y = getTopPos(el);
  if (document.body.className.match("admin-menu"))
    y-= 30;
  smoothScroll(y, d);
}
function ease(t) {
  return (t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1);
}
function scrollTop() {
  var doc = document.documentElement, body = document.body;
  return (doc && doc.scrollTop || body && body.scrollTop  || 0);
}
function bodyHeight() {
  return Math.max( 
    document.body.scrollHeight, 
    document.body.offsetHeight, 
    document.documentElement.clientHeight, 
    document.documentElement.scrollHeight, 
    document.documentElement.offsetHeight);
}
function getTopPos(el) {
  for (var y=0; el != null; y += el.offsetTop, el = el.offsetParent);
  return y;
}
function getPos(el) {
  for (var pos={x: 0, y:0}; el != null; pos.x+= el.offsetLeft, pos.y+= el.offsetTop, el = el.offsetParent);
  return pos;
}
function getXY(e) {
  evt = (e.type == "touchmove" || e.type == "touchstart" ? e.touches[0] : (e.type == "touchend" ? e.changedTouches[0] : e));
  return {x: evt.clientX, y: evt.clientY};
}