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
				a.slice(i, 1);
				this.className = a.join(" ");
			}
		}
	}
}

if (typeof(window.addEventListener) != "function") {
	Element.prototype.addEventListener = function(ev, func, nothing) {
		window.attachEvent("on"+ev, func);
	}
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
function ease(t) {
	return (t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1);
}