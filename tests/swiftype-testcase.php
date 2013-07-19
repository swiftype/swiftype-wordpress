<?php

class SwiftypeTestCase extends WP_UnitTestCase {
  // Checks if given callback is registered as a filter for a given tag in wordpress
  protected function assertHasFilter($tag, $function_to_check) {
    $this->assertGreaterThan(0, has_filter($tag, $function_to_check));
  }

  //------------------------------------------------------------------------------------------------
  // Returns swiftype plugin object for current request
  // FIXME: Maybe we should just change the plugin file to use a global variable?
  protected function swiftypePluginObject() {
    return $GLOBALS['swiftype-wordpress'];
  }
}