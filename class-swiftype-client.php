<?php
/**
	* Client for the Swiftype API
	*
	* This class encapsulates all remote communication via HTTP with the Swiftype API.
	* If the client encounters any errors, it throws an instance of the SwiftypeError exception class.
	*
	* @author  Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>
	*
	* @since 1.0
	*
	*/

/**
	* The Swiftype API Client class
	*/
class SwiftypeClient {

/**
	* The URL endpoint that is the basis for all calls to the Swiftype API
	*/
	private $endpoint = 'http://api.swiftype.com/api/v1/';

/**
	* The Swiftype API Key that this client should use for authenticating its calls
	*/
	private $api_key = NULL;

/**
	* Blank constructor for the class
	*/
	public function __construct() { }

/**
	* Retrieve the client's API Key
	*
	* @return string The client's api key
	*/
	public function get_api_key() {
		return $this->api_key;
	}

/**
	* Set the client's API Key
	*
	* @param string $api_key The api key that the client should use
	*/
	public function set_api_key($api_key) {
		$this->api_key = $api_key;
	}

/**
	* Retrieve an array of engines
	*
	* @return array An array of the engines
	*/
	public function get_engines() {
		$url = $this->endpoint . 'engines.json';
		$params = array( 'per_page' => 100 );
		$response = $this->call_api( 'GET', $url , $params );
		return json_decode( $response['body'], true );
	}

/**
	* Retrieve a specific engine
	*
	* @param string $engine_id The id of the engine to be found
	* @return array The engine specified by $engine_id
	*/
	public function find_engine( $engine_id ) {
		$url = $this->endpoint . 'engines/' . $engine_id . '.json' ;
		$response = $this->call_api( 'GET', $url );
		return json_decode( $response['body'], true );
	}

/**
	* Create an engine
	*
	* @param array $params An array of engine parameters
	* @return array The engine that was created
	*/
	public function create_engine( $params ) {
		$engine = array( 'engine' => $params );
		$url = $this->endpoint . 'engines.json';
		$response = $this->call_api( 'POST', $url, $engine );
		return json_decode( $response['body'], true );
	}

/**
	* Issue a query to an engine within a specific document_type
	*
	* @param string $engine_id The engine_id of the engine to be searched
	* @param string $document_type_id The document_type_id of the document_type to be searched
	* @param string $query The search query
	* @param array $params An array of search options
	* @return array An array of search results matching the issued query
	*/
	public function search( $engine_id, $document_type_id, $query, $params = array() ) {
		$params = array_merge( array( 'q' => $query, 'page' => 1, 'fetch_fields[posts]' => array( 'external_id' ) ), $params );
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '/search';
		$response = $this->call_api( 'GET', $url, $params );
		return json_decode( $response['body'], true );
	}

/**
	* Retrieve a document_type from an engine
	*
	* @param string $engine_id The engine_id of the engine to be searched
	* @param string $document_type_id The document_type_id of the document_type to be found
	* @return array An array representing the document_type that was found
	*/
	public function find_document_type( $engine_id, $document_type_id ) {
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '.json';
		$response = $this->call_api( 'GET', $url );
		return json_decode( $response['body'], true );
	}

/**
	* Create a document_type within an engine
	*
	* @param string $engine_id The engine_id of the engine in which to create the document_type
	* @param string $document_type_name The name of the document_type to be created
	* @return array An array representing the document_type that was created
	*/
	public function create_document_type( $engine_id, $document_type_name ) {
		$params = array( 'document_type' => array( 'name' => $document_type_name ) );
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types.json';
		$response = $this->call_api( 'POST', $url, $params );
		return json_decode( $response['body'], true );
	}

/**
	* Delete a document from an engine within a specific document_type
	*
	* @param string $engine_id The engine_id of the engine from which to delete the document
	* @param string $document_type_id The name of the document_type from which to delete the document
	* @param string $external_id The external id of the document that is to be deleted
	*/
	public function delete_document( $engine_id, $document_type_id, $external_id ) {
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '/documents';
		$url .= '/' . $external_id;
		$response = $this->call_api( 'DELETE', $url );
	}

/**
	* Delete a set of documents from an engine within a specific document_type
	*
	* @param string $engine_id The engine_id of the engine from which to delete the documents
	* @param string $document_type_id The name of the document_type from which to delete the documents
	* @param array $document_ids An array of the external ids of the documents that are to be deleted
	* @return array An array of true/false elements indicated success or failure of the deletion of each individual document
	*/
public function delete_documents( $engine_id, $document_type_id, $document_ids ) {
		$params = array( 'documents' => $document_ids );
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '/documents/bulk_destroy';
		$response = $this->call_api( 'POST', $url, $params );
		return json_decode( $response['body'], true );
	}

/**
	* Create or update a document in an engine within a specific document_type
	*
	* @param string $engine_id The engine_id of the engine in which to create the document
	* @param string $document_type_id The name of the document_type in which to create the document
	* @param array $document The document to be created
	* @return array An array representing the document that was created
	*/
	public function create_or_update_document( $engine_id, $document_type_id, $document ) {
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '/documents/create_or_update';
		$params = array( 'document' => $document );
		$response = $this->call_api( 'POST', $url, $params );
		return json_decode( $response['body'], true );
	}

/**
	* Create or update a set of documents in an engine within a specific document_type
	*
	* @param string $engine_id The engine_id of the engine in which to create or update the documents
	* @param string $document_type_id The name of the document_type in which to create or update the documents
	* @param array $documents An array of the documents to be created
	* @return array An array of true/false elements indicated success or failure of the creation or update of each individual document
	*/
	public function create_or_update_documents( $engine_id, $document_type_id, $documents ) {
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '/documents/bulk_create_or_update';
		$params = array( 'documents' => $documents );
		$response = $this->call_api( 'POST', $url, $params );
		return json_decode( $response['body'], true );
	}

/**
	* Create a set of documents in an engine within a specific document_type
	*
	* @param string $engine_id The engine_id of the engine in which to create the documents
	* @param string $document_type_id The name of the document_type in which to create the documents
	* @param array $documents An array of the documents to be created
	* @return array An array of true/false elements indicated success or failure of the creation of each individual document
	*/
	public function create_documents( $engine_id, $document_type_id, $documents ) {
		$url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '/documents/bulk_create';
		$params = array( 'documents' => $documents );
		$response = $this->call_api( 'POST', $url, $params );
		return json_decode( $response['body'], true );
	}

/**
	* Based on the API Key supplied to the client, confirm whether or not the client is authorized to talk to the Swiftype API
	*
	* @return bool True or false signaling whether or not the client is authorized
	*/
	public function authorized() {
		$url = $this->endpoint . 'engines.json';
		try {
			$response = $this->call_api( 'GET', $url );
			return ( 200 == $response['code'] );
		} catch( SwiftypeError $e ) {
			return false;
		}
	}

/**
	* Make calls directly to the Swiftype API using wp_remote_request
	*
	* @param string $method The HTTP method to be used for the call: { GET, POST, PUT, DELETE }
	* @param string $url The URL for the call
	* @param array $params An array of parameters to be passed along as part of the call
	* @return array An array containing the HTTP status code as well as the response body
	*/
	private function call_api( $method, $url, $params = array() ) {

		if( $this->api_key )
			$params['auth_token'] = $this->api_key;
		else
			throw new SwiftypeError( 'Unauthorized', 403 );

		$headers = array(
			'User-Agent' => 'Swiftype Wordpress Plugin/' . SWIFTYPE_VERSION,
			'Content-Type' => 'application/json'
		);

		$args = array(
			'method' => '',
			'timeout' => 10,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
			'cookies' => array()
		);

		if( 'GET' == $method || 'DELETE' == $method ) {
			$url .= '?' . $this->serialize_params( $params );
			$args['method'] = $method;
			$args['body'] = array();
		} else if( $method == 'POST' ) {
			$args['method'] = $method;
			$args['body'] = json_encode( $params );
		}

		$response = wp_remote_request( $url, $args );

		if( ! is_wp_error( $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );
			if( 200 == $response_code ) {
				$response_body = wp_remote_retrieve_body( $response );
				return array( 'code' => $response_code, 'body' => $response_body );
			} elseif( 200 != $response_code && ! empty( $response_message ) ) {
				$response_body = wp_remote_retrieve_body( $response );
				throw new SwiftypeError( $response_body, $response_code );
			} else {
				throw new SwiftypeError( 'Unknown Error', $response_code );
			}
		} else {
			throw new SwiftypeError( $response->get_error_message(), 500 );
		}
	}

/**
	* Serialize parameters into query string.  Modifies http_build_query to remove array indexes.
	*
	* @param array $params An array of parameters to be passed along as part of the call
	* @return string The serialized query string
	*/
	private function serialize_params( $params ) {
		$query_string = http_build_query( $params );
		return preg_replace( '/%5B(?:[0-9]+)%5D=/', '%5B%5D=', $query_string );
	}
}
