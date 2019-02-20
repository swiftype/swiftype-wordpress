<?php

/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */

$nonce = \wp_create_nonce('swiftype-ajax-nonce');
$indexedDocumentCount = $this->getIndexedDocumentsCount();
$allowedPostTypes = $this->getConfig()->allowedPostTypes();

$total_posts = 0;
$total_posts_in_trash = 0;
foreach( $allowedPostTypes as $type ) {
    $type_count = wp_count_posts($type);
    foreach( $type_count as $status => $count) {
        if( 'publish' == $status ) {
            $total_posts += $count;
        } else {
            $total_posts_in_trash += $count;
        }
    }
}
?>

<div class="wrap">

    <?php include('common/header.php'); ?>

    <div class="swiftype-admin">
        <div class="main-content">
            <p><b>To administer your Swiftype Search Engine, visit the <a href="http://swiftype.com/users/sign_in" target="_new">Swiftype Dashboard</a></b>.</p>

            <div class="card">
                <h3><?= __('Synchronize posts'); ?></h3>
                <?php if ($indexedDocumentCount == 0) : ?>
                    <p>
                        <b><?= __('Important'); ?>:</b> <?= __("Before your site is searchable, you need to synchronize your posts. Click the 'synchronize' button below to begin the process."); ?>
                    </p>
                <?php else : ?>
                    <p>
                        <i>
                        <?= __("Synchronizing your posts with Swiftype ensures that your search engine has indexed all the content you have published."); ?> <br/>
                        <?= __("It shouldn't be necessary to synchronize posts regularly (the update process is automated after your initial setup), but you may use this feature any time you suspect your search index is out of date."); ?>
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
                    <b><?= __("There was an error during synchronization."); ?></b><br/>
                    <?= __("If this problem persists, please email support@swiftype.com and include any error message shown in the text box below, as well as the information listed in the Swiftype Search Plugin Settings box above."); ?><br/>
                    <textarea id="error_text" style="width: 500px; height: 200px; margin-top: 20px;"></textarea>
                </div>
            </div>

            <div class="card">
                <h3><?= __('Customize search'); ?></h3>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td>
                                <p><strong><?= __('Weights') ?></strong></p>
                            </td>
                            <td>
                                <p><em><?= __("Your search relevance function determines the order of your search results."); ?></em></p>
                            </td>
                            <td>
                                <a href="https://app.swiftype.com/engines/<?= $this->getConfig()->getEngineSlug(); ?>/document_types/<?= $this->getConfig()->getDocumentType(); ?>/custom_queries/" class="button-primary" target="_new">
                                    <?= __("Manage Weights"); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong><?= __('Result rankings') ?></strong>
                            </td>
                            <td>
                                <p><em><?= __("Search your engine and re-order the results to your liking."); ?></em></p>
                            </td>
                            <td>
                                <a href="https://app.swiftype.com/engines/<?= $this->getConfig()->getEngineSlug(); ?>/document_types/<?= $this->getConfig()->getDocumentType(); ?>/search/" class="button-primary" target="_new">
                                    <?= __("Manage Rankings"); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong><?= __('Synonyms') ?></strong>
                            </td>
                            <td>
                                <p><em><?= __("Synonyms are groups of terms that will be treated as equivalent for the purposes of search."); ?></em></p>
                            </td>
                            <td>
                                <a href="https://app.swiftype.com/engines/<?= $this->getConfig()->getEngineSlug(); ?>/synonyms" class="button-primary" target="_new">
                                    <?= __("Manage Synonyms"); ?>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card danger">
                <h3><?= __('Dangerous settings'); ?></h3>
                <table class="widefat">
                <tbody>
                    <tr>
                        <td>
                            <?= __("If you're having trouble with the Swiftype plugin, or would like to reconfigure your search engine, you may clear your Swiftype Configuration by clicking the button below. This will allow you to enter a new API key and create a new search engine."); ?>
                        </td>
                        <td>
                        <form name="swiftype_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
                            <?php wp_nonce_field('swiftype-nonce'); ?>
                            <input type="hidden" name="action" value="swiftype_clear_config">
                            <input type="submit" name="Submit" value="Reset Configuration"  class="button-primary" />
                        </form>
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </div>

        <div class="sidebar">

            <table class="widefat">
                <thead>
                    <tr>
                        <th class="row-title" colspan="2"><?= __('Plugin Settings'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= __('API Key:'); ?></td>
                        <td><?= $this->getConfig()->getApiKey(); ?></td>
                    </tr>
                    <tr>
                        <td><?= __('Search Engine:'); ?></td>
                        <td><?= $this->getConfig()->getEngineSlug(); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>


    </div>
</div>

<script>

    jQuery('#index_posts_button').click(function() {
        index_batch_of_posts(0);
        delete_batch_of_posts(0);
    });

    var batch_size = 15;

    var total_posts_written = 0;
    var total_posts_processed = 0;
    var total_posts = <?php print( $total_posts ) ?>;
    var index_batch_of_posts = function(start) {
        set_progress();
        var offset = start || 0;
        var data = { action: 'index_batch_of_posts', offset: offset, batch_size: batch_size, _ajax_nonce: '<?php echo $nonce ?>' };
        jQuery.ajax({
                url: ajaxurl,
                data: data,
                dataType: 'json',
                type: 'POST',
                success: function(response, textStatus) {
                    var increment = response['num_written'];
                    if (increment) {
                        total_posts_written += increment;
                    }
                    total_posts_processed += batch_size;
                    if (response['total'] > 0) {
                        index_batch_of_posts(offset + batch_size);
                    } else {
                        total_posts_processed = total_posts;
                        set_progress();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    try {
                        errorMsg = JSON.parse(jqXHR.responseText).message;
                    } catch (e) {
                        errorMsg = jqXHR.responseText;
                        show_error(errorMsg);
                    }
                }
            }
        );
    };

    var total_posts_in_trash_processed = 0;
    var total_posts_in_trash = <?php print( $total_posts_in_trash ) ?>;
    var delete_batch_of_posts = function(start) {
        set_progress();
        var offset = start || 0;
        var data = { action: 'delete_batch_of_trashed_posts', offset: offset, batch_size: batch_size, _ajax_nonce: '<?php echo $nonce ?>' };
        jQuery.ajax({
                url: ajaxurl,
                data: data,
                dataType: 'json',
                type: 'POST',
                success: function(response, textStatus) {
                    total_posts_in_trash_processed += batch_size;
                    if (response['total'] > 0) {
                        delete_batch_of_posts(offset + batch_size);
                    } else {
                        total_posts_in_trash_processed = total_posts_in_trash;
                        set_progress();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    try {
                        errorMsg = JSON.parse(jqXHR.responseText).message;
                    } catch (e) {
                        errorMsg = jqXHR.responseText;
                        show_error(errorMsg);
                    }
                }
            }
        );
    };

    function show_error(message) {
        jQuery('#synchronizing').fadeOut();
        jQuery('#synchronize_error').fadeIn();
        if(message.length > 0) {
            jQuery('#error_text').append(message).show();
        }
    }

    function set_progress() {
        var total_ops = total_posts + total_posts_in_trash;
        var progress = total_posts_processed + total_posts_in_trash_processed;
        if(progress > total_ops) { progress = total_ops; }
        var progress_width = Math.round(progress / total_ops * 245);
        if(progress_width < 10) { progress_width = 10; }
        if(progress == 0) {
            jQuery('#progress_bar').fadeIn();
        }
        jQuery('#num_indexed_documents').html(total_posts_written);
        jQuery('#progress_bar').find('div.bar').show().width(progress_width);
        if(progress >= total_ops) {
            jQuery('#index_posts_button').html('<?= __("Indexing Complete!"); ?>');
            jQuery('#progress_bar').fadeOut();
            jQuery('#index_posts_button').unbind();
        } else {
            jQuery('#index_posts_button').html('<?= __("Indexing progress..."); ?>' + Math.round(progress / total_ops * 100) + '%');
        }
    }

</script>
