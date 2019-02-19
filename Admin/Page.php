<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

/**
 * Implementation of the admin page for the Site Search Wordpress plugin.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Page extends AbstractSwiftypeComponent
{
    /**
     * @var string
     */
    const MENU_TITLE = 'Site Search';

    /**
     * @var string
     */
    const MENU_SLUG  = 'site-search';

    /**
     * @var string
     */
    const MENU_ICON  = 'assets/swiftype_logo_menu.png';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        \add_action('admin_menu', [$this, 'addMenu']);
        \add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Display admin page content.
     */
    public function getContent()
    {
        $isAuth = $this->getConfig()->getApiKey() && $this->getClient() !== null;

        if (!$isAuth) {
            $this->renderTemplate('authorize.php');
        } else if (!$this->getConfig()->getEngineSlug()) {
            $this->renderTemplate('choose-engine.php');
        } else {
            $this->renderTemplate('controls.php');
        }
    }

    /**
     * Add the Site Search entry to the admin menu.
     */
    public function addMenu()
    {
        \add_menu_page(self::MENU_TITLE, SELF::MENU_TITLE, 'manage_options', SELF::MENU_SLUG, [$this, 'getContent'], $this->getIconUrl());
    }

    /**
     * Add Site Search CSS styles to the assets.
     */
    public function enqueueAdminAssets($hook)
    {
        if ('toplevel_page_site-search' == $hook && \is_admin()) {
            \wp_enqueue_style('admin_styles', \plugins_url('assets/admin_styles.css', __DIR__ . '/../swiftype.php'));
        }
    }

    /**
     * Return the number of currently indexed documents.
     *
     * @return int
     */
    public function getIndexedDocumentsCount()
    {
        $engine = $this->getConfig()->getEngineSlug();
        $docType = $this->getConfig()->getDocumentType();

        $documentTypeInfo = $this->getClient()->getDocumentType($engine, $docType);

        return $documentTypeInfo['document_count'];
    }

    /**
     * Return menu icon URL.
     *
     * @return string
     */
    private function getIconUrl()
    {
        return \plugins_url(self::MENU_ICON, __DIR__ . '/../swiftype.php');
    }

    /**
     * Locate and render a template.
     *
     * @param string $templateFile
     */
    private function renderTemplate($templateFile)
    {
        include(sprintf("%s/%s", $this->getTemplateDir(), $templateFile));
    }

    /**
     * Get the template directory.
     *
     * @return string
     */
    private function getTemplateDir()
    {
        return sprintf("%s/../templates/admin", __DIR__);
    }
}
