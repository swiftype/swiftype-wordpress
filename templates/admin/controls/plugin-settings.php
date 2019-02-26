<?php
/**
 * @var \Swiftype\SiteSearch\Wordpress\Admin\Page $this
 */
?>

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