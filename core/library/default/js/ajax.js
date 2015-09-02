function xajax() {
	
	this.init = function() {
		this.xmlhttp = new XMLHttpRequest();
	}
	
	this.send = function(url, callback, opt) {
		if (!opt)
			opt = {};
		if (!opt.method)
			opt.method = "GET";
		var thi = this;
		this.xmlhttp.onreadystatechange = function(){
			if (thi.xmlhttp.readyState == 4) {
				var response = (thi.xmlhttp.response ? thi.xmlhttp.response : thi.xmlhttp.responseText);
				if (response && response.substr(0, 1) == "{") {
					var r = eval("(function(){ return "+response+";}())");
					if (!opt.errorHandle && r.status == "error") {
						alert(r.error);
						return;
					}
				}
				else 
					var r = response;
				callback(r);
			}
		};
		this.xmlhttp.open(opt.method, url, true);
		if (opt.headers) {
			for (var h in opt.headers)
				this.xmlhttp.setRequestHeader(h, opt.headers[h]);
		}
		if (opt.method == "POST") {
			if (opt.post)
				this.xmlhttp.send(opt.post);
			else
				this.xmlhttp.send(this.stringify(opt.obj));
		}
		else
			this.xmlhttp.send();
	}
	
	this.stringify = function(obj) {
		if (typeof(JSON) == "object")
			return JSON.stringify(obj);
		var t = typeof (obj);
		if (t != "object" || obj === null) {
			if (t == "string") obj = '"'+obj+'"';
			return String(obj);
		}
		else {
			var n, v, json = [], arr = (obj && obj.constructor == Array);
			for (n in obj) {
				v = obj[n]; t = typeof(v);
				if (t == "string") v = '"'+v+'"';
				else if (t == "object" && v !== null) v = this.stringify(v);
				json.push((arr ? "" : '"' + n + '":') + String(v));
			}
			return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
		}
	}
	
	this.init();
}