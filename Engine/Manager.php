<?php

namespace Swiftype\SiteSearch\Wordpress\Engine;

use Swiftype\Exception\NotFoundException;
use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;
use Swiftype\Exception\BadRequestException;

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
                $engine = $this->getEngineUsingListing($engineSlug);
                $this->getConfig()->setEngineSlug($engine['slug']);
            } catch(NotFoundException $e) {
                $engine = $this->getClient()->createEngine($engineSlug);
                $this->getConfig()->setEngineSlug($engine['slug']);
            }
            try {
                $this->getClient()->getDocumentType($engine['slug'], $docType);
            } catch(NotFoundException $e) {
                $this->getClient()->createDocumentType($engine['slug'], $docType);
            }

            \do_action('swiftype_engine_loaded', $engine);
        }
    }

    private function getEngineUsingListing($engineName)
    {
        try {
            $engine = $this->getClient()->getEngine($engineName);
        } catch (NotFoundException $e) {
            $engine = null;
            $currentPage = 1;

            do {
              $engines = $this->getClient()->listEngines($currentPage);
              foreach ($engines as $currentEngine) {
                  if (trim(strtolower($engineName)) == trim(strtolower($currentEngine['name']))) {
                      $engine = $currentEngine;
                  }
              }
              $currentPage++;
              $hasRecords = count($engines) > 0;
            } while ($engine === null && $hasRecords);

            if (!$engine) {
                throw $e;
            }
        }

        return $engine;
    }
}
