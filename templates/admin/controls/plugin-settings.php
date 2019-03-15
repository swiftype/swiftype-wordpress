<?php
/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */
?>

 <table class="widefat">
    <thead>
        <tr>
            <th class="row-title" colspan="2"><?= __('Plugin Settings'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr id="api-key-row">
            <td><?= __('API Key:'); ?></td>
            <td class="toggle-password">
                <input type="password" class="value" value="<?= $this->getConfig()->getApiKey(); ?>"/>
                <div class="toggle-button"></div>
            </td>
        </tr>
        <tr>
            <td><?= __('Search Engine:'); ?></td>
            <td><?= $this->getConfig()->getEngineSlug(); ?></td>
        </tr>
    </tbody>
</table>

<script type="text/javascript">
    jQuery('document').ready(function() {
        jQuery(".toggle-password .toggle-button").click(function() {
         jQuery(this.parentNode).toggleClass('visible');
         var inputType = jQuery(this.parentNode).hasClass('visible') ? 'text' : 'password';
         jQuery(this.parentNode).find('.value').prop('type', inputType);
        });
    });
</script>