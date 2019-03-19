<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <ul class="progressbar">
                <li class="valid"><?php echo __("Authentication"); ?></li>
                <li class="active"><?php echo __("Engine creation"); ?></li>
                <li><?php echo __("Initial sync."); ?></li>
            </ul>
            <div class="card">
                <form name="swiftype_settings" id="engine-chooser-form" method="post" action="<?php echo \esc_url(\admin_url()); ?>">
                    <?php wp_nonce_field('swiftype-nonce'); ?>
                    <input type="hidden" name="action" value="swiftype_create_engine">
                    <?php if (isset($_REQUEST['error'])): ?>
                    <div class="errors">
                       <p>
                           <strong><?= __('An error occcured while creating the Engine. Try again!') ?></strong> <br/>
                           <em><?= __('If this problem persists, please email support@swiftype.com') ?></em>
                       </p>
                    </div>
                    <?php endif; ?>
                    <table class="form-table">
                        <colgroup><col witdh="25%"></col><col width="75%"></col></colgroup>
                        <tbody>
                            <tr>
                                <th><label for="engine_name"><?= __("Engine name:"); ?></label></th>
                                <td>
                                    <input type="text" name="engine_name" class="regular-text" placeholder="e.g. Wordpress Site Search" />
                                    <p class="existing-engine" style="no-display">
                                        <em><?= __('An Engine already exists with the name "<strong class="engine-name"></strong>".') ?></em>
                                    </p>
                                    <p class="existing-engine" style="no-display">
                                        <em><?= __('The existing Engine will be deleted and all data it contains will be lost.') ?></em>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="language"><?= __("Language:"); ?></label></th>
                                <td>
                                    <select name="language">
                                        <option value=""><?= __('Universal'); ?></option>
                                        <option value="pt-br">Brazilian Portuguese</option>
                                        <option value="zh">Chinese</option>
                                        <option value="da">Danish"</option>
                                        <option value="nl">Dutch</option>
                                        <option value="en">English</option>
                                        <option value="fr">French</option>
                                        <option value="de">German</option>
                                        <option value="it">Italian</option>
                                        <option value="ja">Japanese</option>
                                        <option value="ko">Korean</option>
                                        <option value="pt">Portuguese</option>
                                        <option value="ru">Russian</option>
                                        <option value="es">Spanish</option>
                                        <option value="th">Thai</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="controls">
                        <div class="controls-left">
                            <a href="#" id="back-authorization-link"><?= __('Back to authentication'); ?></a>
                        </div>
                        <div class="controls-right">
                            <input type="submit" name="Submit" disabled value="<?=__('Create Engine'); ?>" class="button-primary" id="create_engine"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="sidebar">
            <dl>
              <dt><?= __("What is an Engine ?");?></dt>
              <dd><?= __("Engine is short for <em>search engine</em>."); ?></dd>
              <dd><?= __("Once your posts are pushed into your Engine, they will become searchable documents."); ?></dd>
            </dl>
        </div>
    </div>

    <form name="swiftype_reset" method="post" action="<?php echo \esc_url(\admin_url()); ?>">
        <?php wp_nonce_field('swiftype-nonce'); ?>
        <input type="hidden" name="action" value="swiftype_clear_config">
    </form>

</div>


<script type="text/javascript">
    jQuery(document).ready(function() {

        var engineExists = false;

        jQuery('#back-authorization-link').click(function() {
            jQuery('form[name=swiftype_reset]').submit();
        });

        jQuery('#engine-chooser-form .existing-engine').hide();

        function onExistingEngine(engine) {
            jQuery('#engine-chooser-form .engine-name').html(engine.name);
            jQuery('#engine-chooser-form .existing-engine').fadeIn();
            jQuery('#engine-chooser-form input[type=submit]').prop('value', "<?=__('Use Engine'); ?>");
            engineExists = true;
        };

        function onNewEngine(engine) {
            jQuery('#engine-chooser-form input[type=submit]').prop('value', "<?=__('Create Engine'); ?>");
            jQuery('#engine-chooser-form .existing-engine').fadeOut();
            engineExists = false;
        }

        jQuery('#engine-chooser-form').on("submit", function (ev) {
            if (engineExists && !confirm('<?=__('All data in the existing Engine will be lost. Are you sure you want to continue?'); ?>')) {
                ev.preventDefault();
            }
        });

        jQuery('#engine-chooser-form input[name=engine_name]').change(function() {

            var engineName = jQuery.trim(this.value);

            jQuery('#engine-chooser-form input[type=submit]').prop('disabled', true);

            if (engineName) {
                var data = { action: 'check_engine_exists', 'engine_name': engineName, _ajax_nonce: '<?php echo \wp_create_nonce('swiftype-ajax-nonce'); ?>' };
                jQuery('#engine-chooser-form input[type=submit]').prop('value', "<?=__('Check Engine ...'); ?>");
                jQuery.ajax({
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    type: 'POST',
                    success: function(response, textStatus) {
                        jQuery('#engine-chooser-form input[type=submit]').prop('disabled', false);
                        if (response) {
                            onExistingEngine(response);
                        } else {
                            onNewEngine();
                        }
                    }
                });
            } else {
                onNewEngine();
            }
        });
    });
</script>
