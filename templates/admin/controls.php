<?php

/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */
?>

<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <p><b>To administer your Swiftype Search Engine, visit the <a href="http://swiftype.com/users/sign_in" target="_new">Swiftype Dashboard</a></b>.</p>
            <?php include('controls/synchronize.php'); ?>
            <?php include('controls/search-settings.php'); ?>
            <?php include('controls/dangerous-settings.php'); ?>
        </div>

        <div class="sidebar">
            <?php include('controls/plugin-settings.php'); ?>
        </div>
    </div>
</div>
