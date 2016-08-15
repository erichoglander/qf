/**
 * Slider
 *
 * Similar to formSlider.js but with one value instead of two.
 * Name was prefixed because it collided with slider.js which is a slideshow.
 * In afterthough, slider.js should've been named slideshow.js
 * or maybe all form related scripts should've been prefixed.
 * I'm not good with naming things.
 *
 * @author Eric Höglander
 */
 
var _formSliders = [];
function formSliderInit() {
  var els = document.getElementsByClassName("form-slider");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("form-slider-init")) {
      els[i].addClass("form-slider-init");
      _formSliders.push(new formSlider(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        formSliderObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      formSliderObserve(document.body);
    }, 1000);
  }
}
function formSliderObserve(el) {
  var els = el.getElementsByClassName("form-slider");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("form-slider-init")) {
      els[i].addClass("form-slider-init");
      _formSliders.push(new formSlider(els[i]));
    }
  }
}

function formSlider(el) {
  
  this.tags = {
    wrap: el
  };
  
  this.init = function() {
    var self = this;
    this.tags.wrap.addClass("form-slider-init");
    this.tags.item = formGetItem(this.tags.wrap);
    this.suffix = this.tags.wrap.getAttribute("slider_suffix");
    this.min = this.tags.wrap.getAttribute("slider_min");
    this.max = this.tags.wrap.getAttribute("slider_max");
    this.round = this.tags.wrap.getAttribute("slider_round");
    if (this.round.match("."))
      this.round = parseFloat(this.round);
    else
      this.round = parseInt(this.round);
    this.value_follow = this.tags.wrap.getAttribute("slider_value_follow") == "1";
    this.min = (this.round%1 ? parseFloat(this.min) : parseInt(this.min));
    this.max = (this.round%1 ? parseFloat(this.max) : parseInt(this.max));
    this.tags.container = this.tags.wrap.getElementsByClassName("form-slider-slider")[0];
    this.dragging = false;
    this.drag_pos = 0;
    window.addEventListener("mousemove", function(e){ self.onMouseMove(e); }, false);
    window.addEventListener("mouseup", function(e){ self.onMouseUp(e); }, false);
    window.addEventListener("touchmove", function(e){ self.onMouseMove(e); }, false);
    window.addEventListener("touchend", function(e){ self.onMouseUp(e); }, false);
    this.create();
    if (this.tags.item.hasClass("form-slider-dropdown")) {
      this.tags.label = this.tags.item.getElementsByClassName("form-label")[0];
      this.tags.label.addEventListener("click", function(){ self.toggle(); }, false);
      window.addEventListener("click", function(e){ self.windowClick(e); }, false);
    }
  }
  
  this.toggle = function() {
    if (this.isOpen())
      this.close();
    else
      this.open();
  }
  this.open = function() {
    this.tags.item.addClass("active");
  }
  this.close = function() {
    this.tags.item.removeClass("active");
  }
  this.isOpen = function() {
    if (this.tags.item.hasClass("active"))
      return true;
    return false;
  }
  
  this.windowClick = function(e) {
    if (!this.isOpen())
      return;
    for (var i=0, el = e.target; i<10 && el != null && el != this.tags.item; i++, el = el.parentNode);
    if (!el || i == 10)
      this.close();
  }
  
  this.onMouseDown = function(e) {
    e.preventDefault();
    this.dragging = true;
    this.drag_pos = getPos(this.tags.slider.outer);
  }
  this.onMouseUp = function(e) {
    this.dragging = false;
  }
  this.onMouseMove = function(e) {
    if (!this.dragging)
      return;
    e.preventDefault();
    var xy = getXY(e);
    var width = this.tags.slider.outer.offsetWidth;
    var x = Math.min(Math.max(xy.x - this.drag_pos.x, 0), width)/width;
    var val = Math.round((x*(this.max-this.min)+this.min)/this.round)*this.round;
    
    if (val == this.tags.value.input.value)
      return;
    
    this.setValue(val);
    this.lastXY = xy;
  }
  
  this.numberFormat = function(val) {
    if (val < 100)
      return val+this.suffix;
    var str = val.toString();
    var r = "";
    for (var i=0; i<str.length; i++) {
      if (i%3 == str.length%3 && i != 0)
        r+= " ";
      r+= str[i];
    }
    r+= this.suffix;
    return r;
  }
  
  this.getPoint = function() {
    return parseFloat(this.tags.value.point.style.left);
  }
  this.setPoint = function(value) {
    this.tags.value.point.style.left = value+"%";
  }
  
  this.getValue = function() {
    var val = this.tags.value.input.value;
    if (this.round%1)
      val = parseFloat(val);
    else
      val = parseInt(val);
    return val;
  }
  this.setValue = function(value) {
    var x = (value-this.min)/(this.max-this.min)*100;
    this.tags.value.input.value = value;
    this.tags.value.text.textContent = this.numberFormat(value);
    this.updatePosition();
    this.tags.wrap.trigger("change");
  }
  
  this.updatePosition = function() {
    var value = this.tags.value.input.value;
    var x = (value-this.min)/(this.max-this.min)*100;
    this.setPoint(x);
    this.tags.slider.inner.style.width = x+"%";
  }
  
  this.create = function() {
    var self = this;
    var inp = this.tags.wrap.getElementsByTagName("input");
    
    this.tags.value = {
      point: document.createElement("div"),
      text: document.createElement("div"),
      input: inp[0]
    };
    this.tags.value.point.className = "slider-point";
    this.tags.value.text.className = "slider-text";
    var minp = document.createElement("div");
    minp.className = "slider-point-inner";
    this.tags.value.point.appendChild(minp);
    this.tags.value.text.textContent = this.numberFormat(this.tags.value.input.value);
    this.tags.value.point.addEventListener("mousedown", function(e) { self.onMouseDown(e); }, false);
    this.tags.value.point.addEventListener("touchstart", function(e) { self.onMouseDown(e); }, false);
    
    this.tags.slider = {
      outer: document.createElement("div"),
      inner: document.createElement("div")
    };
    this.tags.slider.outer.className = "slider-outer";
    this.tags.slider.inner.className = "slider-inner";
    
    this.updatePosition();
    
    this.tags.slider.outer.appendChild(this.tags.slider.inner);
    this.tags.slider.outer.appendChild(this.tags.value.point);
    if (this.value_follow) {
      this.tags.value.point.appendChild(this.tags.value.text);
      this.tags.container.appendChild(this.tags.slider.outer);
    }
    else {
      this.tags.container.appendChild(this.tags.value.text);
      this.tags.container.appendChild(this.tags.slider.outer);
    }
  }
  
  this.init();
  
}

window.addEventListener("load", formSliderInit, false);