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
    if (window.location.hash.length > 1)
      this.scroll(window.location.href);
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
            e.preventDefault();
            smoothAnchor.scroll(el.href); 
          }, false);
        }(a[i]));
      }
    }
  },
  
  /**
   * Scroll to anchor
   * @param string href
   */
  scroll: function(href) {
    var arr = href.split("#");
    var loc = window.location.href.split("#");
    if (arr[0] != loc[0])
      return;
    var id = arr[1];
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
    if (el)
      scrollToEl(el, this.duration);
  },
  
};

window.addEventListener("load", function(){ smoothAnchor.init(); }, false);