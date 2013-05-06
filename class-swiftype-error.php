<?php

/**
	* The SwiftypeError Exception class
	*
	* An instance of this class is thrown any time the Swiftype Client encounters an error
	* making calls to the remote service.
	*
	* @author  Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>
	*
	* @since 1.0
	*
	*/

class SwiftypeError extends Exception {
	public function isInvalidAuthentication() {
		return $this->code == 401;
	}
}
