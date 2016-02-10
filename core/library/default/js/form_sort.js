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
    this.lastXY = {
      x: 0,
      y: 0
    };
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
    this.lastXY = getXY(e);
  }
  
  this.onMouseUp = function(e) {
    if (this.dragging)
      this.dragging = false;
  }
  
  this.onMouseMove = function(e) {
    if (!this.dragging)
      return;
    var xy = getXY(e);
    xy.y+= scrollTop();
    if (xy.y < this.lastXY.y) {
      var prev = this.getPrev();
      if (!prev)
        return;
      var y = getTopPos(prev);
      if (xy.y < y + prev.offsetHeight/2)
        this.movePrev(prev);
    }
    else if (xy.y > this.lastXY.y) {
      var next = this.getNext();
      if (!next)
        return;
      var y = getTopPos(next);
      if (xy.y > y + next.offsetHeight/2)
        this.moveNext(next);
    }
    this.lastXY = xy;
  }
  
  this.moveNext = function(next) {
    if (!next) {
      next = this.getNext();
      if (!next)
        return;
    }
    next.parentNode.insertBefore(next, this.tags.item);
  }
  
  this.movePrev = function(prev) {
    if (!prev) {
      prev = this.getPrev();
      if (!prev)
        return;
    }
    prev.parentNode.insertBefore(this.tags.item, prev);
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
  
  this.init();
  
}

window.addEventListener("load", formSortInit, false);