<div class="card danger">
    <h3><?php echo('Dangerous settings'); ?></h3>
    <table class="widefat">
    <tbody>
        <tr>
            <td>
                <?php echo __("If you're having trouble with the Swiftype plugin, or would like to reconfigure your search engine, you may clear your Swiftype Configuration by clicking the button below. This will allow you to enter a new API key and create a new search engine."); ?>
            </td>
            <td>
            <form name="swiftype_settings_reset" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
                <?php wp_nonce_field('swiftype-nonce'); ?>
                <input type="hidden" name="action" value="swiftype_clear_config">
                <input type="submit" name="Submit" value="Reset Configuration"  class="button-primary" />
            </form>
            </td>
        </tr>
    </tbody>
    </table>
</div>

<script type="text/javascript">
     jQuery("form[name=swiftype_settings_reset] input").click(function(ev) {
         if (!confirm('<?php echo __("Are you sure you want to reset the module configuration."); ?>')) {
             ev.preventDefault();
         }
     });
</script>