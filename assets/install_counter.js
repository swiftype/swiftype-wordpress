(function($) {
  window.Swiftype = window.Swiftype || {};

  $(function() {
    var i = new Image();
    var params = '?url=' + encodeURIComponent(window.location.href) + "&engine_key=" + Swiftype.engineKey;
    if (document.referrer != "") { params += "&r=" + encodeURIComponent(document.referrer); }
    i.src = '//swiftype.com/api/v1/public/cc' + params;
  });
})(jQuery);
