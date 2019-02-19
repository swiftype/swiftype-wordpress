<?php

namespace Swiftype\SiteSearch\Wordpress\Engine;

use Swiftype\Exception\NotFoundException;
use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

class Manager extends AbstractSwiftypeComponent
{
    public function __construct()
    {
        parent::__construct();

        \add_action('swiftype_client_loaded', [$this, 'initEngine']);
    }

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
