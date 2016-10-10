/** 
 * Smooth anchor object
 * @var object
 */
var smoothAnchor = {
  
  /**
   * Enable/disable smooth anchor
   * @var bool
   */
  active: false,
  
  /**
   * Scroll duration
   * @var int
   */
  duration: 0,
  
  /**
   * If inited
   * @var bool
   */
  is_init: false,
  
  /**
   * Init
   */
  init: function() {
    if (!this.active)
      return;
    if (typeof(MutationObserver) == "function") {
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          smoothAnchor.check(mutation.target);
        });    
      });
      var config = { childList: true, subtree: true };
      observer.observe(document.body, config);
    }
    else {
      setInterval(function(){
        smoothAnchor.check(document.body);
      }, 1000);
    }
    this.check();
    this.checkCurrent();
    this.is_init = true;
  },
  
  /**
   * Check for current hash
   */
  checkCurrent: function() {
    if (window.location.hash.length > 1) {
      window.scrollTo(0,0);
      this.scroll(window.location.href);
    }
  },
  
  /**
   * Prevent initial hash scroll
   */
  prevent: function() {
    if (this.active && !this.is_init && window.location.hash.length > 1)
      window.scrollTo(0,0);
  },
  
  /**
   * Check for new anchors in element and add listeners
   * @param \Element el
   */
  check: function(el) {
    var a = document.getElementsByTagName("a");
    for (var i=0; i<a.length; i++) {
      if (a[i].href.indexOf("#") != -1 && !a[i].hasClass("smooth-anchor")) {
        a[i].addClass("smooth-anchor");
        (function(el) {
          el.addEventListener("click", function(e){
            smoothAnchor.scroll(el.href, e); 
          }, false);
        }(a[i]));
      }
    }
  },
  
  /**
   * Scroll to anchor
   * @param string href
   * @param \Event
   */
  scroll: function(href, e) {
    var arr = href.split("#");
    var loc = window.location.href.split("#");
    if (arr[0] != loc[0])
      return;
    var id = arr[1];
    var y = 0;
    if (id.length) {
      var el = document.getElementById(id);
      if (!el) {
        var as = document.getElementsByTagName("a");
        for (var i=0; i<as.length; i++) {
          if (as[i].name == id) {
            el = as[i];
            break;
          }
        }
      }
      if (el) {
        y = getTopPos(el);
        var mt = document.body.getStyle("margin-top");
        y-= parseInt(mt);
      }
    }
    if (e && y == scrollTop()) {
      e.preventDefault();
      if (history.pushState)
        history.pushState(null, null, "#"+id);
    }
    smoothScroll(y, this.duration);
  },
  
};

window.addEventListener("load", function(){ smoothAnchor.prevent(); }, false);
document.addEventListener("DOMContentLoaded", function(){ smoothAnchor.init(); }, false);