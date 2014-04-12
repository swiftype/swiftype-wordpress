(function($) {
  window.Swiftype = window.Swiftype || {};

  Swiftype.pingSearchResultClick = function (engineKey, docId, query, callback) {
    var params = {
      t: new Date().getTime(),
      engine_key: engineKey,
      document_type_id: 'posts',
      doc_id: docId,
      q: query
    };
    var url = Swiftype.root_url + '/api/v1/public/analytics/pc?' + $.param(params);
    Swiftype.pingUrl(url, callback);
  };

  $(function() {
    function getSearchQuery() {
      return $.queryParams()['s'];
    }

    function getDocumentId(classes) {
      var docId = null;
      var docIdClasses = $.grep(classes.split(" "), function(item) {
        return item.indexOf("swiftype-result-") !== -1;
      });

      if (docIdClasses.length == 1) {
        var docIdClass = docIdClasses[0];
        docId = docIdClass.split("swiftype-result-")[1];
      }

      return docId;
    }

    $(".swiftype-result a").click(function(event) {
      var $element = $(this);
      var docId = getDocumentId($element.parents(".swiftype-result").attr("class"));

      if (docId !== null) {
        event.preventDefault();

        var theWindow = window;
        if (event.metaKey || event.ctrlKey) {
          theWindow = window.open('about:blank', '_blank');
        }

        Swiftype.pingSearchResultClick(Swiftype.key, docId, getSearchQuery(), function() {
          theWindow.location = $element.attr("href");
        });
      }
    });
  });
})(jQuery);
