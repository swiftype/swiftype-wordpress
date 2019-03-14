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

            <?php if ($this->getConfig()->getApiKey() && $this->getConfig()->getEngineSlug()): ?>
                <div class="errors">
                   <p>
                       <strong><?= __('The configured API is invalid.') ?></strong> <br/>
                   </p>
                </div>
            <?php else: ?>
                <ul class="progressbar">
                    <li class="active"><?php echo __("Authentication"); ?></li>
                    <li><?php echo __("Engine creation"); ?></li>
                    <li><?php echo __("Initial sync."); ?></li>
                </ul>
            <?php endif; ?>

            <div class="card">
                <p><?= __("Please enter your Site Search API key in the field below and click 'Authorize' to get started."); ?></p>
                <form name="swiftype_settings" method="post" action="<?php echo \esc_url(\admin_url()); ?>">
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
                <?php if ($this->getConfig() && $this->getConfig()->getApiKey() && isset($_REQUEST['error'])): ?>
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
              <dd><?= __('You will find your API Key at the top of the Site Search <b><a href="https://app.swiftype.com/settings/account" target="_new">Account Settings</b></a> screen.'); ?></dd>
            </dl>
        </div>
    </div>
</div>
