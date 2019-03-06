<?php

namespace Swiftype\SiteSearch\Wordpress\Cli;

use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * Command-line interface for the integrating WordPress with the Swiftype API.
 */
class Command extends \WP_CLI_Command
{
    /**
     * @var string
     */
    const SETUP_API_KEY_PARAM_NAME = 'api-key';

    /**
     * @var string
     */
    const SETUP_ENGINE_PARAM_NAME = 'engine';

    /**
     * @var string
     */
    const SYNC_DESTRUCTIVE_PARAM_NAME = 'destructive';

    /**
     * @var string
     */
    const SYNC_INDEX_BATCH_SIZE_PARAM_NAME = 'index-batch-size';

    /**
     * @var string
     */
    const SYNC_DELETE_BATCH_SIZE_PARAM_NAME = 'delete-batch-size';

    /**
     * @var string
     */
    const SYNC_INDEX_BATCH_SIZE_DEFAULT  = 15;

    /**
     * @var string
     */
    const SYNC_DELETE_BATCH_SIZE_DEFAULT = 100;

    /**
     * @var \Swiftype\SiteSearch\Client
     */
    private $client = null;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $engine = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = new Config();
    }

    /**
     * Set up the plugin and create a search engine. This will reset existing configuration.
     *
     * ## OPTIONS
     *
     * <api-key>
     * : Site Search API key.
     *
     * <engine>
     * : Name of the search engine to create.
     *
     * ## EXAMPLES
     *
     *     wp swiftype setup --api-key=your_api_key --engine="My WordPress Search"
     *
     * @synopsis --api-key=<your-api-key> --engine=<name>
     */
    public function setup($args, $assocArgs)
    {
        \WP_CLI::line("Installing swiftype ...");

        $mandatoryArgs = [self::SETUP_API_KEY_PARAM_NAME, self::SETUP_ENGINE_PARAM_NAME];

        if($this->checkMandatoryArgs($assocArgs, $mandatoryArgs) === false) {
            \WP_CLI::halt(1);
        }

        $apiKey     = \sanitize_text_field($assocArgs['api-key']);
        $engineSlug = \sanitize_text_field($assocArgs['engine']);

        \add_action('swiftype_client_loaded', function($client) { $this->updateClient($client); });
        \add_action('swiftype_engine_loaded', function($engine) { $this->updateEngine($engine); });

        $this->config->setApiKey($apiKey);
        $this->config->setEngineSlug($engineSlug);

        \do_action('swiftype_config_loaded', $this->config);

        if ($this->client == null) {
            \WP_CLI::error("Unable to connect to Site Search : check your API key is valid.");
        }

        if ($this->engine == null) {
            \WP_CLI::error("Unable to create engine with the name \"${engineSlug}\"");
        }

        \WP_CLI::line("   API Key : ${apiKey}");
        \WP_CLI::line("   Engine  : ${engineSlug}");

        \WP_CLI::line("");
        \WP_CLI::line("Site Search is now configured. Before being able to search you need to sync your posts with the engine.");
        \WP_CLI::line(" - Using the following command : \"wp swiftype sync\"");
        \WP_CLI::line(" - From the Site Search tab into Wordpress admin");
        \WP_CLI::line("");
    }

    /**
     * Synchronize your WordPress database with the Swiftype search engine configured in the WordPress admin.
     *
     * ## OPTIONS
     *
     * <index-batch-size>
     * : The number of posts to index at once. Defaults to 15.
     *
     * <delete-batch-size>
     * : The number of non-published posts to delete at once. Defaults to 100.
     *
     * <destructive>
     * : Delete the posts DocumentType and re-index all published posts from scratch.
     *
     * ## EXAMPLES
     *
     * Synchronize with the default settings:
     *
     *     wp swiftype sync
     *
     * Destructively synchronize with a large index batch size. This will be faster, but large batch sizes only work with small post data.
     *
     *     wp swiftype sync --destructive --index-batch-size=100
     *
     * @synopsis [--destructive] [--delete-batch-size=<number>] [--index-batch-size=<number>]
     */
    function sync($args, $assocArgs)
    {
        \add_action('swiftype_client_loaded', function($client) { $this->updateClient($client); });
        \add_action('swiftype_engine_loaded', function($engine) { $this->updateEngine($engine); });

        \do_action('swiftype_config_loaded', $this->config);

        if ($this->client === null) {
            \WP_CLI::error("Unable to connect to Site Search : check your API key is valid.");
        }

        if ($this->engine === null) {
            \WP_CLI::error("Site Search plugin is not configured. Use the \"wp swiftype setup\" command to proceed.");
        }

        if (isset($assocArgs[self::SYNC_DESTRUCTIVE_PARAM_NAME])) {
            $this->resetDocuments();
        } else {
            $deleteBatchSize = self::SYNC_DELETE_BATCH_SIZE_DEFAULT;
            if (isset($assocArgs[self::SYNC_DELETE_BATCH_SIZE_PARAM_NAME])) {
                $deleteBatchSize = $this->parseIntegerArg($assocArgs[self::SYNC_DELETE_BATCH_SIZE_PARAM_NAME], $deleteBatchSize);
            }
            $this->deleteDocuments($deleteBatchSize);
        }

        $indexBatchSize = self::SYNC_INDEX_BATCH_SIZE_DEFAULT;
        if (isset($assocArgs[self::SYNC_INDEX_BATCH_SIZE_PARAM_NAME])) {
            $indexBatchSize = $this->parseIntegerArg($assocArgs[self::SYNC_INDEX_BATCH_SIZE_PARAM_NAME], $indexBatchSize);
        }
        $this->indexDocuments($indexBatchSize);
    }

    /**
     * Reset all documents of the engine.
     */
    private function resetDocuments()
    {
        \WP_CLI::confirm("Delete all documents and re-index?");
        \WP_CLI::line("Deleting existing documents...");

        $checkDocumentTypeExists = true;

        try {
            $this->client->deleteDocumentType($this->config->getEngineSlug(), $this->config->getDocumentType());
        } catch (\Swiftype\Exception\NotFoundException $e) {
            $checkDocumentTypeExists = false;
        }

        while ($checkDocumentTypeExists == true) {
            try {
                $this->client->getDocumentType($this->config->getEngineSlug(), $this->config->getDocumentType());
            } catch (\Swiftype\Exception\NotFoundException $e) {
                $checkDocumentTypeExists = false;
            }
        }

        $this->client->createDocumentType($this->config->getEngineSlug(), $this->config->getDocumentType());
    }

    /**
     * Delete all trashed documents of the engine.
     *
     * @param int $batchSize
     */
    private function deleteDocuments($batchSize)
    {
        \WP_CLI::line( "Deleting trashed posts ...");
        $offset   = 0;
        $statuses = array_diff(\get_post_stati(), ['publish']);
        $deletedPosts = 0;

        \add_action('swiftype_batch_post_delete_result', function ($count) use (&$deletedPosts) {
            $deletedPosts = $count;
        });

        do {
            $deletedPosts = 0;
            $posts = $this->getPosts($offset, $batchSize, $statuses);
            $totalPosts = count($posts);
            $offset += $totalPosts;

            if ($totalPosts) {
                $postIds = array_map(function($post) { return $post->ID; }, $posts);
                \do_action('swiftype_batch_post_delete', $postIds);
                \WP_CLI::line("   Deleted ${deletedPosts} of ${totalPosts} trashed posts");
            }
        } while ($totalPosts > 0);
    }

    /**
     * Index documents into the engine.
     *
     * @param int $batchSize
     */
    private function indexDocuments($batchSize)
    {
        \WP_CLI::line( "Indexing posts ...");
        $offset   = 0;
        $status = 'publish';
        $currentStats = ['success' => 0, 'errors' => 0];

        \add_action('swiftype_batch_post_index_result', function ($stats) use (&$currentStats) {
            $currentStats = $stats;
        });

        do {
            $currentStats = ['success' => 0, 'errors' => 0];
            $posts = $this->getPosts($offset, $batchSize, $status);
            $totalPosts = count($posts);
            $offset += $totalPosts;

            if ($totalPosts) {
                \do_action('swiftype_batch_post_index', $posts);
                $message = "   Indexed ${currentStats['success']} of ${totalPosts} posts";
                if (isset($currentStats['errors']) && $currentStats['errors'] > 0) {
                    $message .= " (${currentStats['errors']} errors)";
                }
                \WP_CLI::line($message);
            }
        } while ($totalPosts > 0);
    }

    /**
     * Method called when the config is loaded to update the client.
     *
     * @param Client $client
     */
    private function updateClient($client)
    {
        $this->client = $client;
    }

    /**
     * Method called when the engine is loaded to update the current engine.
     *
     * @param array $engine
     */
    private function updateEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Check all mandatory arguments are present.
     *
     * @param array $args
     * @param array $mandatoryArgs
     *
     * @return boolean
     */
    private function checkMandatoryArgs($args, $mandatoryArgs)
    {
        $isValid = true;

        foreach ($mandatoryArgs as $argName) {
            if (!isset($args[$argName]) || empty($args[$argName])) {
                $isValid = false;
                \WP_CLI::error($this->getMissingArgErrorMessage($argName), false);
            }
        }

        return $isValid;
    }

    /**
     * Retrieve a list of post filtered on a status or a status list.
     *
     * Only posts that are meant to be indexed are retrieved (see Config::allowedPostTypes).
     *
     * @param int          $offset
     * @param int          $batchSize
     * @param string|array $status
     *
     * @return array
     */
    private function getPosts($offset, $batchSize, $status) {
        $query = [
            'numberposts' => $batchSize,
            'offset' => $offset,
            'orderby' => 'id',
            'order' => 'ASC',
            'post_status' => $status,
            'post_type' => $this->config->allowedPostTypes(),
        ];

        return \get_posts($query);
    }

    /**
     * Parse an integer argument and fallback to the default value if fails.
     *
     * @param mixed $arg
     * @param int   $default
     *
     * @return int
     */
    private function parseIntegerArg($arg, $default)
    {
        $value = $default;

        if (intval($arg) > 0) {
            $value = intval($arg);
        }

        return $value;
    }

    /**
     * Errro message to display when a mandatory argument is missing/empty.
     *
     * @param string $paramName
     *
     * @return string
     */
    private function getMissingArgErrorMessage($paramName)
    {
        return sprintf('Missing argument error : --%s is mandatory and should not be empty.', $paramName);
    }
}
