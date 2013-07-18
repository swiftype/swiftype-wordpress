<?php

class CheckSwiftypePluginInfrastructureLogicTest extends WP_UnitTestCase {
  // Make sure testing framework works
  function test_tests() {
    $this->assertTrue(true);
  }

  // Make sure we could get our plugin object
  function test_swiftype_plugin_object() {
    $swiftype_plugin = $this->swiftypePluginObject();
    $this->assertInstanceOf('SwiftypePlugin', $swiftype_plugin);
  }

  // Make sure swiftype menu item added to admin menu
  function test_admin_menu_added() {
    $swiftype_plugin = $this->swiftypePluginObject();
    $action = array($swiftype_plugin, 'swiftype_menu');
    $this->assertHasFilter('admin_menu', $action);
  }

  // Make sure swiftype's admin screen initialization action has been added
  function test_admin_screen_init_added() {
    $swiftype_plugin = $this->swiftypePluginObject();
    $action = array($swiftype_plugin, 'initialize_admin_screen');
    $this->assertHasFilter('admin_init', $action);
  }

  //------------------------------------------------------------------------------------------------
  function assertHasFilter($tag, $function_to_check) {
    $this->assertGreaterThan(0, has_filter($tag, $function_to_check));
  }

  private function swiftypePluginObject() {
    global $wp_filter;
    $admin_init_filters = $wp_filter["admin_init"];
    foreach (array_keys($admin_init_filters) as $priority) {
      foreach (array_keys($admin_init_filters[$priority]) as $filter_key) {
        if (preg_match("/^\w{32}initialize_admin_screen$/", $filter_key)) {
          return $admin_init_filters[$priority][$filter_key]["function"][0];
        }
      }
    }
  }
}
