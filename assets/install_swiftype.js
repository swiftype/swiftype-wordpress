(function($) {
  $(function() {

    Swiftype.engineKey = swiftypeParams.engineKey;

    var onComplete = function(dataItem,prefix) {
      Swiftype.pingAutoSelection(Swiftype.engineKey, dataItem['id'], prefix);
      window.location = dataItem['url'];
    };

    Swiftype.inputElements = jQuery('input[name=s]');
    $.each(Swiftype.inputElements, function(idx, el) {
      $(el).swiftype({
        onComplete: onComplete,
        dataUrl: "http://localhost:3000/api/v1/public/engines/suggest.json",
        documentTypes: ['posts'],
        engineKey: Swiftype.engineKey,
        nameField: 'title',
        attachTo: el,
        disableAutocomplete: false
      });
    });

  });
})(jQuery);