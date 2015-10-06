<?php

class SwiftypePluginFiltersTest extends SwiftypeTestCase {
  // Make sure we could get our plugin object
  function test_swiftype_plugin_object() {
    $swiftype_plugin = $this->globalPluginObject();
    $this->assertInstanceOf('SwiftypePlugin', $swiftype_plugin);
  }

  // Make sure swiftype menu item added to admin menu
  function test_admin_menu_added() {
    $swiftype_plugin = $this->globalPluginObject();
    $action = array($swiftype_plugin, 'swiftype_menu');
    $this->assertHasFilter('admin_menu', $action);
  }

  // Make sure swiftype's admin screen initialization action has been added
  function test_admin_screen_init_added() {
    $swiftype_plugin = $this->globalPluginObject();
    $action = array($swiftype_plugin, 'initialize_admin_screen');
    $this->assertHasFilter('admin_init', $action);
  }

  // Check to make sure enqueue_swiftype_assets hook is added for non-admin pages
  function test_assets_filter_added() {
    $swiftype_plugin = $this->globalPluginObject();
    $action = array($swiftype_plugin, 'enqueue_swiftype_assets');
    $this->assertHasFilter('wp_enqueue_scripts', $action);
  }

  // Check to make sure all post transition hooks are added everywhere
  function test_post_transition_filters_added() {
    $swiftype_plugin = $this->globalPluginObject();

    $action = array($swiftype_plugin, 'handle_future_to_publish');
    $this->assertHasFilter('future_to_publish', $action);

    $action = array($swiftype_plugin, 'handle_save_post');
    $this->assertHasFilter('save_post', $action);

    $action = array($swiftype_plugin, 'handle_transition_post_status');
    $this->assertHasFilter('transition_post_status', $action);

    $action = array($swiftype_plugin, 'delete_post');
    $this->assertHasFilter('trashed_post', $action);
  }
}
