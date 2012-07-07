<?php

  if($_POST['action'] == 'set_api_key') {
    $api_key = $_POST['api_key'];
    update_option('swiftype_api_key', $api_key);
    delete_option('swiftype_engine_slug');
    delete_option('swiftype_num_indexed_documents');
  } else if($_POST['action'] == 'set_engine') {
    $engine_slug = $_POST['engine_slug'];
    $engine_name = $_POST['engine_name'];
    if(strlen($engine_name) > 0) {
      update_option('swiftype_create_engine',$engine_name);
    } else {
      update_option('swiftype_engine_slug', $engine_slug);
    }
  }

  class SwiftypeHttpResponse {
    public $status = NULL;
    public $body = NULL;
    function __construct($status, $body) {
      $this->status = $status;
      $this->body = $body;
    }
  }

  class SwiftypePlugin {

    private $client = NULL;
    private $api_key = NULL;
    private $engine_slug = NULL;
    private $engine_name = NULL;
    private $engine_key = NULL;
    private $document_type_slug = "posts";
    private $num_indexed_documents = 0;
    private $swiftype_posts_array = NULL;

    private $api_authorized = false;
    private $engine_initialized = false;

    public function SwiftypePlugin() { $this->__construct(); }

    public function __construct() {
      add_action('admin_notices', array($this, 'notify_curl_needed'));
      if(!function_exists('curl_init')) {
        return;
      }
      add_action("init", array($this, "include_swiftype_assets"));
      add_action('admin_menu', array($this, 'swiftype_menu'));
      add_filter('the_posts', array($this, 'get_search_result_posts'));
      add_filter('the_content', array($this, 'set_content'));
      add_action("publish_post", array($this, 'index_post'));
      add_action("trashed_post", array($this, 'delete_post'));
      add_action('wp_ajax_index_all', array($this, 'async_index_all'));
      add_action('wp_ajax_delete_trashed_posts', array($this, 'async_delete_trashed_posts'));
      add_action("admin_init", array($this, 'include_admin_styles'));
      $this->api_key = get_option('swiftype_api_key');
      $this->engine_slug = get_option('swiftype_engine_slug');
      $this->engine_name = get_option('swiftype_engine_name');
      $this->engine_key = get_option('swiftype_engine_key');
      $this->client = new SwiftypeClient;
      $this->client->set_api_key($this->api_key);
      $this->check_client_authorized();
      $this->initialize_engine();
    }

    public function notify_curl_needed() {
      if (!function_exists('curl_init')) {
        echo '<div id="message" class="error">You must update your <strong>PHP</strong> installation to include CURL in order to use the Swiftype Search plugin.</div>';
      }
    }

    public function include_swiftype_assets() {
      if(is_admin())
        return;
      wp_enqueue_style("swiftype", plugins_url("assets/autocomplete.css", __FILE__));
      wp_enqueue_script("swiftype", plugins_url("assets/install_swiftype.js", __FILE__), array("jquery"));
      wp_localize_script('swiftype', 'swiftypeParams', array('engineKey' => $this->engine_key));
    }

    public function check_client_authorized() {
      if(!is_admin())
        return;
      update_option('swiftype_api_authorized',$this->api_authorized);
      if(!$this->api_key || strlen($this->api_key) == 0) {
        return;
      }
      $this->api_authorized = $this->client->authorized();
      update_option('swiftype_api_authorized',$this->api_authorized);
    }

    public function initialize_engine() {
      if(!is_admin())
        return;
      update_option('swiftype_engine_initialized',$this->engine_initialized);
      update_option('swiftype_num_indexed_documents',$this->num_indexed_documents);

      // create the engine if they asked you too, using the name submitted.
      if(get_option('swiftype_create_engine')) {
        $engine_name = get_option('swiftype_create_engine');
        delete_option('swiftype_create_engine');
        $engine = $this->client->create_engine(array('name' => $engine_name));
        if($engine['error']) {
          $engine = $this->client->create_engine("Wordpress Search: " . get_option('swiftype_create_engine'));
        }
        $this->engine_slug = $engine['slug'];
      }

      if(!$this->engine_slug || strlen($this->engine_slug) == 0 || !$this->api_authorized) {
        return;
      }

      $engine = $this->client->find_engine($this->engine_slug);
      if($engine) {
        $document_type = $this->client->find_document_type($this->engine_slug, $this->document_type_slug);
        if(!$document_type) {
          $document_type = $this->client->create_document_type($this->engine_slug, $this->document_type_slug);
        }
        if($document_type) {
          $this->engine_initialized = true;
          $this->engine_slug = $engine['slug'];
          $this->engine_name = $engine['name'];
          $this->engine_key = $engine['key'];
          $this->num_indexed_documents = $document_type['document_count'];
        }
      }
      update_option('swiftype_engine_initialized',$this->engine_initialized);
      update_option('swiftype_engine_name',$this->engine_name);
      update_option('swiftype_engine_slug',$this->engine_slug);
      update_option('swiftype_engine_key',$this->engine_key);
      update_option('swiftype_num_indexed_documents',$this->num_indexed_documents);
    }

    public function get_search_result_posts($posts) {
      if(!is_search() || !$this->engine_slug || strlen($this->engine_slug) == 0) {
        return $posts;
      }
      if(function_exists('is_main_query') && !is_main_query()) {
        return $posts;
      }

      global $wp_query;
      $query_string = $wp_query->query_vars["s"];
      $response = $this->client->search($this->engine_slug, $this->document_type_slug, $query_string);
      $results = json_decode($response->body);

      $post_ids = array();
      $this->swiftype_records_array = array();
      $posts = array();

      // ummm, $results->records->posts is hardcoded to "posts" where it should really be $this->document_type_slug
      foreach($results->records->posts as $record) {
        $post_ids[] = $record->external_id;
      }
      $unordered_posts = get_posts(array('post_status' => 'publish', 'post_type' => 'any', 'include' => $post_ids));

      foreach($unordered_posts as $post) {
        $this->swiftype_records_array[$post->ID] = array('post' => $post);
      }
      foreach($post_ids as $post_id) {
        $posts[] = $this->swiftype_records_array[$post_id]['post'];
      }
      return $posts;
    }

    public function set_content($content) {
      if(!is_search()) {
        return $content;
      }
      // eventually implement highlighting here
      return $content;
    }

    public function include_admin_styles() {
      wp_enqueue_style("styles", plugins_url("assets/admin_styles.css", __FILE__));
    }

    public function async_index_all() {
      $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
      $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 10;
      $posts = get_posts(array('numberposts' => $batch_size, 'offset' => $offset, 'orderby' => 'id', 'order' => 'ASC', 'post_status' => 'publish', 'post_type' => 'any'));
      if(count($posts) > 0) {
        $documents = array();
        foreach($posts as $post) { $documents[] = $this->convert_post_to_document($post); }
        $num_created = $this->client->create_or_update_documents($this->engine_slug, $this->document_type_slug, $documents);
      }
      header("Content-Type: application/json");
      print("{}");
      die();
    }

    public function async_delete_trashed_posts() {
      $document_ids = array();
      $posts = get_posts(array('numberposts' => -1, 'offset' => 0, 'orderby' => 'id', 'order' => 'ASC', 'post_status' => array('trash','draft','pending','future','private'), 'post_type' => 'any'));
      if(count($posts) > 0) {
        foreach($posts as $post) { $document_ids[] = $post->ID; }
      }
      if(count($document_ids) > 0) {
        $this->client->delete_documents($this->engine_slug, $this->document_type_slug, $document_ids);
      }
      header("Content-Type: application/json");
      print("{}");
      die();
    }

    public function index_post($post_id) {
      $post = get_post($post_id);
      $document = $this->convert_post_to_document($post);
      $this->client->create_or_update_document($this->engine_slug, $this->document_type_slug, $document);
    }

    public function delete_post($post_id){
      $status = $this->client->delete_document($this->engine_slug, $this->document_type_slug, $post_id);
    }

    // inspired by the indextank wordpress plugin.
    private function convert_post_to_document($somepost) {
      global $post;
      $post = $somepost;

      // Google Analytics for wordpress 4.1.3 is buggy.
      // if the filter is not removed, it render the following error
      // 'Non-static method GA_Filter::the_content() cannot be called statically'
      remove_filter("the_content", array("GA_Filter", "the_content"), 99);

      $document = array();

      // handle text indexing, applying filters if necessary
      if ( get_option("it_apply_filters") ) {
        $the_content = apply_filters("the_content", $post->post_content);
      } else {
        $the_content = $post->post_content;
      }

      $nickname = get_the_author_meta('nickname', $post->post_author);
      $first_name = get_the_author_meta('first_name', $post->post_author);
      $last_name = get_the_author_meta('last_name', $post->post_author);
      $name = $first_name . " " . $last_name;

      $tags = get_the_tags($post->ID);
      $tag_strings = array();
      if(is_array($tags)) {
        foreach($tags as $tag) {
          $tag_strings[] = $tag->name;
        }
      }

      $document['external_id'] = $post->ID;
      $document['fields'] = array();
      $document['fields'][0] = array('name' => 'object_type', 'type' => 'enum', 'value' => $post->post_type);
      $document['fields'][1] = array('name' => 'url', 'type' => 'enum', 'value' => get_permalink($post->ID));
      $document['fields'][2] = array('name' => 'timestamp', 'type' => 'date', 'value' => $post->post_date_gmt);
      $document['fields'][3] = array('name' => 'title', 'type' => 'string', 'value' => $post->post_title);
      $document['fields'][4] = array('name' => 'body', 'type' => 'text', 'value' => html_entity_decode(strip_tags($the_content), ENT_COMPAT, "UTF-8"));
      $document['fields'][5] = array('name' => 'excerpt', 'type' => 'text', 'value' => html_entity_decode(strip_tags($post->post_excerpt), ENT_COMPAT, "UTF-8"));
      $document['fields'][6] = array('name' => 'author', 'type' => 'string', 'value' => array($nickname, $name));
      $document['fields'][7] = array('name' => 'tags', 'type' => 'string', 'value' => $tag_strings);

      return $document;
    }

    function swiftype_admin_page() { include('swiftype_admin_page.php'); }
    function swiftype_menu() { add_menu_page( 'Swiftype Search', 'Swiftype Search', 'manage_options', __FILE__, array($this, 'swiftype_admin_page'), plugins_url('assets/swiftype_logo_menu.png', __FILE__)); }

  }

  new SwiftypePlugin;

?>