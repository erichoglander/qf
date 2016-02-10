var FontAwesome = {

  icon: function(cname) {
    var span = document.createElement("span");
    span.className = "fa fa-"+cname;
    return span;
  },

};