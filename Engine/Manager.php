<?php

namespace Swiftype\SiteSearch\Wordpress\Engine;

use Swiftype\Exception\NotFoundException;
use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

/**
 * Check the engine exists and create it if needed.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Manager extends AbstractSwiftypeComponent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        \add_action('swiftype_client_loaded', [$this, 'initEngine']);
    }

    /**
     * Check the engine exists and create it if needed.
     */
    public function initEngine()
    {
        $engineSlug = $this->getConfig()->getEngineSlug();
        $docType    = $this->getConfig()->getDocumentType();

        if ($engineSlug) {
            try {
                $engine = $this->getClient()->getEngine($engineSlug);
            } catch(NotFoundException $e) {
                $engine = $this->getClient()->createEngine($engineSlug);
            }
            try {
                $this->getClient()->getDocumentType($engineSlug, $docType);
            } catch(NotFoundException $e) {
                $this->getClient()->createDocumentType($engineSlug, $docType);
            }

            \do_action('swiftype_engine_loaded', $engine);
        }
    }
}
