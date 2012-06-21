<?php

  $api_authorized = get_option('swiftype_api_authorized');
  $engine_initialized = get_option('swiftype_engine_initialized');

	if($api_authorized) {
    if($engine_initialized) {
      include('swiftype_controls.php');
    } else {
      include('swiftype_choose_engine.php');
    }
  } else {
    include('swiftype_authorize.php');
  }

?>