var _autoselects = [];
function autoselectInit() {
  var els = document.getElementsByClassName("form-autoselect");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("autoselect-init")) {
      els[i].addClass("autoselect-init");
      _autoselects.push(new autoselect(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        autoselectObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      autoselectObserve(document.body);
    }, 1000);
  }
}
function autoselectObserve(el) {
  var els = el.getElementsByClassName("form-autoselect");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("autoselect-init")) {
      els[i].addClass("autoselect-init");
      _autoselects.push(new autoselect(els[i]));
    }
  }
}

function autoselect(el) {
  
  this.tags = {
    input: el
  };
  
  this.init = function() {
    var self = this;
    this.optionActive = -1;
    this.is_mouse_down = false;
    this.tags.item = formGetItem(this.tags.input);
    this.tags.select = this.tags.item.getElementsByTagName("select")[0];
    this.tags.options = this.tags.item.getElementByClassName("autoselect-options");
    this.tags.input.addEventListener("blur", function(){ self.onBlur(); }, false);
    this.tags.input.addEventListener("focus", function(){ self.onFocus(); }, false);
    this.tags.input.addEventListener("keydown", function(e){ self.onKeyDown(e); }, false);
    this.tags.input.addEventListener("keyup", function(e){ self.onKeyUp(e); }, false);
    this.tags.options.addEventListener("mousedown", function(){ self.onMouseDown(); }, false);
    this.tags.options.addEventListener("mouseup", function(){ self.onMouseUp(); }, false);
    this.tags.input.addClass("autoselect-init");
    self.selectChange();
    if (typeof(MutationObserver) == "function") {
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          self.selectChange(mutation.target);
        });
      });
      var config = { childList: true, subtree: true };
      observer.observe(this.tags.select, config);
    }
  }

  this.selectChange = function() {
    if (this.isDisabled()) {
      this.tags.input.setAttribute("disabled", true);
      this.tags.item.addClass("disabled");
    }
    else {
      this.tags.input.removeAttribute("disabled");
      this.tags.item.removeClass("disabled");
    }
    this.tags.input.value = (this.tags.select.value.length ? this.tags.select.options[this.tags.select.selectedIndex].text : "");
    this.renderOptions();
  }
  
  this.renderOptions = function() {
    var value = this.tags.input.value.toLowerCase().trim();
    var arr = value.split(" ");
    this.tags.options.innerHTML = "";
    this.tags.option = [];
    this.optionActive = -1;
    for (var i=0; i<this.tags.select.options.length; i++) {
      var match = false;
      var lower = this.tags.select.options[i].text.toLowerCase();
      if (!value.length || lower.indexOf(value) != -1) {
        match = true;
      }
      else {
        match = true;
        for (var j=0; j<arr.length; j++) {
          if (lower.indexOf(arr[j]) == -1) {
            match = false;
            break;
          }
        }
      }
      if (!this.tags.options[i]) {
        this.tags.option[i] = document.createElement("div");
        this.tags.option[i].className = "autoselect-option";
        if (!this.tags.select.options[i].value.length)
          this.tags.option[i].className+= " empty";
        this.tags.option[i].innerHTML = this.tags.select.options[i].text;
        this.tags.options.appendChild(this.tags.option[i]);
        (function(self, n) {
          self.tags.option[n].addEventListener("click", function(){ self.optionClick(n); }, false);
        }(this, i));
      }
      if (match) {
        this.tags.option[i].style.display = "block";
        this.tags.option[i].addClass("match");
      }
      else {
        this.tags.option[i].style.display = "none";
        this.tags.option[i].removeClass("match");
      }
    }
  }
  
  this.onFocus = function() {
    this.renderOptions();
    this.tags.input.select();
    var self = this;
    setTimeout(function(){
      self.tags.input.select();
    }, 100);
  }
  
  this.onBlur = function() {
    var self = this;
    if (!this.is_mouse_down) {
      if (!self.tags.input.value.length && self.tags.select.selectedIndex != 0) {
        self.tags.select.selectedIndex = 0;
        self.tags.select.trigger("change");
      }
    }
  }
  
  this.isDisabled = function() {
    return this.tags.select.getAttribute("disabled") !== null;
  }
  
  /*
    e.keyCode
      37 = left
      38 = up
      39 = right
      40 = down
      27 = esc
      13 = enter
  */
  this.onKeyDown = function(e) {
    var code = e.keyCode;
    if (code == 13) {
      e.preventDefault();
      return false;
    }
    else if (code == 27) {
      this.tags.input.blur();
    }
  }
  this.onKeyUp = function(e) {
    var code = e.keyCode;
    if (code == 13) {
      e.preventDefault();
      this.optionEnter();
      return false;
    }
    else if (code == 40 || code == 38) {
      if (code == 40)
        this.optionDown();
      else
        this.optionUp();
    }
    else {
      this.renderOptions();
    }
  }
  
  this.onMouseDown = function() {
    this.tags.input.addClass("is_mouse_down");
    this.is_mouse_down = true;
  }
  this.onMouseUp = function() {
    this.tags.input.removeClass("is_mouse_down");
    this.is_mouse_down = false;
  }
  
  this.optionUp = function() {
    for (var i=this.optionActive-1; i >= 0; i--) {
      if (this.tags.option[i].hasClass("match")) {
        if (this.optionActive != -1)
          this.tags.option[this.optionActive].removeClass("active");
        this.tags.option[i].addClass("active");
        this.optionActive = i;
        this.optionScroll();
        break;
      }
    }
  }
  this.optionDown = function() {
    for (var i=this.optionActive+1; i < this.tags.select.options.length; i++) {
      if (this.tags.option[i].hasClass("match")) {
        if (this.optionActive != -1)
          this.tags.option[this.optionActive].removeClass("active");
        this.tags.option[i].addClass("active");
        this.optionActive = i;
        this.optionScroll();
        break;
      }
    }
  }
  this.optionEnter = function() {
    if (this.optionActive != -1)
      this.optionClick(this.optionActive);
  }
  
  this.optionScroll = function() {
    if (this.optionActive == -1)
      return;
    var option = this.tags.option[this.optionActive];
    if (option.offsetTop < this.tags.options.scrollTop)
      this.tags.options.scrollTop = option.offsetTop;
    else if (option.offsetTop + option.offsetHeight > this.tags.options.scrollTop + this.tags.options.offsetHeight)
      this.tags.options.scrollTop = option.offsetTop + option.offsetHeight - this.tags.options.offsetHeight;
  }
  
  this.optionClick = function(n) {
    this.tags.select.selectedIndex = n;
    this.tags.select.trigger("change");
    this.tags.input.value = (this.tags.select.value.length ? this.tags.select.options[n].text : "");
    this.tags.input.blur();
  }
  
  this.init();
  
}

window.addEventListener("load", autoselectInit, false);