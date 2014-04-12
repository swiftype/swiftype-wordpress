(function(){
  if (Swiftype && Swiftype.disableEmbedTracking === true) {
    return;
  }

  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.async = true;
  script.src = '//s.swiftypecdn.com/cc.js';
  var entry = document.getElementsByTagName('script')[0];
  entry.parentNode.insertBefore(script, entry);
})();
