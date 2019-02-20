<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <h3><?= __('Let\'s create an engine !'); ?></h3>
            <div class="card">
                <form name="swiftype_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
                    <?php wp_nonce_field('swiftype-nonce'); ?>
                    <input type="hidden" name="action" value="swiftype_create_engine">
                    <ul>
                        <li>
                            <label>
                                <span class="title"><?= __("Please name the search engine the plugin will use in the field bellow:"); ?></span>
                                <input type="text" name="engine_name" class="regular-text" placeholder="e.g. Wordpress Site Search" />
                             </label>
                        </li>
                    </ul>
                    <div class="controls">
                        <div class="controls-left">
                            <a href="#" onclick="jQuery('form[name=swiftype_reset]').submit();"><?= __('Back to authentication'); ?></a>
                        </div>
                        <div class="controls-right">
                            <input type="submit" name="Submit" value="Create Engine" class="button-primary" id="create_engine"/>
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

</div>
