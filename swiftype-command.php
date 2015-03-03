<?php
/**
 * Command-line interface for the integrating WordPress with the Swiftype API.
 */
class Swiftype_Command extends WP_CLI_Command {
	/**
	 * Set up the plugin and create a search engine. This will reset existing configuration.
	 *
	 * ## OPTIONS
	 *
	 * <api-key>
	 * : Swiftype API key.
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
	function setup( $args, $assoc_args ) {
		global $swiftype_plugin;

		$swiftype_plugin->clear_config();

		$api_key = sanitize_text_field( $assoc_args['api-key'] );
		$engine_name = sanitize_text_field( $assoc_args['engine'] );

		update_option( 'swiftype_api_key', $api_key );

		try {
			$swiftype_plugin->initialize_engine( $engine_name );
		} catch ( SwiftypeError $e ) {
			WP_CLI::log( $e );
			WP_CLI::error( "Could not set up search engine. You may be over your engine limit or trying to use a name that is already taken." );
		}
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
	function sync( $args, $assoc_args ) {
		global $swiftype_plugin;

		$destructive = $assoc_args['destructive'];

		if ( $destructive ) {
			WP_CLI::confirm( "Delete all documents and re-index?" );

			$engine_slug = get_option( 'swiftype_engine_slug' );
			WP_CLI::log("Deleting existing documents...");

			try {
				$swiftype_plugin->client()->delete_document_type( $engine_slug, 'posts' );
			} catch( SwiftypeError $e ) {
				if ( $e->getCode() == 404 ) {
					WP_CLI::warning( "No 'posts' DocumentType, ignoring." );
				} else {
					WP_CLI::log( $e );
					WP_CLI::error( "Could not delete 'posts' DocumentType, aborting." );
				}
			}

			while ( true ) {
				try {
					$swiftype_plugin->client()->find_document_type( $engine_slug, 'posts' );
				} catch( SwiftypeError $e ) {
					// DocumentType is gone now.
					break;
				}
			}

			$response = NULL;
			$retries = 0;
			$max_retries = 3;
			$retry_delay = 5;

			while ( is_null( $response ) ) {
				try {
					$response = $swiftype_plugin->client()->create_document_type( $engine_slug, 'posts' );
				} catch ( SwiftypeError $e ) {
					if ( $retries >= $max_retries ) {
						WP_CLI::log( $e );
						WP_CLI::error( "Could not create 'posts' DocumentType, aborting. Re-create your search engine to continue." );
					} else {
						$retries++;
						sleep( $retry_delay );
					}
				}
			}
		}


		if ( !$destructive ) {
			$offset = 0;
			$posts_deleted_in_batch = 0;
			$delete_batch_size = $this->integer_argument( $assoc_args['delete-batch-size'], 100 );

	  do {
		$end_count = $offset + $delete_batch_size;
				WP_CLI::log( "Deleting trashed posts from " . $offset . " to " . $end_count );
				$posts_deleted_in_batch = $swiftype_plugin->delete_batch_of_trashed_posts( $offset, $delete_batch_size );
				$offset += $posts_deleted_in_batch;
				WP_CLI::log( "Successfully deleted " . $posts_deleted_in_batch . " trashed posts." );

			} while ( $posts_deleted_in_batch != 0 );

			WP_CLI::log( "Deleted up to " . $offset . " posts" );
		}

		$offset = 0;
		$index_batch_size = $this->integer_argument( $assoc_args['index-batch-size'], 15 );

		do {
			WP_CLI::log( "Indexing " . $index_batch_size . " posts from offset " . $offset );
			$this->clear_caches();
			list($num_written, $posts_indexed_in_batch) = $swiftype_plugin->index_batch_of_posts( $offset, $index_batch_size );
			$offset += $posts_indexed_in_batch;
			WP_CLI::log( "Successfully indexed " . $posts_indexed_in_batch . " posts" );

		} while ( $posts_indexed_in_batch != 0 );

		WP_CLI::log( "Indexed " . $offset . " posts" );
	}

	private function integer_argument( $arg, $default ) {
		$value = $default;
		if ( intval( $arg ) > 0 ) {
			$value = intval( $arg );
		}

		return $value;
	}

	private function clear_caches() {
		global $wpdb, $wp_object_cache;
		$wpdb->queries = array();
		if ( !is_object( $wp_object_cache ) )
			return;
		$wp_object_cache->group_ops = array();
		$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();
		if ( method_exists( $wp_object_cache, '__remoteset' ) )
			$wp_object_cache->__remoteset();
	}
}

WP_CLI::add_command( 'swiftype', 'Swiftype_Command' );
