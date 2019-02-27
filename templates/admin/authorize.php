<?php
/**
 * Site search authorize admin template.
 *
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */
?>

<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <h3><?= __('Thanks for installing the Site Search Plugin for Wordpress !'); ?></h3>
            <div class="card">
                <p><?= __("Please enter your Site Search API key in the field below and click 'Authorize' to get started."); ?></p>
                <form name="swiftype_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
                    <?php wp_nonce_field('swiftype-nonce'); ?>
                    <input type="hidden" name="action" value="swiftype_set_api_key">
                    <ul>
                        <li>
                            <label>
                                <span class="title no-display"><?= __('Site Search API Key :'); ?></span>
                                <input type="text" name="api_key" class="regular-text" placeholder="<?= __('Enter your API KEY'); ?>" autocomplete="off"/>
                             </label>
                            <input type="submit" name="Submit" value="Authorize" class="button-primary" />
                        </li>
                    </ul>
                </form>
                <?php if ($this->getConfig() && $this->getConfig()->getApiKey() && isset($_POST['api_key'])): ?>
                    <div class="errors">
                       <p>
                           <strong><?= __('Authentication has failed') ?></strong> <br/>
                           <em><?= __('Please check the API Key is correct.') ?></em> <br/>
                           <em><?= __('If this problem persists, please email support@swiftype.com') ?></em>
                       </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar">
            <dl>
              <dt><?= __("New to Site Search ?");?></dt>
              <dd><?= __("If you don't have an API Key, you can get one by signing up for a free account at"); ?> <a href="http://swiftype.com/users/sign_up" target="_new">swiftype.com</a>.</dd>
              <dt><?= __("Existing Site Search user ?");?></dt>
              <dd><?= __('You will find your API Key at the top of the Swiftype <b><a href="https://app.swiftype.com/settings/account">Account Settings</b></a> screen.'); ?></dd>
            </dl>
        </div>
    </div>
</div>
