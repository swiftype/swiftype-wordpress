<?php

class SwiftypePluginApiAuthTest extends SwiftypeTestCase {
  public $api_key;
  public $plugin;

  //------------------------------------------------------------------------------------------------
  function setUp() {
    parent::setUp();

    // This is an admin page
    if ( ! defined( 'WP_ADMIN' ) ) {
      define( 'WP_ADMIN', true );
    }

    // Create plugin for testing
    $this->plugin = new SwiftypePlugin();

    // Reset api_authorized option
    update_option('swiftype_api_authorized', false);
    $this->assertFalse(get_option('swiftype_api_authorized'));
  }

  //------------------------------------------------------------------------------------------------
  // Engine authorization check test for valid a valid engine key
  function test_check_api_authorized_success() {
    // Set API key
    update_option('swiftype_api_key', $this->valid_api_key());
    $this->plugin->initialize_api_client();

    // Check authorization
    $this->plugin->check_api_authorized();
    $this->assertTrue(get_option('swiftype_api_authorized'));
    $this->assertNoErrors();
  }

  //------------------------------------------------------------------------------------------------
  // Engine authorization check test for valid an invalid engine key
  function test_check_api_authorized_failure() {
    // Set API key
    update_option('swiftype_api_key', "foobar");
    $this->plugin->initialize_api_client();

    // Check authorization
    $this->plugin->check_api_authorized();
    $this->assertFalse(get_option('swiftype_api_authorized'));
    $this->assertNoErrors();
  }
}
