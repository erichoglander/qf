var _autocompletes = [];
function autocompleteInit() {
  var els = document.getElementsByClassName("form-autocomplete");
  for (var i=0; i<els.length; i++) {
    if (!els[i].hasClass("autocomplete-init")) {
      els[i].addClass("autocomplete-init");
      _autocompletes.push(new autocomplete(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        autocompleteObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      autocompleteObserve(document.body);
    }, 1000);
  }
}
function autocompleteObserve(el) {
  var els = el.getElementsByClassName("form-autocomplete");
  for (var i=0; i<els.length; i++) {
    if (!els[i].hasClass("autocomplete-init")) {
      els[i].addClass("autocomplete-init");
      _autocompletes.push(new autocomplete(els[i]));
    }
  }
}

function autocomplete(el) {
  
  this.tags = {
    title: el
  };
  
  this.init = function() {
    
    var self = this;
    this.timeoutTime = 400;
    this.timeout = null;
    this.itemActive = -1;
    this.items = [];
    this.uri = this.tags.title.getAttribute("uri");
    this.lastLength = this.tags.title.length;
    
    this.tags.wrap = formGetItem(this.tags.title);
    this.tags.wrap.addClass("autocomplete-init");
    this.tags.value = this.tags.title.form.elements[this.tags.title.name.substr(0, this.tags.title.name.length-7)+"[value]"];
    this.tags.itemsWrap = this.tags.wrap.getElementsByClassName("autocomplete-items")[0];
    this.tags.preview = this.tags.wrap.getElementsByClassName("autocomplete-preview-title")[0];
    this.tags.remove = this.tags.wrap.getElementsByClassName("autocomplete-remove")[0];
    this.tags.items = [];
    
    if (this.tags.title.getAttribute("onchange")) {
      this.tags.value.setAttribute("onchange", this.tags.title.getAttribute("onchange"));
      this.tags.title.removeAttribute("onchange");
    }
    
    this.ajax = new xajax();
    this.tags.title.addEventListener("keydown", function(e){ self.onKeydown(e); }, false);
    this.tags.title.addEventListener("keyup", function(e){ self.onKeyup(e); }, false);
    this.tags.title.addEventListener("blur", function(){ self.onBlur(); }, false);
    this.tags.title.addEventListener("focus", function(){ self.onFocus(); }, false);
    this.tags.remove.addEventListener("click", function(){ self.remove(); }, false);
    this.tags.value.addEventListener("reset", function(){ self.reset(); }, false);
    
  }
  
  /*
    e.keyCode
      37 = left
      38 = up
      39 = right
      40 = down
      13 = enter
  */
  this.onKeydown = function(e) {
    var code = e.keyCode;
    if (code == 13) {
      e.preventDefault();
      return false;
    }
  }
  this.onKeyup = function(e) {
    var self = this;
    var code = e.keyCode;
    this.removeError();
    if (code == 13) {
      e.preventDefault();
      this.itemEnter();
      return false;
    }
    else if (code == 40 || code == 38) {
      if (code == 40)
        this.itemDown();
      else
        this.itemUp();
    }
    else if (this.lastLength != this.tags.title.value.length) {
      this.lastLength = this.tags.title.value.length;
      this.deleteItems();
      if (this.timeout)
        clearTimeout(this.timeout);
      if (this.ajax.xmlhttp.readyState != 0 && this.ajax.xmlhttp.readyState != 4)
        this.ajax.xmlhttp.abort();
      this.timeout = setTimeout(function() {
        self.request();
      }, this.timeoutTime);
    }
  }
  this.onBlur = function() {
    var self = this;
    // Workaround for idiotic chrome browser
    setTimeout(function() {
      self.hideItems();
    }, 10);
  }
  this.onFocus = function() {
    this.showItems();
  }
  
  this.removeError = function() {
    if (this.tags.wrap.hasClass("form-item-error")) {
      this.tags.wrap.removeClass("form-item-error");
      var icon = this.tags.wrap.getElementByClassName("form-icon-feedback");
      var error = this.tags.wrap.getElementByClassName("form-input-error");
      if (icon)
        icon.parentNode.removeChild(icon);
      if (error)
        error.parentNode.removeChild(error);
    }
  }
  
  this.request = function() {
    var self = this;
    var q = this.tags.title.value.trim();
    if (!q.length)
      return;
    var url = BASE_URL+this.uri+"/"+q;
    this.ajax.send(
      url,
      function(r){ 
        self.handleRequest(r); 
      }
    );
  }
  this.handleRequest = function(r) {
    if (r && r.items)
      this.renderItems(r.items);
  }
  this.showItems = function() {
    if (this.tags.items.length)
      this.tags.itemsWrap.addClass("active");
  }
  this.hideItems = function() {
    this.tags.itemsWrap.removeClass("active");
  }
  this.deleteItems = function() {
    this.tags.itemsWrap.removeClass("active");
    this.tags.itemsWrap.innerHTML = "";
    this.itemSetActive(-1);
    this.tags.items = [];
  }
  this.renderItems = function(items) {
    var self = this;
    this.items = items;
    if (items.length)
      this.tags.itemsWrap.addClass("active");
    for (var i=0; i<items.length; i++) {
      this.tags.items[i] = document.createElement("div");
      this.tags.items[i].className = "autocomplete-item";
      this.tags.items[i].textContent = items[i].title;
      this.tags.itemsWrap.appendChild(this.tags.items[i]);
      (function(n) {
        self.tags.items[n].addEventListener("click", function(){ self.itemClick(n); }, false);
        self.tags.items[n].addEventListener("mouseover", function(){ self.itemOver(n); }, false);
      }(i));
    }
  }
  
  this.preview = function() {
    this.tags.preview.textContent = this.tags.title.value;
    this.tags.preview.setAttribute("title", this.tags.title.value);
  }
  
  this.remove = function() {
    this.clear();
    this.tags.title.focus();
  }
  
  this.clear = function() {
    this.tags.value.value = null;
    this.tags.title.value = null;
    this.tags.preview.textContent = "";
    this.tags.wrap.removeClass("has-value");
    this.tags.value.trigger("change");
  }
  
  this.reset = function() {
    this.clear();
  }
  
  this.itemUp = function() {
    if (this.itemActive == -1)
      var n = this.items.length-1;
    else
      var n = (this.itemActive-1+this.items.length)%this.items.length;
    this.itemSetActive(n);
  }
  this.itemDown = function() {
    var n = (this.itemActive+1)%this.items.length;
    this.itemSetActive(n);
  }
  this.itemEnter = function() {
    if (this.items[this.itemActive])
      this.itemChoose(this.itemActive);
  }
  this.itemChoose = function(n) {
    this.tags.title.value = this.items[n].title;
    this.tags.value.value = this.items[n].value;
    this.tags.wrap.addClass("has-value");
    this.preview();
    this.deleteItems();
    this.tags.value.trigger("change");
  }
  this.itemSetActive = function(n) {
    if (this.itemActive != -1 && this.items[this.itemActive])
      this.tags.items[this.itemActive].removeClass("active");
    if (n != -1 && this.items[n])
      this.tags.items[n].addClass("active");
    this.itemActive = n;
  }
  this.itemOver = function(n) {
    this.itemSetActive(n);
  }
  this.itemClick = function(n) {
    this.itemChoose(n);
  }
  
  this.init();
  
}

window.addEventListener("load", autocompleteInit, false);