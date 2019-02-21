<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <h3><?= __('Let\'s create an engine !'); ?></h3>
            <div class="card">
                <form name="swiftype_settings" id="engine-chooser-form" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
                    <?php wp_nonce_field('swiftype-nonce'); ?>
                    <input type="hidden" name="action" value="swiftype_create_engine">
                    <table class="form-table">
                        <colgroup><col witdh="25%"></col><col width="75%"></col></colgroup>
                        <tbody>
                            <tr>
                                <th><label for="engine_name"><?= __("Engine name:"); ?></label></th>
                                <td>
                                    <input type="text" name="engine_name" class="regular-text" placeholder="e.g. Wordpress Site Search" />
                                     <p class="existing-engine" style="no-display">
                                        <em><?= __('An engine already exists with the name "<strong class="engine-name"></strong>".') ?></em>
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
                                    <p class="existing-engine" style="no-display">
                                        <em><?= __('Language can not be changed on existing engine.') ?></em>
                                    </p>
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
    </div>

    <form name="swiftype_reset" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
        <?php wp_nonce_field('swiftype-nonce'); ?>
        <input type="hidden" name="action" value="swiftype_clear_config">
    </form>

    <script type="text/javascript">
        jQuery('#back-authorization-link').click(function() {
            jQuery('form[name=swiftype_reset]').submit();
        });

        jQuery('#engine-chooser-form .existing-engine').hide();

        jQuery('#engine-chooser-form input[name=engine_name]').change(function() {

            var engineName = jQuery.trim(this.value);
            var engine     = null;

            function onExistingEngine(engine) {
                jQuery('#engine-chooser-form select[name=language]').prop('disabled', true);
                jQuery('#engine-chooser-form .engine-name').html(engine.name);
                jQuery('#engine-chooser-form .existing-engine').fadeIn();
                jQuery('#engine-chooser-form input[type=submit]').prop('value', "<?=__('Use Engine'); ?>");
            };

            function onNewEngine(engine) {
                jQuery('#engine-chooser-form input[type=submit]').prop('value', "<?=__('Create Engine'); ?>");
                jQuery('#engine-chooser-form select[name=language]').prop('disabled', false);
                jQuery('#engine-chooser-form .existing-engine').fadeOut();
            }

            jQuery('#engine-chooser-form input[type=submit]').prop('disabled', true);

            if (engineName) {
                var data = { action: 'check_engine_exists', 'engine_name': engineName};
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
    </script>

</div>
