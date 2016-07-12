var _form_sortables = [];
function formSortInit() {
  var els = document.getElementsByClassName("form-sortable");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("form-sortable-init")) {
      els[i].addClass("form-sortable-init");
      _form_sortables.push(new formSort(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        formSortObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      formSortObserve(document.body);
    }, 1000);
  }
}
function formSortObserve(el) {
  var els = el.getElementsByClassName("form-sortable");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("form-sortable-init")) {
      els[i].addClass("form-sortable-init");
      _form_sortables.push(new formSort(els[i]));
    }
  }
}

function formSort(el) {
  
  this.tags = {
    wrap: el
  };
  
  this.init = function() {
    var self = this;
    this.dragging = false;
    this.startXY = {
      x: 0,
      y: 0
    };
    this.top = 0;
    this.tags.item = formGetItem(this.tags.wrap);
    this.tags.up = this.tags.wrap.getElementByClassName("form-sortable-up");
    this.tags.down = this.tags.wrap.getElementByClassName("form-sortable-down");
    this.tags.drag = this.tags.wrap.getElementByClassName("form-sortable-drag");
    if (this.tags.up)
      this.tags.up.addEventListener("click", function(){ self.movePrev(); }, false);
    if (this.tags.down)
      this.tags.down.addEventListener("click", function(){ self.moveNext(); }, false);
    if (this.tags.drag) {
      this.tags.drag.addEventListener("mousedown", function(e){ self.onMouseDown(e); }, false);
      window.addEventListener("mouseup", function(e){ self.onMouseUp(e); }, false);
      window.addEventListener("mousemove", function(e){ self.onMouseMove(e); }, false);
    }
  }
  
  this.onMouseDown = function(e) {
    this.dragging = true;
    this.tags.item.addClass("dragging");
    this.startXY = this.getXY(e);
  }
  
  this.onMouseUp = function(e) {
    if (this.dragging) {
      this.tags.item.removeClass("dragging");
      this.dragging = false;
      this.tags.item.style.transform = null;
    }
  }
  
  this.onMouseMove = function(e) {
    if (!this.dragging)
      return;
    var xy = this.getXY(e);
    var dy = xy.y - this.startXY.y;
    if (dy < 0) {
      var prev = this.getPrev();
      if (prev) {
        var y = prev.getTopPos();
        var dt = prev.offsetTop - this.tags.item.offsetTop;
        if (dy < dt/2) {
          if (this.movePrev(prev))
            this.startXY.y+= dt;
        }
      }
    }
    else if (dy > 0) {
      var next = this.getNext();
      if (next) {
        var y = next.getTopPos();
        var dt = next.offsetTop - this.tags.item.offsetTop;
        if (dy > dt/2) {
          if (this.moveNext(next))
            this.startXY.y+= dt;
        }
      }
    }
    var ty = xy.y - this.startXY.y;
    this.tags.item.style.transform = "translate3d(0, "+ty+"px, 0)";
    this.lastXY = xy;
  }
  
  this.moveNext = function(next) {
    if (!next) {
      next = this.getNext();
      if (!next)
        return false;
    }
    next.parentNode.insertBefore(next, this.tags.item);
    return true;
  }
  
  this.movePrev = function(prev) {
    if (!prev) {
      prev = this.getPrev();
      if (!prev)
        return false;
    }
    prev.parentNode.insertBefore(this.tags.item, prev);
    return true;
  }
  
  this.getPrev = function() {
    var el = this.tags.item.previousElementSibling;
    if (!el || !el.hasClass("form-item"))
      return null;
    return el;
  }
  
  this.getNext = function() {
    var el = this.tags.item.nextElementSibling;
    if (!el || !el.hasClass("form-item"))
      return null;
    return el;
  }
  
  this.getXY = function(e) {
    var xy = getXY(e);
    xy.y+= scrollTop();
    return xy;
  }
  
  this.init();
  
}

window.addEventListener("load", formSortInit, false);