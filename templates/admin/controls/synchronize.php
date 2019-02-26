<?php
/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */

$nonce = \wp_create_nonce('swiftype-ajax-nonce');
$allowedPostTypes = $this->getConfig()->allowedPostTypes();

$totalPosts = 0;
$trashedPosts = 0;
foreach( $allowedPostTypes as $type ) {
    $type_count = wp_count_posts($type);
    foreach( $type_count as $status => $count) {
        if( 'publish' == $status ) {
            $totalPosts += $count;
        } else {
            $trashedPosts += $count;
        }
    }
}

?>

<div class="card">
    <h3><?php echo __('Synchronize posts'); ?></h3>
    <?php if ($this->hasBeenIndexed() == false) : ?>
        <p>
            <b><?php echo __('Important'); ?>:</b> <?= __("Before your site is searchable, you need to synchronize your posts. Click the 'synchronize' button below to begin the process."); ?>
        </p>
    <?php else : ?>
        <p>
            <i>
            <?php echo __("Synchronizing your posts with Swiftype ensures that your search engine has indexed all the content you have published."); ?> <br/>
            <?php echo __("It shouldn't be necessary to synchronize posts regularly (the update process is automated after your initial setup), but you may use this feature any time you suspect your search index is out of date."); ?>
            </i>
        </p>
    <?php endif; ?>

    <div class="controls">
        <div class="controls-left">
            <div id="synchronizing">
                <div class="swiftype" id="progress_bar" style="display: none;">
                    <div class="progress">
                        <div class="bar" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="controls-right">
            <a href="#" id="index_posts_button" class="button-primary"><?= __('Synchronize'); ?></a>
        </div>
    </div>

    <div id="synchronize_error" style="display: none; color: red;">
        <b><?php echo ("There was an error during synchronization."); ?></b><br/>
        <?php echo  __("If this problem persists, please email support@swiftype.com and include any error message shown in the text box below, as well as the information listed in the Swiftype Search Plugin Settings box above."); ?><br/>
        <textarea id="error_text" style="width: 500px; height: 200px; margin-top: 20px;"></textarea>
    </div>
</div>

<script type="text/javascript">
(function syncUi() {
    var batchSize = 15;
    var nounce = '<?php echo $nonce; ?>';

    var indexingStats = {
        total: <?php echo $totalPosts; ?>,
        processed: 0
    };

    var deleteStats = {
        total: <?php echo $trashedPosts; ?>,
        processed: 0
    };

    var total_posts_written = 0;
    var total_posts_processed = 0;
    var total_posts = <?php echo $totalPosts; ?>;
    var total_posts_in_trash_processed = 0;
    var total_posts_in_trash = <?php echo $trashedPosts; ?>;

    function setProgress() {
        var totalOps = indexingStats.total + deleteStats.total;
        var progress = indexingStats.processed + deleteStats.processed;
        if(progress > totalOps) {
            progress = totalOps;
        }
        var progressWidth = Math.round(progress / totalOps * 245);
        if(progressWidth < 10) {
            progressWidth = 10;
        }
        if (progress == 0) {
            jQuery('#progress_bar').fadeIn();
        }
        jQuery('#progress_bar').find('div.bar').show().width(progressWidth);
        if (progress >= totalOps) {
            jQuery('#index_posts_button').html('<?php echo __("Indexing Complete!"); ?>');
            jQuery('#progress_bar').fadeOut();
            jQuery('#index_posts_button').unbind();
            jQuery('#index_posts_button').click(function(ev) {ev.preventDefault(); });
        } else {
            jQuery('#index_posts_button').html('<?php echo __("Indexing progress..."); ?>' + Math.round(progress / totalOps * 100) + '%');
        }
    };

    function showErrors(message) {
        jQuery('#synchronizing').fadeOut();
        jQuery('#synchronize_error').fadeIn();
        if (message.length > 0) {
            jQuery('#error_text').append(message).show();
        }
    };

    function onError(jqXHR, textStatus, errorThrown) {
        try {
            errorMsg = JSON.parse(jqXHR.responseText).message;
        } catch (e) {
            errorMsg = jqXHR.responseText;
            showErrors(errorMsg);
        }
    };

    function onIndexBatchSuccess(response) {
        indexingStats.processed += batchSize;
        if (response['total'] > 0) {
            indexBatchOfPosts();
        } else {
            indexingStats.processed = indexingStats.total;
            setProgress();
        }
    };

    function indexBatchOfPosts() {
        setProgress();
        var data = { action: 'index_batch_of_posts', offset: indexingStats.processed, batch_size: batchSize, _ajax_nonce: nounce };
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            dataType: 'json',
            type: 'POST',
            success: onIndexBatchSuccess,
            error: onError
        });
    };

    function onDeleteBatchSuccess(response) {
        deleteStats.processed += batchSize;
        if (response['total'] > 0) {
            deleteBatchOfPosts();
        } else {
            deleteStats.processed = deleteStats.total;
            setProgress();
        }
    };

    function deleteBatchOfPosts() {
        setProgress();
        var data = { action: 'delete_batch_of_trashed_posts', offset: deleteStats.processed, batch_size: batchSize, _ajax_nonce: nounce };
        jQuery.ajax({
                url: ajaxurl,
                data: data,
                dataType: 'json',
                type: 'POST',
                success: onDeleteBatchSuccess,
                error: onError
            }
        );
    }

    jQuery('#index_posts_button').click(function(ev) {
        indexBatchOfPosts();
        deleteBatchOfPosts();
        ev.preventDefault();
    });

})();

</script>