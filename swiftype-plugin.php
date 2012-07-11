<?php

  if( $_POST['action'] == 'swiftype_set_api_key' ) {
    $api_key = sanitize_text_field( $_POST['api_key'] );
    update_option( 'swiftype_api_key', $api_key );
    delete_option( 'swiftype_engine_slug' );
    delete_option( 'swiftype_num_indexed_documents' );
  } elseif( $_POST['action'] == 'swiftype_set_engine' ) {
    $engine_key = sanitize_text_field( $_POST['engine_key'] );
    $engine_name = sanitize_text_field( $_POST['engine_name'] );
    if( strlen( $engine_name ) > 0 ) {
      update_option( 'swiftype_create_engine', $engine_name );
    } else {
      update_option( 'swiftype_engine_key', $engine_key );
    }
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
      add_action( 'publish_post', array( $this, 'index_post' ) );
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

    public function set_sql_limit( $limit ) {
      if( is_search() && $this->search_successful )
        $limit = 'LIMIT 0, ' . count( $this->post_ids );
      return $limit;
    }

    public function clear_sql_search_clause( $search ) {
      if( is_search() && ! is_admin() && $this->search_successful ) {
        $search = '';
      }
      return $search;
    }

    public function get_posts_from_swiftype( $wp_query ) {
      if( is_search() ) {

        $query_string = $wp_query->query_vars['s'];
        $transient_key = 'stq-' . $query_string;

        delete_transient( $transient_key );

        if( false == ( $swiftype_post_ids = get_transient( $transient_key ) ) ) {
          $results = $this->client->search( $this->engine_slug, $this->document_type_slug, $query_string );

          // if $results is empty here then our API call failed and we want to fall back on default WP search.
          if( ! isset( $results ) ) {
            $this->search_successful = false;
            return;
          }

          $swiftype_post_ids = array();
          $records = $results['records']['posts'];

          foreach( $records as $record ) {
            $swiftype_post_ids[] = $record['external_id'];
          }
          set_transient( $transient_key, $swiftype_post_ids, 60 * 5 );
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

    public function check_api_authorized() {
      if( ! is_admin() )
        return;
      if( $this->api_authorized )
        return;

      if( ! $this->api_key || strlen( $this->api_key ) == 0 )
        $this->api_authorized = false;
      else
        $this->api_authorized = $this->client->authorized();

      update_option( 'swiftype_api_authorized', $this->api_authorized );
    }

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

      if( ! $this->engine_slug || strlen( $this->engine_slug ) == 0 ) {
        return;
      }

      $engines = $this->client->get_engines();
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
        } catch( SwiftypeError $e ) { }
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

    public function get_search_result_posts( $posts ) {
      if( ! is_search() || ! $this->engine_slug || strlen( $this->engine_slug ) == 0 ) {
        return $posts;
      }
      if( function_exists( 'is_main_query' ) && ! is_main_query() ) {
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

    public function async_refresh_num_indexed_documents() {
      $this->engine_slug = get_option( 'swiftype_engine_slug' );
      $this->engine_name = get_option( 'swiftype_engine_name' );
      $this->engine_key = get_option( 'swiftype_engine_key' );
      $document_type = $this->client->find_document_type( $this->engine_slug, $this->document_type_slug );
      $this->num_indexed_documents = $document_type['document_count'];
      update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
      header( 'Content-Type: application/json' );
      print( json_encode( array( 'num_indexed_documents' => $this->num_indexed_documents ) ) );
      die();
    }

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
        $this->client->delete_documents( $this->engine_slug, $this->document_type_slug, $document_ids );
      }
      header( "Content-Type: application/json" );
      print( "{}" );
      die();
    }

    public function index_post( $post_id ) {
      $post = get_post( $post_id );
      $document = $this->convert_post_to_document( $post );
      try {
        $this->client->create_or_update_document( $this->engine_slug, $this->document_type_slug, $document );
        $this->num_indexed_documents += 1;
        update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
      } catch( SwiftypeError $e ) {}
    }

    public function delete_post( $post_id ){
      try {
        $status = $this->client->delete_document( $this->engine_slug, $this->document_type_slug, $post_id );
        $this->num_indexed_documents -= 1;
        update_option( 'swiftype_num_indexed_documents', $this->num_indexed_documents );
      } catch( SwiftypeError $e ) {}
    }

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
      $document['fields'][0] = array( 'name' => 'object_type', 'type' => 'enum', 'value' => $post->post_type );
      $document['fields'][1] = array( 'name' => 'url', 'type' => 'enum', 'value' => get_permalink( $post->ID ) );
      $document['fields'][2] = array( 'name' => 'timestamp', 'type' => 'date', 'value' => $post->post_date_gmt );
      $document['fields'][3] = array( 'name' => 'title', 'type' => 'string', 'value' => $post->post_title );
      $document['fields'][4] = array( 'name' => 'body', 'type' => 'text', 'value' => html_entity_decode( strip_tags( $post->post_content ), ENT_COMPAT, "UTF-8" ) );
      $document['fields'][5] = array( 'name' => 'excerpt', 'type' => 'text', 'value' => html_entity_decode( strip_tags( $post->post_excerpt ), ENT_COMPAT, "UTF-8" ) );
      $document['fields'][6] = array( 'name' => 'author', 'type' => 'string', 'value' => array( $nickname, $name ) );
      $document['fields'][7] = array( 'name' => 'tags', 'type' => 'string', 'value' => $tag_strings );

      return $document;
    }

    public function swiftype_admin_page() {
      include( 'swiftype-admin-page.php' );
    }

    public function swiftype_menu() {
      add_menu_page( 'Swiftype Search', 'Swiftype Search', 'manage_options', __FILE__, array( $this, 'swiftype_admin_page' ), plugins_url( 'assets/swiftype_logo_menu.png', __FILE__ ) );
    }

    public function enqueue_admin_assets() {
      wp_enqueue_style( 'styles', plugins_url( 'assets/admin_styles.css', __FILE__ ) );
    }

    public function enqueue_swiftype_assets() {
      if( is_admin() )
        return;
      wp_enqueue_style( 'swiftype', plugins_url( 'assets/autocomplete.css', __FILE__ ) );
      wp_enqueue_script( 'swiftype', plugins_url( 'assets/install_swiftype.js', __FILE__ ), array( 'jquery' ) );
      wp_localize_script( 'swiftype', 'swiftypeParams', array( 'engineKey' => $this->engine_key ) );
    }

  }

  new SwiftypePlugin;

?>