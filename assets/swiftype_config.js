(function($) {
  $(function() {
    Swiftype.key = swiftypeParams.engineKey;
    Swiftype.inputElements = $('input[name=s]');
    var defaultRenderFunction = function(document_type, item) {
        var title = item['highlight']['title'] || Swiftype.htmlEscape(item['title']);
        return '<p class="title">' + title + '</p>';
    };

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
        Swiftype.pingAutoSelection(Swiftype.key, dataItem['id'], prefix, function() { window.location = dataItem['url']; });
      },
      documentTypes: ['posts'],
      engineKey: Swiftype.key,
      filters: SwiftypeConfigManager.getFilters(),
      functionalBoosts: SwiftypeConfigManager.getFunctionalBoosts(),
      searchFields: SwiftypeConfigManager.getSearchFields(),
      sortField: SwiftypeConfigManager.getSortField(),
      sortDirection: SwiftypeConfigManager.getSortDirection(),
      disableAutocomplete: SwiftypeConfigManager.getDisableAutocomplete(),
      resultLimit: SwiftypeConfigManager.getResultLimit(),
      renderFunction: (window.swiftypeConfig && window.swiftypeConfig["renderFunction"]) || defaultRenderFunction
    };

    $.each(Swiftype.inputElements, function(idx, el) {
      var $el = $(el);
      $el.swiftype(swiftypeOptions);
    });

    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.async = true;
    script.src = '//s.swiftypecdn.com/cc.js';
    var entry = document.getElementsByTagName('script')[0];
    entry.parentNode.insertBefore(script, entry);
  });
})(jQuery);

