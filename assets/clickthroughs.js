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
    /* Public domain code by Jan Wolter from http://unixpapa.com/js/querystring.html */
    function QueryString(qs)
    {
        this.dict= {};

        // If no query string  was passed in use the one from the current page
        if (!qs) qs= location.search;

        // Delete leading question mark, if there is one
        if (qs.charAt(0) == '?') qs= qs.substring(1);

        // Parse it
        var re= /([^=&]+)(=([^&]*))?/g;
        while (match= re.exec(qs))
        {
            var key= decodeURIComponent(match[1].replace(/\+/g,' '));
            var value= match[3] ? QueryString.decode(match[3]) : '';
            if (this.dict[key])
                this.dict[key].push(value);
            else
                this.dict[key]= [value];
        }
    }

    QueryString.decode= function(s)
    {
        s= s.replace(/\+/g,' ');
        s= s.replace(/%([EF][0-9A-F])%([89AB][0-9A-F])%([89AB][0-9A-F])/g,
            function(code,hex1,hex2,hex3)
            {
                var n1= parseInt(hex1,16)-0xE0;
                var n2= parseInt(hex2,16)-0x80;
                if (n1 == 0 && n2 < 32) return code;
                var n3= parseInt(hex3,16)-0x80;
                var n= (n1<<12) + (n2<<6) + n3;
                if (n > 0xFFFF) return code;
                return String.fromCharCode(n);
            });
        s= s.replace(/%([CD][0-9A-F])%([89AB][0-9A-F])/g,
            function(code,hex1,hex2)
            {
                var n1= parseInt(hex1,16)-0xC0;
                if (n1 < 2) return code;
                var n2= parseInt(hex2,16)-0x80;
                return String.fromCharCode((n1<<6)+n2);
            });
        s= s.replace(/%([0-7][0-9A-F])/g,
            function(code,hex)
            {
                return String.fromCharCode(parseInt(hex,16));
            });
        return s;
    };

    QueryString.prototype.value= function (key)
    {
        var a= this.dict[key];
        return a ? a[a.length-1] : undefined;
    };

    QueryString.prototype.values= function (key)
    {
        var a= this.dict[key];
        return a ? a : [];
    };

    QueryString.prototype.keys= function ()
    {
        var a= [];
        for (var key in this.dict)
            a.push(key);
        return a;
    };

    function getSearchQuery() {
      var queryString = new QueryString();

      return queryString.value('s');
    }

    function getDocumentId(classes) {
      var docIdClasses = $.grep(classes.split(" "), function(item) {
        return item.indexOf("swiftype-result-") !== -1;
      });

      var docIdClass = docIdClasses[0];
      var docId = docIdClass.split("swiftype-result-")[1];

      return docId;
    }

    $(".swiftype-result a").click(function(event) {
      var $element = $(this);
      var docId = getDocumentId($element.parents(".swiftype-result").attr("class"));

      console.log("got doc ID: ");
      console.log(docId);

      if (docId !== undefined) {
        event.preventDefault();

        Swiftype.pingSearchResultClick(Swiftype.engineKey, docId, getSearchQuery(), function() {
          console.log("sending user to " + $element.attr("href"));
          window.location = $element.attr("href");
        });
      }
    });
  });
})(jQuery);
