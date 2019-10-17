<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;
use Swiftype\Exception\SwiftypeException;

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
     * @var array
     */
    private $documentTypeInfo;

    /**
     * @var \Exception
     */
    private $error;

    /**
     * @var
     */
    private $engine;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        \add_action('admin_menu', [$this, 'addMenu']);
        \add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        \add_action('swiftype_admin_error', [$this, 'setError']);

        \add_action('swiftype_engine_loaded', function($engine) {
            $this->engine = $engine;
        });
    }

    /**
     * Display admin page content.
     */
    public function getContent()
    {
        if (\current_user_can('manage_options')) {
            $isAuth = $this->getConfig()->getApiKey() && $this->getClient() !== null;

            if($this->error) {
                $this->renderTemplate('error.php');
            } else if (!$isAuth) {
                $this->renderTemplate('authorize.php');
            } else if (!$this->getConfig()->getEngineSlug() || null === $this->engine) {
                $this->renderTemplate('choose-engine.php');
            } else {
                $this->renderTemplate('controls.php');
            }
        }
    }

    /**
     * Hook called when a connection error is thrown during client init.
     *
     * @param SwiftypeException $e
     */
    public function setError(SwiftypeException $e) {
        $this->error = $e;
    }

    /**
     * Add the Site Search entry to the admin menu.
     */
    public function addMenu()
    {
        if (\current_user_can('manage_options')) {
          \add_menu_page(self::MENU_TITLE, SELF::MENU_TITLE, 'manage_options', SELF::MENU_SLUG, [$this, 'getContent'], $this->getIconUrl());
        }
    }

    /**
     * Add Site Search CSS styles to the assets.
     */
    public function enqueueAdminAssets($hook)
    {
        if ('toplevel_page_site-search' == $hook && \is_admin()) {
            \wp_enqueue_style('admin_styles', \plugins_url('assets/admin_styles.css', __DIR__ . '/../swiftype.php'));
            \wp_enqueue_style('dashicons');
            \wp_enqueue_script('jquery-ui-core');
            \wp_enqueue_script('jquery-ui-sortable');
        }
    }

    /**
     * Load document type data.
     *
     * @return array
     */
    public function getDocumentTypeInfo()
    {
        if ($this->documentTypeInfo === null) {
            $engine = $this->getConfig()->getEngineSlug();
            $docType = $this->getConfig()->getDocumentType();
            $this->documentTypeInfo = $this->getClient()->getDocumentType($engine, $docType);
        }

        return $this->documentTypeInfo;
    }

    /**
     * Check if indexing have been run at least once.
     *
     * Previous version where using doc counts but because indexing is async it is not reliable.
     * We now check if some fields are present into the mapping.
     *
     * @return boolean
     */
    public function hasBeenIndexed()
    {
        $documentTypeInfo = $this->getDocumentTypeInfo();

        return !empty($documentTypeInfo['field_mapping']);
    }

    /**
     * Retrieve a list of field that can be used has facets from the mapping.
     *
     * @return array
     */
    public function getFacetFieldsFromMapping()
    {
        $allowedFieldTypes = ['string', 'enum', 'integer', 'float', 'date'];
        $forbiddenFields   = ['external_id', 'timestamp', 'title', 'updated_at', 'url'];

        $fields = array_filter($this->getDocumentTypeInfo()['field_mapping'], function ($fieldType) use ($allowedFieldTypes) {
            return in_array($fieldType, $allowedFieldTypes);
        });

        return array_values(array_diff(array_keys($fields), $forbiddenFields));
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
