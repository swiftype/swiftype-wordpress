<?php
  if( isset($_POST['action']) ) {
    if( $_POST['action'] == 'swiftype_set_api_key' ) {
      $api_key = sanitize_text_field( $_POST['api_key'] );
      update_option( 'swiftype_api_key', $api_key );
      delete_option( 'swiftype_engine_slug' );
      delete_option( 'swiftype_num_indexed_documents' );
    } elseif( $_POST['action'] == 'swiftype_create_engine' ) {
      $engine_name = sanitize_text_field( $_POST['engine_name'] );
      update_option( 'swiftype_create_engine', $engine_name );
    } elseif( $_POST['action'] == 'swiftype_use_existing_engine' ) {
      $engine_key = sanitize_text_field( $_POST['engine_key'] );
      update_option( 'swiftype_engine_key', $engine_key );
    } elseif( $_POST['action'] == 'swiftype_clear_config' ) {
      delete_option( 'swiftype_api_key' );
      delete_option( 'swiftype_api_authorized' );
      delete_option( 'swiftype_engine_slug' );
      delete_option( 'swiftype_engine_name' );
      delete_option( 'swiftype_engine_key' );
      delete_option( 'swiftype_engine_initialized' );
      delete_option( 'swiftype_create_engine' );
      delete_option( 'swiftype_num_indexed_documents' );
    }
  }

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
    private $per_page = 10;

    private $api_key = NULL;
    private $engine_slug = NULL;
    private $engine_name = NULL;
    private $engine_key = NULL;
    private $num_indexed_documents = 0;
    private $api_authorized = false;
    private $engine_initialized = false;

    private $post_ids = NULL;
    private $total_num_results = 0;
    private $search_successful = false;

    public function SwiftypePlugin() { $this->__construct(); }

    public function __construct() {

      add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_swiftype_assets' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
      add_action( 'admin_menu', array( $this, 'swiftype_menu' ) );

      add_action( 'pre_get_posts', array( $this, 'get_posts_from_swiftype' ) );
      add_filter( 'posts_search', array( $this, 'clear_sql_search_clause' ) );
      add_filter( 'post_limits', array( $this, 'set_sql_limit' ) );

      add_filter( 'the_posts', array( $this, 'get_search_result_posts' ) );
      add_action( 'save_post', array( $this, 'handle_save_post' ), 99, 1 );
      add_action( 'trashed_post', array( $this, 'delete_post' ) );

      add_action( 'wp_ajax_refresh_num_indexed_documents', array( $this, 'async_refresh_num_indexed_documents' ) );
      add_action( 'wp_ajax_index_batch_of_posts', array( $this, 'async_index_batch_of_posts' ) );
      add_action( 'wp_ajax_delete_all_trashed_posts', array( $this, 'async_delete_all_trashed_posts' ) );

      $this->api_key = get_option( 'swiftype_api_key' );
      $this->api_authorized = get_option( 'swiftype_api_authorized' );
      $this->client = new SwiftypeClient;
      $this->client->set_api_key( $this->api_key );
      $this->check_api_authorized();
      if( ! $this->api_authorized )
        return;

      $this->num_indexed_documents = get_option( 'swiftype_num_indexed_documents' );
      $this->engine_slug = get_option( 'swiftype_engine_slug' );
      $this->engine_name = get_option( 'swiftype_engine_name' );
      $this->engine_key = get_option( 'swiftype_engine_key' );
      $this->engine_initialized = get_option( 'swiftype_engine_initialized' );
      $this->check_engine_initialized();
      if( ! $this->engine_initialized )
        return;
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
      if( is_search() && ! is_admin() ) {

        $query_string = $wp_query->query_vars['s'];
        $category = $_GET['st-cat'];
        if ( ! empty( $category ) ) {
          $params = array( 'filters[posts][category]' => $category );
        } else {
          $params = array();
        }

        $params = apply_filters( 'swiftype_search_params', $params );

        try {
          $results = $this->client->search( $this->engine_slug, $this->document_type_slug, $query_string, $params );
        } catch( SwiftypeError $e ) {
          $this->search_successful = false;
        }

        if( ! isset( $results ) ) {
          $this->search_successful = false;
          return;
        }

        $swiftype_post_ids = array();
        $records = $results['records']['posts'];

        foreach( $records as $record ) {
          $swiftype_post_ids[] = $record['external_id'];
        }

        $page = get_query_var( 'paged' );
        $offset = 0;
        if( $page > 0 )
          $offset = ( $page - 1 ) * $this->per_page;

        $this->total_num_results = count( $swiftype_post_ids );
        $this->post_ids = array_slice( $swiftype_post_ids, $offset, $this->per_page );
        $wp_query->query_vars['post__in'] = $this->post_ids;
        $this->search_successful = true;
      }

    }

  /**
    * Check whether or not the Swiftype API client is authorized
    *
    * Retrieves search results from the Swiftype API based on the user-input text query.
    * We retrieve 100 results during the initial
    * request to Swiftype and cache all results, so pagination is done locally as well (i.e. requests for page 2, 3, etc.
    * do not hit the Swiftype API either). Called from the pre_get_posts action.
    *
    * @return null
    */
    private function check_api_authorized() {
      if( ! is_admin() )
        return;
      if( $this->api_authorized )
        return;

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
      if( ! is_admin() )
        return;
      if( $this->engine_initialized )
        return;

      // create the engine if they asked you too, using the name submitted.
      if( get_option( 'swiftype_create_engine' ) ) {
        $engine_name = get_option( 'swiftype_create_engine' );
        delete_option( 'swiftype_create_engine' );
        try {
          $engine = $this->client->create_engine( array( 'name' => $engine_name ) );
        } catch( SwiftypeError $e ) {
          return;
        }
        $this->engine_slug = $engine['slug'];
      }

      try {
        $engines = $this->client->get_engines();
      } catch( SwiftypeError $e ) {
        return;
      }
      foreach( $engines as $remote_engine ) {
        if( $remote_engine['key'] == $this->engine_key ) {
          $engine = $remote_engine;
          break;
        }
      }

      if( $engine ) {
        $this->engine_slug = $engine['slug'];
        $this->engine_name = $engine['name'];
        $this->engine_key = $engine['key'];
        try {
          $document_type = $this->client->find_document_type( $this->engine_slug, $this->document_type_slug );
        } catch( SwiftypeError $e ) {}
        if( ! $document_type ) {
          try {
            $document_type = $this->client->create_document_type( $this->engine_slug, $this->document_type_slug );
          } catch( SwiftypeError $e ) {
            return;
          }
        }
        if( $document_type ) {
          $this->engine_initialized = true;
          $this->num_indexed_documents = $document_type['document_count'];
        }
      }

      update_option( 'swiftype_engine_name', $this->engine_name );
      update_option( 'swiftype_engine_slug', $this->engine_slug );
      update_option( 'swiftype_engine_key', $this->engine_key );
      update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
      update_option( 'swiftype_engine_initialized', $this->engine_initialized );
    }


  /**
    * Get posts from the database in the order dicated by the Swiftype API
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
      $wp_query->max_num_pages = $this->total_num_results / $this->per_page;

      $lookup_table = array();
      foreach( $posts as $post ) {
        $lookup_table[ $post->ID ] = $post;
      }

      $ordered_posts = array();
      foreach( $this->post_ids as $pid ) {
        $ordered_posts[] = $lookup_table[ $pid ];
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
      $this->engine_slug = get_option( 'swiftype_engine_slug' );
      $this->engine_name = get_option( 'swiftype_engine_name' );
      $this->engine_key = get_option( 'swiftype_engine_key' );
      try {
        $document_type = $this->client->find_document_type( $this->engine_slug, $this->document_type_slug );
      } catch( SwiftypeError $e ) {
        http_response_code( 500 );
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
      $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
      $batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 10;
      $posts_query = array(
        'numberposts' => $batch_size,
        'offset' => $offset,
        'orderby' => 'id',
        'order' => 'ASC',
        'post_status' => 'publish',
        'post_type' => 'any'
      );
      $posts = get_posts( $posts_query );
      if( count( $posts ) > 0 ) {
        $documents = array();
        foreach( $posts as $post ) {
          $documents[] = $this->convert_post_to_document( $post );
        }
        try {
          $resp = $this->client->create_or_update_documents( $this->engine_slug, $this->document_type_slug, $documents );
        } catch( SwiftypeError $e ) {
          http_response_code( 500 );
          die();
        }
        $num_written = 0;
        foreach( $resp as $record ) {
          if( $record )
            $num_written += 1;
        }
      } else {
        $num_written = 0;
      }

      header( 'Content-Type: application/json' );
      print( json_encode( array( 'num_written' => $num_written ) ) );
      die();
    }

  /**
    * Delete all posts from the index that shouldn't be indexed in the search engine
    *
    * Sends a request to the Swiftype API to remove from the server-side search engine any posts that are not 'published'.
    * This method is called asynchronously via client-side Ajax when an admin clicks the "synchronize with swiftype" button
    * on the plugin Admin page.
    */
    public function async_delete_all_trashed_posts() {
      $document_ids = array();

      $posts_query = array(
        'numberposts' => -1,
        'offset' => 0,
        'orderby' => 'id',
        'order' => 'ASC',
        'post_status' => array(
          'trash',
          'draft',
          'pending',
          'future',
          'private'
        ),
        'post_type' => 'any'
      );

      $posts = get_posts( $posts_query );

      if( count( $posts ) > 0 ) {
        foreach( $posts as $post ) {
          $document_ids[] = $post->ID;
        }
      }
      if( count( $document_ids ) > 0 ) {
        try {
          $this->client->delete_documents( $this->engine_slug, $this->document_type_slug, $document_ids );
        } catch( SwiftypeError $e ) {
          http_response_code( 500 );
          die();
        }
      }
      header( "Content-Type: application/json" );
      print( "{}" );
      die();
    }

  /**
    * Indexes or deletes a post based on status.
    *
    * @param int $post_id The ID of the post to be indexed.
    */
    public function handle_save_post( $post_id ) {
      $post = get_post( $post_id );
      if( "publish" == $post->post_status ) {
        $this->index_post( $post_id );
      } else {
        $this->delete_post( $post_id );
      }
    }

  /**
    * Sends a request to the Swiftype API index a specific post in the server-side search engine.
    *
    * @param int $post_id The ID of the post to be indexed.
    */
    public function index_post( $post_id ) {
      $post = get_post( $post_id );
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
      $document['fields'][] = array( 'name' => 'title', 'type' => 'string', 'value' => $post->post_title );
      $document['fields'][] = array( 'name' => 'body', 'type' => 'text', 'value' => html_entity_decode( strip_tags( $post->post_content ), ENT_COMPAT, "UTF-8" ) );
      $document['fields'][] = array( 'name' => 'excerpt', 'type' => 'text', 'value' => html_entity_decode( strip_tags( $post->post_excerpt ), ENT_COMPAT, "UTF-8" ) );
      $document['fields'][] = array( 'name' => 'author', 'type' => 'string', 'value' => array( $nickname, $name ) );
      $document['fields'][] = array( 'name' => 'tags', 'type' => 'string', 'value' => $tag_strings );
      $document['fields'][] = array( 'name' => 'category', 'type' => 'enum', 'value' => wp_get_post_categories($post->ID) );

      return $document;
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
      add_menu_page( 'Swiftype Search', 'Swiftype Search', 'manage_options', __FILE__, array( $this, 'swiftype_admin_page' ), plugins_url( 'assets/swiftype_logo_menu.png', __FILE__ ) );
    }

  /**
    * Enqueues the styles used by the plugin's admin page.
    * This method is called by the admin_enqueue_scripts action.
    */
    public function enqueue_admin_assets() {
      wp_enqueue_style( 'styles', plugins_url( 'assets/admin_styles.css', __FILE__ ) );
    }

  /**
    * Enqueues the javascripts and styles to be used by the plugin on the primary website.
    * This method is called by the wp_enqueue_scripts action.
    */
    public function enqueue_swiftype_assets() {
      if( is_admin() )
        return;
      wp_enqueue_style( 'swiftype', plugins_url( 'assets/autocomplete.css', __FILE__ ) );
      wp_enqueue_script( 'swiftype', plugins_url( 'assets/install_swiftype.js', __FILE__ ), array( 'jquery' ) );
      wp_localize_script( 'swiftype', 'swiftypeParams', array( 'engineKey' => $this->engine_key ) );
    }

  }

  $swiftype_plugin = new SwiftypePlugin();

?>
