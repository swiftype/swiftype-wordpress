<?php

/**
	* The Swiftype Search Wordpress Plugin
	*
	* This class encapsulates all of the Swiftype Search plugin's functionality.
	*
	* @author  Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>
	*
	* @since 1.0
	*
	*/

	class SwiftypePlugin {

		private $client = NULL;
		private $document_type_slug = 'posts';

		private $api_key = NULL;
		private $engine_slug = NULL;
		private $engine_name = NULL;
		private $engine_key = NULL;
		private $num_indexed_documents = 0;
		private $api_authorized = false;
		private $engine_initialized = false;

		private $post_ids = NULL;
		private $total_result_count = 0;
		private $num_pages = 0;
		private $per_page = 0;
		private $search_successful = false;
		private $error = NULL;
		private $results = NULL;

		private $max_retries = 5;
		private $retry_delay = 2;

		public function __construct() {
			$this->api_authorized = get_option( 'swiftype_api_authorized' );

			add_action( 'admin_menu', array( $this, 'swiftype_menu' ) );
			add_action( 'admin_init', array( $this, 'initialize_admin_screen' ) );
			add_action( 'future_to_publish' , array( $this, 'handle_future_to_publish' ) );

			if ( ! is_admin() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_swiftype_assets' ) );
				add_action( 'pre_get_posts', array( $this, 'get_posts_from_swiftype' ) );
				add_filter( 'posts_search', array( $this, 'clear_sql_search_clause' ) );
				add_filter( 'post_limits', array( $this, 'set_sql_limit' ) );
				add_filter( 'the_posts', array( $this, 'get_search_result_posts' ) );

				$this->initialize_api_client();
			}
		}

		/**
		 * Initialize swiftype API client
		 */
		public function initialize_api_client() {
			$this->api_key = get_option( 'swiftype_api_key' );
			$this->engine_slug = get_option( 'swiftype_engine_slug' );
			$this->engine_key = get_option( 'swiftype_engine_key' );

			$this->client = new SwiftypeClient();
			$this->client->set_api_key( $this->api_key );
		}

		/**
			* Initialize the Swiftype Search plugin's admin screen
			*
			* Performs most of the mechanical work of the admin settings screen. Gets/Sets Option values based on user input, and binds
			* functions ot different actions that are triggered in the admin area.
			*/
		public function initialize_admin_screen() {
			if ( current_user_can( 'manage_options' ) ) {
				// these methods make the Swiftype Plugin admin page work
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
				add_action( 'admin_menu', array( $this, 'swiftype_menu' ) );
				add_action( 'wp_ajax_refresh_num_indexed_documents', array( $this, 'async_refresh_num_indexed_documents' ) );
				add_action( 'wp_ajax_index_batch_of_posts', array( $this, 'async_index_batch_of_posts' ) );
				add_action( 'wp_ajax_delete_batch_of_trashed_posts', array( $this, 'async_delete_batch_of_trashed_posts' ) );
				add_action( 'admin_notices', array( $this, 'error_notice' ) );


				if( isset( $_POST['action'] ) ) {
					if( $_POST['action'] == 'swiftype_set_api_key' ) {
						check_admin_referer( 'swiftype-nonce' );
						$api_key = sanitize_text_field( $_POST['api_key'] );
						update_option( 'swiftype_api_key', $api_key );
						delete_option( 'swiftype_engine_slug' );
						delete_option( 'swiftype_num_indexed_documents' );
					} elseif( $_POST['action'] == 'swiftype_create_engine' ) {
						check_admin_referer( 'swiftype-nonce' );
						$engine_name = sanitize_text_field( $_POST['engine_name'] );
						update_option( 'swiftype_create_engine', $engine_name );
					} elseif( $_POST['action'] == 'swiftype_clear_config' ) {
						check_admin_referer( 'swiftype-nonce' );
						$this->clear_config();
					}
				}
			}

			if ( current_user_can( 'edit_posts' ) ) {
				// hooks for sending post updates to the Swiftype API
				add_action( 'save_post', array( $this, 'handle_save_post' ), 99, 1 );
				add_action( 'transition_post_status' , array( $this, 'handle_transition_post_status' ), 99, 3 );
				add_action( 'trashed_post', array( $this, 'delete_post' ) );

				$this->initialize_api_client();
				$this->check_api_authorized();
				if( ! $this->api_authorized )
					return;

				$this->num_indexed_documents = get_option( 'swiftype_num_indexed_documents' );
				$this->engine_slug = get_option( 'swiftype_engine_slug' );
				$this->engine_name = get_option( 'swiftype_engine_name' );
				$this->engine_key = get_option( 'swiftype_engine_key' );
				$this->engine_initialized = get_option( 'swiftype_engine_initialized' );
				$this->error = $this->check_engine_initialized();
				if( ! $this->engine_initialized )
					return;
			}
		}

	/**
	 * Display an error message in the dashboard if there was an error in the plugin
	 */
		public function error_notice() {
			if( ! is_admin() )
				return;
		  if( isset( $this->error ) && ! empty( $this->error ) ) {
		  	echo '<div class="error"><p>' . $this->error . '</p></div>';
		  }
		}

	/**
		* Get search results from the Swiftype API
		*
		* Retrieves search results from the Swiftype API based on the user-input text query.
		* We retrieve 100 results during the initial
		* request to Swiftype and cache all results, so pagination is done locally as well (i.e. requests for page 2, 3, etc.
		* do not hit the Swiftype API either). Call from the pre_get_posts action.
		*
		* @uses apply_filters() Calls 'swiftype_search_params' for the search parameters
		* @param WP_Query $wp_query The query for this request.
		*/
		public function get_posts_from_swiftype( $wp_query ) {
			$this->search_successful = false;
			if( function_exists( 'is_main_query' ) && ! $wp_query->is_main_query() ) {
				return;
			}
			if( is_search() && ! is_admin() && $this->engine_slug && strlen( $this->engine_slug ) > 0) {
				$query_string = apply_filters( 'swiftype_search_query_string', stripslashes( get_search_query( false ) ) );
				$page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

				$params = array( 'page' => $page );
				if ( isset( $_GET['st-cat'] ) && ! empty( $_GET['st-cat'] ) ) {
					$params['filters[posts][category]'] = sanitize_text_field( $_GET['st-cat'] );
				}

				if ( isset( $_GET['st-facet-field'] ) && isset( $_GET['st-facet-term'] ) ) {
					$params['filters[posts][' . $_GET['st-facet-field'] . ']'] = $_GET['st-facet-term'];
				}

				$params = apply_filters( 'swiftype_search_params', $params );

				try {
					$this->results = $this->client->search( $this->engine_slug, $this->document_type_slug, $query_string, $params );
				} catch( SwiftypeError $e ) {
					$this->results = NULL;
					$this->search_successful = false;
				}

				if( ! isset( $this->results ) ) {
					$this->search_successful = false;
					return;
				}

				$this->post_ids = array();
				$records = $this->results['records']['posts'];

				foreach( $records as $record ) {
					$this->post_ids[] = $record['external_id'];
				}

				$result_info = $this->results['info']['posts'];
				$this->per_page = $result_info['per_page'];

				$this->total_result_count = $result_info['total_result_count'];
				$this->num_pages = $result_info['num_pages'];
				set_query_var( 'post__in', $this->post_ids);
				$this->search_successful = true;

				add_filter( 'post_class', array( $this, 'swiftype_post_class' ) );
			}

		}

	/**
		* Check whether or not the Swiftype API client is authorized
		*
		* @return null
		*/
		public function check_api_authorized() {
			if( ! is_admin() )
				return;
			if( $this->api_authorized )
				return;

			// If we have the key, try to ask API client for authorization
			if( $this->api_key && strlen( $this->api_key ) > 0 ) {
				try {
					$this->api_authorized = $this->client->authorized();
				} catch( SwiftypeError $e ) {
					$this->api_authorized = false;
				}
			} else {
				$this->api_authorized = false;
			}

			update_option( 'swiftype_api_authorized', $this->api_authorized );
		}

	/**
		* Check whether or not the search engine for this Wordpress install is initialized
		*
		* Performs a series of calls to the Swiftype API to ensure that the API Key and engine names the user has provided
		* during plugin setup have been properly initialized on the server. This call will be made every time the user
		* loads the admin page until the engine is correctly initialized. Once the engine is properly initialized
		* an instance variable is set and future calls are short-circuited.
		*/
		public function check_engine_initialized() {
			$engine = null;

			if( ! is_admin() )
				return;
			if( $this->engine_initialized )
				return;

			$engine_name = get_option( 'swiftype_create_engine' );

			if ( $engine_name == false ) {
				return;
			}

			try {
				$this->initialize_engine( $engine_name );
			} catch ( SwiftypeError $e ) {
				$error_message = json_decode( $e->getMessage() );
				return "<b>There was an error creating your search engine on the Swiftype servers.</b> There error message was: " . $error_message->error;
			}
		}

	/**
		* Initialize the search engine for this WordPress installation.
		*/
		public function initialize_engine( $engine_name ) {
			$engine = $this->client->create_engine( array( 'name' => $engine_name ) );

			$this->engine_slug = $engine['slug'];
			$this->engine_name = $engine['name'];
			$this->engine_key = $engine['key'];

			$document_type = $this->client->create_document_type( $this->engine_slug, $this->document_type_slug );

			if( $document_type ) {
				$this->engine_initialized = true;
				$this->num_indexed_documents = $document_type['document_count'];
			}

			delete_option( 'swiftype_create_engine' );
			update_option( 'swiftype_engine_name', $this->engine_name );
			update_option( 'swiftype_engine_slug', $this->engine_slug );
			update_option( 'swiftype_engine_key', $this->engine_key );
			update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
			update_option( 'swiftype_engine_initialized', $this->engine_initialized );
		}


	/**
		* Get posts from the database in the order dictated by the Swiftype API
		*
		* Apply the correct ordering to the posts retrieved in the main query, based on results from the Swiftype API.
		* Called by the the_posts filter.
		*
		* @param array $posts the posts ordered as they are when they are originally retrieved from the database
		*/
		public function get_search_result_posts( $posts ) {
			if( ! is_search() || ! $this->engine_slug || strlen( $this->engine_slug ) == 0 ) {
				return $posts;
			}
			if( ! $this->search_successful ) {
				return $posts;
			}
			global $wp_query;
			$wp_query->max_num_pages = $this->num_pages;
			$wp_query->found_posts = $this->total_result_count;

			$lookup_table = array();
			foreach( $posts as $post ) {
				$lookup_table[ $post->ID ] = $post;
			}

			$ordered_posts = array();
			foreach( $this->post_ids as $pid ) {
				if ( isset( $lookup_table[ $pid ] ) ) {
					$ordered_posts[] = $lookup_table[ $pid ];
				}
			}

			return $ordered_posts;
		}

	/**
		* Set the LIMIT clause
		*
		* Sets the proper limit for the query to get posts, based on the number of search results retrieved by calls to the Swiftype API.
		* Called from the post_limits filter.
		*
		* @param string $limit The existing LIMIT clause as it is formed before making it to this filter.
		* @return string The modified LIMIT clause
		*/
		public function set_sql_limit( $limit ) {
			if( is_search() && $this->search_successful )
				$limit = 'LIMIT 0, ' . count( $this->post_ids );
			return $limit;
		}

	/**
		* Clear the LIKE clause
		*
		* Clears the existing LIKE (search) clause that Wordpress sets when performing a search, since the searching aspects of the query
		* are handled by the Swiftype client. Called from the posts_search filter.
		*
		* @param string $search The existing LIKE clause as it is formed before making it to this filter.
		* @return string The modified LIKE clause
		*/
		public function clear_sql_search_clause( $search ) {
			if( is_search() && ! is_admin() && $this->search_successful ) {
				$search = '';
			}
			return $search;
		}

	/**
		* Refresh the num_indexed_documents instance variable
		*
		* Resets the num_indexed_documents instance variable (and option) by calling the Swiftype API and getting the official
		* number of documents indexed in the search engine. This method is called asynchronously via client-side Ajax any time
		* an admin clicks the "synchronize with swiftype" button on the plugin Admin page.
		*/
		public function async_refresh_num_indexed_documents() {
			check_ajax_referer( 'swiftype-ajax-nonce' );
			$this->engine_slug = get_option( 'swiftype_engine_slug' );
			$this->engine_name = get_option( 'swiftype_engine_name' );
			$this->engine_key = get_option( 'swiftype_engine_key' );
			try {
				$document_type = $this->client->find_document_type( $this->engine_slug, $this->document_type_slug );
			} catch( SwiftypeError $e ) {
				header('HTTP/1.1 500 Internal Server Error');
				die();
			}
			$this->num_indexed_documents = $document_type['document_count'];
			update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
			header( 'Content-Type: application/json' );
			print( json_encode( array( 'num_indexed_documents' => $this->num_indexed_documents ) ) );
			die();
		}

	/**
		* Index a batch of posts
		*
		* Sends a batch of posts to the Swiftype API via the client in order to index them in the server-side search engine.
		* This method is called asynchronously via client-side Ajax when an admin clicks the "synchronize with swiftype" button
		* on the plugin Admin page.
		*/
		public function async_index_batch_of_posts() {
			check_ajax_referer( 'swiftype-ajax-nonce' );
			$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
			$batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 10;

			try {
				list( $num_written, $total_posts ) = $this->index_batch_of_posts( $offset, $batch_size );

				header( 'Content-Type: application/json' );
				print( json_encode( array( 'num_written' => $num_written, 'total' => $total_posts ) ) );
				die();

			} catch ( SwiftypeError $e ) {
				header( 'HTTP/1.1 500 Internal Server Error' );
				print( "Error in Create or Update Documents. " );
				print( "Offset: " . $offset . " " );
				print( "Batch Size: " . $batch_size . " " );
				print( "Retries: " . $retries . " " );
				print_r( $e );
				die();
			}

		}


		public function index_batch_of_posts( $offset, $batch_size ) {
			$posts_query = array(
				'numberposts' => $batch_size,
				'offset' => $offset,
				'orderby' => 'id',
				'order' => 'ASC',
				'post_status' => 'publish',
				'post_type' => $this->allowed_post_types()
			);
			$posts = get_posts( $posts_query );
			$total_posts = count( $posts );
			$retries = 0;
			$resp = NULL;
			$num_written = 0;

			if( $total_posts > 0 ) {
				$documents = array();
				foreach( $posts as $post ) {
					if( $this->should_index_post( $post ) ) {
						$document = $this->convert_post_to_document( $post );

						if ( $document ) {
							$documents[] = $document;
						}
					}
				}
				if( count( $documents ) > 0 ) {
					while( is_null( $resp ) ) {
						try {
							$resp = $this->client->create_or_update_documents( $this->engine_slug, $this->document_type_slug, $documents );
						} catch( SwiftypeError $e ) {
							if( $retries >= $this->max_retries ) {
								throw $e;
							} else {
								$retries++;
								sleep( $this->retry_delay );
							}
						}
					}

					foreach( $resp as $record ) {
						if( $record ) {
							$num_written += 1;
						}
					}
				}
			}

			return array( $num_written, $total_posts );
		}

	/**
		* Delete all posts from the index that shouldn't be indexed in the search engine
		*
		* Sends a request to the Swiftype API to remove from the server-side search engine any posts that are not 'published'.
		* This method is called asynchronously via client-side Ajax when an admin clicks the "synchronize with swiftype" button
		* on the plugin Admin page.
		*/
		public function async_delete_batch_of_trashed_posts() {
			check_ajax_referer( 'swiftype-ajax-nonce' );

			$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
			$batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 10;
			$total_posts = 0;

			try {
				$total_posts = $this->delete_batch_of_trashed_posts( $offset, $batch_size );
			} catch ( SwiftypeError $e ) {
				header( 'HTTP/1.1 500 Internal Server Error' );
				print( 'Error in Delete all Trashed Posts.' );
				print_r( $e );
				die();
			}

			header( "Content-Type: application/json" );
			print( json_encode( array( 'total' => $total_posts ) ) );
			die();
		}


		public function delete_batch_of_trashed_posts( $offset, $batch_size ) {
			$document_ids = array();

			$posts_query = array(
				'numberposts' => $batch_size,
				'offset' => $offset,
				'orderby' => 'id',
				'order' => 'ASC',
				'post_status' => array_diff( get_post_stati(), array( 'publish' ) ),
				'post_type' => $this->allowed_post_types(),
				'fields' => 'ids'
			);

			$posts = get_posts( $posts_query );
			$total_posts = count( $posts );
			$retries = 0;
			$resp = NULL;

			if( $total_posts ) {
				foreach( $posts as $post_id ) {
					$document_ids[] = $post_id;
				}
			}
			if( count( $document_ids ) > 0 ) {
				while( is_null( $resp ) ) {
					try {
						$resp = $this->client->delete_documents( $this->engine_slug, $this->document_type_slug, $document_ids );
					} catch( SwiftypeError $e ) {
						if( $retries >= $this->max_retries ) {
							throw $e;
						} else {
							$retries++;
							sleep( $this->retry_delay );
						}
					}
				}
			}

			return $total_posts;

		}

	/**
		* Deletes a post from Swiftype's search index any time the post's status transitions from 'publish' to anything else.
		*
		* @param int $new_status The new status of the post
		* @param int $old_status The old status of the post
		* @param int $post The post
		*/
		public function handle_transition_post_status( $new_status, $old_status, $post ) {
			if ( "publish" == $old_status && "publish" != $new_status ) {
				$this->delete_post( $post->ID );
			}
		}

	/**
		* Index a post when it transitions from the 'future' state to the 'publish' state
		*
		* @param int $post The post
		*/
		public function handle_future_to_publish( $post ) {
			if( "publish" == $post->post_status ) {
				$this->index_post( $post->ID );
			}
		}

	/**
		* Sends a post to Swiftype for indexing as long as the status of the post is 'publish'
		*
		* @param int $post_id The ID of the post to be indexed.
		*/
		public function handle_save_post( $post_id ) {
			$post = get_post( $post_id );
			if( "publish" == $post->post_status ) {
				$this->index_post( $post_id );
			}
		}

	/**
		* Sends a request to the Swiftype API index a specific post in the server-side search engine.
		*
		* @param int $post_id The ID of the post to be indexed.
		*/
		public function index_post( $post_id ) {
			$post = get_post( $post_id );
			if ( ! $this->should_index_post( $post ) ) {
				return;
			}

			$document = $this->convert_post_to_document( $post );
			try {
				$this->client->create_or_update_document( $this->engine_slug, $this->document_type_slug, $document );
				$this->num_indexed_documents += 1;
				update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
			} catch( SwiftypeError $e ) {
				return;
			}
		}

	/**
		* Sends a request to the Swiftype API remove a specific post from the server-side search engine.
		*
		* @param int $post_id The ID of the post to be deleted.
		*/
		public function delete_post( $post_id ){
			try {
				$this->client->delete_document( $this->engine_slug, $this->document_type_slug, $post_id );
				$this->num_indexed_documents -= 1;
				update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
			} catch( SwiftypeError $e ) {
				return;
			}
		}

	/**
		* Converts a post into an array that can be sent to the Swiftype API to be indexed in the server-side engine.
		*
		* @param object $somepost The post that is to be converted
		* @return array An array representing the post that is suitable for sending to the Swiftype API
		*/
		private function convert_post_to_document( $somepost ) {
			global $post;
			$post = $somepost;

			$nickname = get_the_author_meta( 'nickname', $post->post_author );
			$first_name = get_the_author_meta( 'first_name', $post->post_author );
			$last_name = get_the_author_meta( 'last_name', $post->post_author );
			$name = $first_name . " " . $last_name;

			$tags = get_the_tags( $post->ID );
			$tag_strings = array();
			if( is_array( $tags ) ) {
				foreach( $tags as $tag ) {
					$tag_strings[] = $tag->name;
				}
			}

			$document = array();
			$document['external_id'] = $post->ID;
			$document['fields'] = array();
			$document['fields'][] = array( 'name' => 'object_type', 'type' => 'enum', 'value' => $post->post_type );
			$document['fields'][] = array( 'name' => 'url', 'type' => 'enum', 'value' => get_permalink( $post->ID ) );
			$document['fields'][] = array( 'name' => 'timestamp', 'type' => 'date', 'value' => $post->post_date_gmt );
			$document['fields'][] = array( 'name' => 'title', 'type' => 'string', 'value' => html_entity_decode( strip_tags( $post->post_title ), ENT_QUOTES, "UTF-8" ) );
			$document['fields'][] = array( 'name' => 'body', 'type' => 'text', 'value' => html_entity_decode( strip_tags( $this->strip_shortcodes_retain_contents( $post->post_content ) ), ENT_QUOTES, "UTF-8" ) );
			$document['fields'][] = array( 'name' => 'excerpt', 'type' => 'text', 'value' => html_entity_decode( strip_tags( $post->post_excerpt ), ENT_QUOTES, "UTF-8" ) );
			$document['fields'][] = array( 'name' => 'author', 'type' => 'string', 'value' => array( $nickname, $name ) );
			$document['fields'][] = array( 'name' => 'tags', 'type' => 'string', 'value' => $tag_strings );
			$document['fields'][] = array( 'name' => 'category', 'type' => 'enum', 'value' => wp_get_post_categories( $post->ID ) );

			$image = NULL;

			if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post->ID ) ) {
				// NOTE: returns false on failure
				$image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
			}

			if ( $image ) {
				$document['fields'][] = array( 'name' => 'image', 'type' => 'enum', 'value' => $image );
			} else {
				$document['fields'][] = array( 'name' => 'image', 'type' => 'enum', 'value' => NULL );
			}

			$document = apply_filters( "swiftype_document_builder", $document, $post );

			return $document;
		}

	/**
		* Strip shortcodes, retaining any content inside of them.
		* @param string $content The string to strip shortcodes from
		* @return string A string with the shortcodes removed
		*/
		private function strip_shortcodes_retain_contents( $content ) {
			global $shortcode_tags;

			if ( empty($shortcode_tags) || !is_array($shortcode_tags) )
				return $content;

			$pattern = get_shortcode_regex();

			# Replace the short code with its content (the 5th capture group) surrounded by spaces
			return preg_replace("/$pattern/s", ' $5 ', $content);
		}

	/**
		* Includes the Swiftype Search plugin's admin page
		*/
		public function swiftype_admin_page() {
			include( 'swiftype-admin-page.php' );
		}

	/**
		* Creates a menu in the Wordpress admin for the Swiftype Search plugin
		* This method is called by the admin_menu action.
		*/
		public function swiftype_menu() {
			add_menu_page( 'Swiftype Search', 'Swiftype Search', 'manage_options', "swiftype", array( $this, 'swiftype_admin_page' ), plugins_url( 'assets/swiftype_logo_menu.png', __FILE__ ) );
		}

	/**
		* Enqueues the styles used by the plugin's admin page.
		* This method is called by the admin_enqueue_scripts action.
		*/
		public function enqueue_admin_assets($hook) {
			if( 'toplevel_page_swiftype' != $hook )
				return;
			wp_enqueue_style( 'admin_styles', plugins_url( 'assets/admin_styles.css', __FILE__ ) );
		}

	/**
		* Enqueues the javascripts and styles to be used by the plugin on the primary website.
		* This method is called by the wp_enqueue_scripts action.
		*/
		public function enqueue_swiftype_assets() {
			if ( is_admin() )
				return;
			wp_enqueue_style( 'swiftype', plugins_url( 'assets/autocomplete.css', __FILE__ ) );
			wp_enqueue_script( 'swiftype', plugins_url( 'assets/install_swiftype.min.js', __FILE__ ) );
			wp_localize_script( 'swiftype', 'swiftypeParams', array( 'engineKey' => $this->engine_key ) );
		}

	/**
		* Add a Swiftype-specific post class to the list of post classes.
		*/
		public function swiftype_post_class( $classes ) {
			global $post;

			$classes[] = 'swiftype-result';
			$classes[] = 'swiftype-result-' . $post->ID;

			return $classes;
		}

	/**
		* Return the SwiftypeClient instance
		*/
		public function client() {
			return $this->client;
		}

	/**
		* Delete all stored options for the Swiftype plugin.
		*/
		public function clear_config() {
			delete_option( 'swiftype_create_engine' );
			delete_option( 'swiftype_api_key' );
			delete_option( 'swiftype_api_authorized' );
			delete_option( 'swiftype_engine_slug' );
			delete_option( 'swiftype_engine_name' );
			delete_option( 'swiftype_engine_key' );
			delete_option( 'swiftype_engine_initialized' );
			delete_option( 'swiftype_create_engine' );
			delete_option( 'swiftype_num_indexed_documents' );
		}

	/**
		* Return the raw Swiftype results array after a search is performed.
		*/
		public function results() {
			return $this->results;
		}

	/**
		* Return the total number of results after a search is performed.
		*/
		public function get_total_result_count() {
			return $this->total_result_count;
		}

	/**
		* Determines if a post should be indexed.
		*/
		private function should_index_post( $post ) {
		  return ( in_array( $post->post_type, $this->allowed_post_types() ) && ! empty( $post->post_title ) );
		}

		private function allowed_post_types() {
			$allowed_post_types = array( 'post', 'page' );
			if ( function_exists( 'get_post_types' ) ) {
				$allowed_post_types = array_merge( get_post_types( array( 'exclude_from_search' => '0' ) ), get_post_types( array( 'exclude_from_search' => false ) ) );
			}
			return $allowed_post_types;
		}

	}
