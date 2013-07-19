<?php

class SwiftypePluginVersionTest extends SwiftypeTestCase {
  // make sure the SWIFTYPE_VERSION constant matches the version in the plugin info header
  function test_versions() {
    $plugin_data = get_plugin_data(SWIFTYPE_PLUGIN_DIR . '/swiftype.php');
    $this->assertEquals(SWIFTYPE_VERSION, $plugin_data['Version']);
  }
}
