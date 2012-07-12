<?php

class SwiftypeClient {

  private $endpoint = 'http://api.swiftype.com/api/v1/';
  private $api_key = NULL;

  public function __construct() { }

  public function get_api_key() {
    return $this->api_key;
  }

  public function set_api_key($api_key) {
    $this->api_key = $api_key;
  }

  public function get_engines() {
    $url = $this->endpoint . 'engines.json';
    $response = $this->call_api( 'GET', $url );
    return json_decode( $response['body'], true );
  }

  public function find_engine( $engine_id ) {
    $url = $this->endpoint . 'engines/' . $engine_id . '.json' ;
    $response = $this->call_api( 'GET', $url );
    return json_decode( $response['body'], true );
  }

  public function create_engine( $params ) {
    $engine = array( 'engine' => $params );
    $url = $this->endpoint . 'engines.json';
    $response = $this->call_api( 'POST', $url, $engine );
    return json_decode( $response['body'], true );
  }

  public function search( $engine, $document_type, $query ) {
    $params = array( 'q' => $query, 'per_page' => 100, 'page' => 1 );
    $url = $this->endpoint . 'engines/' . $engine . '/document_types/' . $document_type . '/search';
    $response = $this->call_api( 'GET', $url, $params );
    return json_decode( $response['body'], true );
  }

  public function find_document_type( $engine_id, $document_type_id ) {
    $url = $this->endpoint . 'engines/' . $engine_id . '/document_types/' . $document_type_id . '.json';
    $response = $this->call_api( 'GET', $url );
    return json_decode( $response['body'], true );
  }

  public function create_document_type( $engine, $document_type ) {
    $params = array( 'document_type' => array( 'name' => $document_type ) );
    $url = $this->endpoint . 'engines/' . $engine . '/document_types.json';
    $response = $this->call_api( 'POST', $url, $params );
    return json_decode( $response['body'], true );
  }

  public function delete_document( $engine, $document_type, $external_id ) {
    $url = $this->endpoint . 'engines/' . $engine . '/document_types/' . $document_type . '/documents';
    $url .= '/' . $external_id;
    $response = $this->call_api( 'DELETE', $url, $params );
    return json_decode( $response['body'], true );
  }

public function delete_documents( $engine, $document_type, $document_ids ) {
    $params = array( 'documents' => $document_ids );
    $url = $this->endpoint . 'engines/' . $engine . '/document_types/' . $document_type . '/documents/bulk_destroy';
    $response = $this->call_api( 'POST', $url, $params );
    return json_decode( $response['body'], true );
  }

  public function create_or_update_document( $engine, $document_type, $document ) {
    $url = $this->endpoint . 'engines/' . $engine . '/document_types/' . $document_type . '/documents/create_or_update';
    $params = array( 'document' => $document );
    $response = $this->call_api( 'POST', $url, $params );
    return json_decode( $response['body'], true );
  }

  public function create_or_update_documents( $engine, $document_type, $documents ) {
    $url = $this->endpoint . 'engines/' . $engine . '/document_types/' . $document_type . '/documents/bulk_create_or_update';
    $params = array( 'documents' => $documents );
    $response = $this->call_api( 'POST', $url, $params );
    return json_decode( $response['body'], true );
  }

  public function create_documents( $engine, $document_type, $documents ) {
    $url = $this->endpoint . 'engines/' . $engine . '/document_types/' . $document_type . '/documents/bulk_create';
    $params = array( 'documents' => $documents );
    $response = $this->call_api( 'POST', $url, $params );
    return json_decode( $response['body'], true );
  }

  public function authorized() {
    $url = $this->endpoint . 'engines.json';
    try {
      $response = $this->call_api( 'GET', $url );
      return ( 200 == $response['code'] );
    } catch( SwiftypeError $e ) {
      return false;
    }
  }

  private function call_api( $method, $url, $params = array() ) {

    if( $this->api_key )
      $params['auth_token'] = $this->api_key;
    else
      throw new SwiftypeError( 'Unauthorized', 403 );

    $headers = array(
      'User-Agent' => 'Swiftype Wordpress Plugin/1.0',
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
      $url .= '?' . http_build_query( $params );
      $args['method'] = $method;
      $args['body'] = array();
    } else if( $method == 'POST' ) {
      $args['method'] = $method;
      $args['body'] = json_encode( $params );
    }

    $response = wp_remote_request( $url, $args );

    if( ! is_wp_error( $repsonse ) ) {
      $response_code = wp_remote_retrieve_response_code( $response );
      $response_message = wp_remote_retrieve_response_message( $response );
      $response_body = wp_remote_retrieve_body( $response );
      if( 200 == $response_code ) {
        return array( 'code' => $response_code, 'body' => $response_body );
      } elseif( 200 != $response_code && ! empty( $response_message ) ) {
        throw new SwiftypeError( $response_message, $reponse_code );
      } else {
        throw new SwiftypeError( 'Unknown Error', $reponse_code );
      }
    } else {
      throw new SwiftypeError( $response->get_error_message(), $response->get_error_code() );
    }
  }

}

?>