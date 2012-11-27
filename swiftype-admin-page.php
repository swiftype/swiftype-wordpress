<?php

	$api_authorized = get_option( 'swiftype_api_authorized' );
	$engine_initialized = get_option( 'swiftype_engine_initialized' );

	if( $api_authorized ) {
		if( $engine_initialized ) {
			include( 'swiftype-controls.php' );
		} else {
			include( 'swiftype-choose-engine.php' );
		}
	} else {
		include( 'swiftype-authorize.php' );
	}
