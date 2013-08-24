(function($) {
  $(function() {
    Swiftype.engineKey = swiftypeParams.engineKey;
    Swiftype.inputElements = $('input[name=s]');

    function readSwiftypeConfigFor(option) {
      if ((typeof window.swiftypeConfig === 'undefined') || (typeof window.swiftypeConfig[option] === 'undefined') || window.swiftypeConfig[option] === null) {
        return undefined;
      }

      return function() { 
        if (typeof window.swiftypeConfig[option] === 'function') {
          return window.swiftypeConfig[option].call();
        } else {
          return window.swiftypeConfig[option];
        }
      };
    }

    var SwiftypeConfigManager = {
      getFilters: function() {
        return readSwiftypeConfigFor('filters');
      },
      getSearchFields: function() {
        return readSwiftypeConfigFor('searchFields');
      },
      getSortField: function() {
        return readSwiftypeConfigFor('sortField');
      },
      getSortDirection: function() {
        return readSwiftypeConfigFor('sortDirection');
      },
      getFunctionalBoosts: function() {
        return readSwiftypeConfigFor('functionalBoosts');
      },
      getDisableAutocomplete: function() {
        return readSwiftypeConfigFor('disableAutocomplete');
      },
      getResultLimit: function() {
        return readSwiftypeConfigFor('resultLimit');
      }
    };

    var swiftypeOptions = {
      onComplete: function(dataItem, prefix) {
        Swiftype.pingAutoSelection(Swiftype.engineKey, dataItem['id'], prefix, function() { window.location = dataItem['url']; });
      },
      documentTypes: ['posts'],
      engineKey: Swiftype.engineKey,
      filters: SwiftypeConfigManager.getFilters(),
      functionalBoosts: SwiftypeConfigManager.getFunctionalBoosts(),
      searchFields: SwiftypeConfigManager.getSearchFields(),
      sortField: SwiftypeConfigManager.getSortField(),
      sortDirection: SwiftypeConfigManager.getSortDirection(),
      disableAutocomplete: SwiftypeConfigManager.getDisableAutocomplete(),
      resultLimit: SwiftypeConfigManager.getResultLimit()
    };

    $.each(Swiftype.inputElements, function(idx, el) {
      var $el = $(el);
      $el.swiftype(swiftypeOptions);
    });
  });
})(jQuery);

