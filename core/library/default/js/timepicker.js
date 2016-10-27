var _timepickers = [];
function timepickerInit() {
  var els = document.getElementsByClassName("form-time");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("timepicker-init")) {
      els[i].addClass("timepicker-init");
      _timepickers.push(new timepicker(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        timepickerObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      timepickerObserve(document.body);
    }, 1000);
  }
}
function timepickerObserve(el) {
  var els = el.getElementsByClassName("form-time");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("timepicker-init")) {
      els[i].addClass("timepicker-init");
      _timepickers.push(new timepicker(els[i]));
    }
  }
}

function timepicker(el) {
  
  this.tags = {
    wrap: el.parentNode,
    input: el,
  };
  this.value = {h: 0, m: 0};

  this.init = function() {
    var self = this;
    this.tags.input.addEventListener("click", function(){ self.toggle(); }, false);
    window.addEventListener("click", function(e){ self.windowClick(e); }, false);
    if (this.tags.input.value.length) {
      var arr = this.tags.input.value.split(":");
      this.value.h = arr[0];
      this.value.m = arr[1];
    }
    this.render();
  }

  this.render = function() {
    this.tags.picker = document.createElement("div");
    this.tags.picker.className = "timepicker";
    var inner = document.createElement("div");
    inner.className = "timepicker-inner";
    this.tags.hour = document.createElement("div");
    this.tags.hour.className = "timepicker-hours";
    this.tags.minute = document.createElement("div");
    this.tags.minute.className = "timepicker-minutes";
    this.tags.hours = [];
    this.tags.minutes = [];
    for (var i=1; i<=24; i++) {
      this.tags.hours[i] = document.createElement("div");
      this.tags.hours[i].className = "timepicker-hour timepicker-hour-"+i;
      if (i == this.value.h)
        this.tags.hours[i].className+= " active";
      this.tags.hours[i].textContent = i%24;
      var x = (i > 12 ? i-12 : i);
      var d = (i > 12 ? 40 : 28);
      var a = 2 * Math.PI * x / 12;
      var x = 50 + d * Math.sin(a);
      var y = 50 + d * Math.cos(a);
      this.tags.hours[i].style.left = x+"%";
      this.tags.hours[i].style.top = (100-y)+"%";
      this.tags.hour.appendChild(this.tags.hours[i]);
      (function(self, n) {
        self.tags.hours[i].addEventListener("click", function(){ self.hourClick(n); }, false);
      }(this, i));
    }
    for (var i=0; i<60; i++) {
      this.tags.minutes[i] = document.createElement("div");
      this.tags.minutes[i].className = "timepicker-minute timepicker-minute-"+i;
      if (i%5 == 0)
        this.tags.minutes[i].className+= " timepicker-minute-five";
      if (i == this.value.m)
        this.tags.minutes[i].className+= " active";
      this.tags.minutes[i].textContent = i;
      var d = 40;
      var a = 2 * Math.PI * i / 60;
      var x = 50 + d * Math.sin(a);
      var y = 50 + d * Math.cos(a);
      this.tags.minutes[i].style.left = x+"%";
      this.tags.minutes[i].style.top = (100-y)+"%";
      this.tags.minute.appendChild(this.tags.minutes[i]);
      (function(self, n) {
        self.tags.minutes[i].addEventListener("click", function(){ self.minuteClick(n); }, false);
      }(this, i));
    }
    inner.appendChild(this.tags.hour);
    inner.appendChild(this.tags.minute);
    this.tags.picker.appendChild(inner);
    this.tags.wrap.appendChild(this.tags.picker);
  }

  this.renderValue = function() {
    this.tags.input.value = this.addZero(this.value.h%24)+":"+this.addZero(this.value.m);
    this.tags.input.trigger("change");
  }

  this.addZero = function(val) {
    if (val < 10)
      return "0"+val;
    return val;
  }

  this.toggle = function() {
    if (this.isOpen())
      this.close();
    else
      this.open();
  }
  this.isOpen = function() {
    return this.tags.wrap.hasClass("timepicker-open");
  }
  this.open = function() {
    this.tags.wrap.addClass("timepicker-open");
    this.hourOpen();
  }
  this.close = function() {
    this.minuteClose();
    this.hourClose();
    this.tags.wrap.removeClass("timepicker-open");
  }

  this.hourIsOpen = function() {
    return this.tags.wrap.hasClass("timepicker-hour-open");
  }
  this.hourOpen = function() {
    this.minuteClose();
    this.tagOpen("hour");
  }
  this.hourClose = function() {
    this.tagClose("hour");
  }
  this.minuteOpen = function() {
    if (this.hourIsOpen()) {
      var self = this;
      var t = getStyle(this.tags.hour, "transition-duration");
      if (!t)
        t = getStyle(this.tags.hour, "-webkit-transition-duration")
      t = parseFloat(t)*1000;
      this.hourClose();
      setTimeout(function() {
        self.tagOpen("minute");
      }, t);
    }
    else {
      this.tagOpen("minute");
    }
  }
  this.minuteClose = function() {
    this.tagClose("minute");
  }
  this.tagOpen = function(type) {
    var self = this;
    this.tags[type].style.display = "block";
    setTimeout(function() {
      self.tags.wrap.addClass("timepicker-"+type+"-open");
    }, 10);
  }
  this.tagClose = function(type) {
    var self = this;
    var t = getStyle(this.tags[type], "transition-duration");
    if (!t)
      t = getStyle(this.tags[type], "-webkit-transition-duration");
    t = parseFloat(t)*1000;
    this.tags.wrap.removeClass("timepicker-"+type+"-open");
    setTimeout(function() {
      self.tags[type].style.display = "none";
    }, t);
  }

  this.hourClick = function(h) {
    this.setHour(h);
    this.minuteOpen();
  }
  this.minuteClick = function(m) {
    this.setMinute(m);
    this.close();
  }

  this.windowClick = function(e) {
    if (!this.isOpen())
      return;
    var el = e.target;
    for (var i=0; el && i<7; el = el.parentNode, i++) {
      if (el == this.tags.picker || el == this.tags.input)
        return;
    }
    this.close();
  }

  this.setHour = function(h) {
    if (this.tags.hours[this.value.h])
      this.tags.hours[this.value.h].removeClass("active");
    this.value.h = h;
    this.tags.hours[h].addClass("active");
    this.renderValue();
  }
  this.setMinute = function(m) {
    if (this.tags.minutes[this.value.m])
      this.tags.minutes[this.value.m].removeClass("active");
    this.value.m = m;
    this.tags.minutes[m].addClass("active");
    this.renderValue();
  }

  this.init();

}

window.addEventListener("load", timepickerInit, false);