<?php
/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */

$engineSlug   = $this->getConfig()->getEngineSlug();
$documentType = $this->getConfig()->getDocumentType();

$searchSettingsRows = [
  [
    'title'        => __('Weights'),
    'description'  => __("Your search relevance function determines the order of your search results."),
    'url'          => sprintf("https://app.swiftype.com/engines/%s/document_types/%s/custom_queries/", $engineSlug, $documentType),
    'button_title' => __("Manage Weights"),
  ],
  [
    'title'        => __('Result rankings'),
    'description'  => __("Search your Engine and re-order search results to your liking."),
    'url'          => sprintf("https://app.swiftype.com/engines/%s/document_types/%s/search/", $engineSlug, $documentType),
    'button_title' => __("Manage Rankings"),
  ],
  [
    'title'        => __('Synonyms'),
    'description'  => __("Synonyms are groups of terms that will be treated as equivalent for the purposes of search."),
    'url'          => sprintf("https://app.swiftype.com/engines/%s/synonyms", $engineSlug),
    'button_title' => __("Manage Synonyms"),
  ],
];

?>

<div class="card">
    <h3><?php echo __('Customize search'); ?></h3>
    <table class="widefat">
        <tbody>
            <?php foreach ($searchSettingsRows as $row): ?>
            <tr>
                <td><strong><?php echo $row['title']; ?></strong></td>
                <td><p><em><?php echo $row['description']; ?></em></p></td>
                <td><a href="<?php echo $row['url']; ?>" class="button-primary" target="_new"><?php echo $row['button_title']?></a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
