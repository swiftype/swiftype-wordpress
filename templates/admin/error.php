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
        <div class="connection-error">
           <h3>
               <?php echo __("Unable to connect to Site Search"); ?>
           </h3>
           <p class="error-message">
               <strong>
               <?php if ($this->error instanceof \Elastic\OpenApi\Codegen\Exception\ConnectionException) : ?>
                   <?php echo __("Connection error:"); ?>
               <?php else : ?>
                   <?php echo __("Unexepected error:"); ?>
               <?php endif; ?>
               </strong>
               <span class="content"><?php echo $this->error->getMessage();?></span>
           </p>
           <p><?php echo  __("If this problem persists, please email support@swiftype.com and include the above error message."); ?></p>

           <div class="controls">
               <div class="controls-right">
                   <a href="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" class="button-primary"><?= __('Retry'); ?></a>
               </div>
           </div>
        </div>
    </div>
</div>
