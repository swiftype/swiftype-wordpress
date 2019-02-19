<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

class Page extends AbstractSwiftypeComponent
{
    const MENU_TITLE = 'Swiftype Search';
    const MENU_SLUG  = 'swiftype';
    const MENU_ICON  = 'assets/swiftype_logo_menu.png';

    public function __construct()
    {
        parent::__construct();
        \add_action('admin_menu', [$this, 'addMenu']);
        \add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    public function getContent()
    {
        $isAuth = $this->getConfig()->getApiKey() && $this->getClient() !== null;

        if (!$isAuth) {
            include(__DIR__ . '/../swiftype-authorize.php');
        } else if (!$this->getConfig()->getEngineSlug()) {
            include(__DIR__ . '/../swiftype-choose-engine.php');
        } else {
            include(__DIR__ . '/../swiftype-controls.php');
        }
    }

    public function addMenu()
    {
        if (\is_admin() && \current_user_can('manage_options')) {
            \add_menu_page(self::MENU_TITLE, SELF::MENU_TITLE, 'manage_options', SELF::MENU_SLUG, [$this, 'getContent'], $this->getIconUrl());
        }
    }

    public function enqueueAdminAssets($hook)
    {
        if ('toplevel_page_swiftype' == $hook && \is_admin() && \current_user_can('manage_options')) {
            \wp_enqueue_style('admin_styles', \plugins_url('assets/admin_styles.css', __DIR__ . '/../swiftype.php'));
        }
    }

    public function getIndexedDocumentsCount()
    {
        $documentTypeInfo = $this->getClient()->getDocumentType($this->getConfig()->getEngineSlug(), $this->getConfig()->getDocumentType());

        return $documentTypeInfo['document_count'];
    }

    private function getIconUrl()
    {
        return \plugins_url(self::MENU_ICON, __DIR__ . '/../swiftype.php');
    }
}
