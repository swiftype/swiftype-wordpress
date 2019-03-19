<?php
/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */
?>

<div class="card" id="facets-config">
    <div class="tooltip-title">
      <div class="content">
          <h4><?php echo __("What are facets ?"); ?></h4>
          <p><?php echo __('Facets allow users to narrow down search results by applying filters based on the post fields. Read the <b><a href="https://swiftype.com/documentation/site-search/searching/faceting" target="_new">Site Search Facets Documentation</b></a>.' ); ?></p>

          <h4><?php echo __("How to display facets ?"); ?></h4>
          <p><?php echo __("If facets are configured but not displayed in your search results make sure you have called the swiftype_render_facets theme function in the search.php file of your theme."); ?></p>
      </div>
    </div>
    <h3><?php echo __('Configure facets'); ?></h3>

    <div class="widefat">
        <table width="100%">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __('Facet title'); ?></th>
                    <th><?php echo __('Facet field'); ?></th>
                    <th><?php echo __('Facet sort order'); ?></th>
                    <th><?php echo __('Facet max. size'); ?></th>
                    <th> </th>
                </tr>
            </thead>
            <tbody class="ui-sortable list">
                <tr>
                    <td colspan="5" class="no-facets-msg"><?php echo __('You have no facet configured yet. Start adding your first facet using the button bellow.');?></td>
                </tr>
            </tbody>
        </table>
        <div class="controls">
            <div class="controls-right">
                <a href="#" class="button-primary add-facet"><?php echo __('Add Facet');?></a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
  jQuery(document).ready(function() {

      var facets = <?php echo json_encode($this->getConfig()->getFacetConfig());?> ;

      function getSortOrderOptionsHtml() {
        sortOptions = [
            '<option value="count"><?php echo __('Count values'); ?></option>',
            '<option value="text"><?php echo __('Alphabetic'); ?></option>'
        ];
        return sortOptions.join("");
      }

      function getFacetFieldOptionsHtml() {
          return (<?php echo json_encode($this->getFacetFieldsFromMapping());?>.map(function(value){
              return `<option value="${value}">${value}</option>`;
          })).join("");
      }

      function renderFacet(facet) {
          var rootNode = jQuery("#facets-config .ui-sortable").append(`
              <tr class="facet-config view-mode">
                <td class="handle"></td>
                <td class="facet-title">
                  <div class="view-mode">${facet.title}</div>
                  <div class="edit-mode"><input required type="text" value="${facet.title}" name="title" /></div>
                </td>
                <td class="facet-field">
                  <div class="view-mode">${facet.field}</div>
                  <div class="edit-mode"><select name="field"></select></div>
                </td>
                <td class="facet-sort-order">
                  <div class="view-mode">${facet.sortOrder}</div>
                  <div class="edit-mode"><select name="sortOrder"></select></div>
                </td>
                <td class="facet-size">
                  <div class="view-mode">${facet.size}</div>
                  <div class="edit-mode"><input required  type="number" name="size" min="0" max="1000" step="1" value="${facet.size}" /></div>
                </td>
                <td class="actions">
                  <div class="view-mode">
                    <a href="#" class="inline-edit-link" title="<?php echo __('Edit'); ?>"> </a>
                    <a href="#" class="inline-delete-link" title="<?php echo __('Delete'); ?>"></a>
                  </div>
                  <div class="edit-mode">
                    <button href="#" class="button-primary inline-save-link"><?php echo __('Save'); ?></button>
                  </div>
                </td>
              </tr>
          `);

          var rowNode = jQuery(rootNode).find('tr:last-child');

          if (facet.title == null || facet.title == "") {
              rowNode.removeClass('view-mode');
              rowNode.addClass('edit-mode');
          }

          rowNode.find('select[name=field]').append(getFacetFieldOptionsHtml());
          rowNode.find('select[name=field]').prop('value', facet.field);

          var sortOrderSelect = rowNode.find('select[name=sortOrder]');
          sortOrderSelect.append(getSortOrderOptionsHtml());
          sortOrderSelect.prop('value', facet.sortOrder);

          function refreshDisplayValues() {
              rowNode.find('.facet-title .view-mode').html(facet.title);
              rowNode.find('.facet-field .view-mode').html(facet.field);
              rowNode.find('.facet-size .view-mode').html(facet.size);
              var sortOrderText = jQuery(sortOrderSelect).children("option:selected").html();
              rowNode.find('.facet-sort-order .view-mode').html(sortOrderText);
          };

          refreshDisplayValues();

          rowNode.find('input').change(function() {
            var disabled = rowNode.find('input:invalid').length > 0;
            rowNode.find('.inline-save-link').prop('disabled', disabled);
          })
          rowNode.find('input').trigger('change');

          rowNode.find('.inline-edit-link').click(function(ev) {
              ev.preventDefault();

              rowNode.removeClass('view-mode');
              rowNode.addClass('edit-mode');
          });

          rowNode.find('.inline-delete-link').click(function(ev) {
              ev.preventDefault();

              if (confirm("<?php echo _('Are you sure you want delete the facet ?')?>")) {
                  rowNode.remove();
                  facets.splice(facets.indexOf(facet), 1);
                  saveFacets();
              }
          });

          rowNode.find('.inline-save-link').click(function(ev) {
              ev.preventDefault();

              facet.title      = rowNode.find('input[name="title"]').val();
              facet.field      = rowNode.find('select[name=field]').val();
              facet.size       = rowNode.find('input[name=size]').val();
              facet.sortOrder  = rowNode.find('select[name=sortOrder]').val();

              saveFacets();

              refreshDisplayValues();

              rowNode.removeClass('edit-mode');
              rowNode.addClass('view-mode');
          });

          rowNode.on("facet:sort", function() {
              facets.splice(facets.indexOf(facet), 1);
              facets.splice(rowNode.index() - 1, 0, facet);
              saveFacets();
          })
      }

      function onSort(ev, ui) {
          ui.item.trigger("facet:sort");
      }

      function renderFacets() {
          for (var i=0; i< facets.length; i++) {
              renderFacet(facets[i]);
          }

          jQuery('#facets-config .ui-sortable').sortable({"handle": ".handle", "stop": onSort});
          displayEmptyFacetMessage();
      }

      function displayEmptyFacetMessage() {
          if (facets.length > 0) {
             jQuery('#facets-config .no-facets-msg').hide();
          } else {
             jQuery('#facets-config .no-facets-msg').show();
          }
      }

      function saveFacets() {
          var savedFacets = facets.filter(function (facet) {
              return !(facet.title == "" || facet.title == null || facet.field == null || facet.sortOrder == null);
          });

          var data = { action: 'update_facet_config', facet_config: savedFacets, _ajax_nonce: '<?php echo \wp_create_nonce('swiftype-ajax-nonce'); ?>' };
          jQuery.ajax({url: ajaxurl, data: data, dataType: 'json', type: 'POST'});
          displayEmptyFacetMessage();
      }

      jQuery("#facets-config a.add-facet").click(function(ev) {
          facet = {title: "", size: 10};
          facets.push(facet);
          displayEmptyFacetMessage();
          renderFacet(facet);
          ev.preventDefault();
      })

      renderFacets();
  });
</script>
