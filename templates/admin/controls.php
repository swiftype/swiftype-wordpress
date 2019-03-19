<?php

/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */
?>

<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <?php if ($this->hasBeenIndexed()): ?>
                <p><b>To administer your Elastic Site Search Engine, visit the <a href="http://swiftype.com/users/sign_in" target="_new">Site Search Dashboard</a></b>.</p>
            <?php else: ?>
                <ul class="progressbar">
                    <li class="valid"><?php echo __("Authentication"); ?></li>
                    <li class="valid"><?php echo __("Engine creation"); ?></li>
                    <li class="active"><?php echo __("Initial sync."); ?></li>
                </ul>
            <?php endif; ?>
            <?php include('controls/synchronize.php'); ?>
            <?php if ($this->hasBeenIndexed()): ?>
                <?php include('controls/search-settings.php'); ?>
                <?php include('controls/facets-settings.php'); ?>
            <?php endif; ?>
            <?php include('controls/dangerous-settings.php'); ?>
        </div>

        <div class="sidebar">
            <?php include('controls/plugin-settings.php'); ?>
        </div>
    </div>
</div>
