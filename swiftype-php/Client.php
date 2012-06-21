<?php

class SwiftypeClient {

  private $endpoint = "http://api.swiftype.com/api/v1/";
  private $api_key = NULL;

  public function __construct() { }

  public function get_api_key() {
    return $this->api_key;
  }

  public function set_api_key($api_key) {
    $this->api_key = $api_key;
  }

  public function get_engines() {
    $url = $this->endpoint . "engines.json";
    $response = $this->call_api("GET", $url);
    if($response->status == 200) {
      return json_decode($response->body, true);
    }
  }

  public function find_engine($engine_id) {
    $url = $this->endpoint . "engines/" . $engine_id . ".json" ;
    $response = $this->call_api("GET", $url);
    if($response->status == 200) {
      return json_decode($response->body, true);
    }
  }

  public function create_engine($params) {
    $engine = array('engine' => $params);
    $url = $this->endpoint . "engines.json";
    $response = $this->call_api("POST", $url, $engine);
    if($response->status == 200) {
      $engine = json_decode($response->body, true);
      return $engine;
    } else {
      return $response->body;
    }
  }

  public function search($engine, $document_type, $query) {
    $params = array('q' => $query);
    $url = $this->endpoint . "engines/" . $engine . "/document_types/" . $document_type . "/search";
    $response = $this->call_api("GET", $url, $params);
    return $response;
  }

  public function find_document_type($engine_id, $document_type_id) {
    $url = $this->endpoint . "engines/" . $engine_id . "/document_types/" . $document_type_id . ".json";
    $response = $this->call_api("GET", $url);
    if($response->status == 200) {
      return json_decode($response->body, true);
    }
  }

  public function create_document_type($engine, $document_type) {
    $params = array('document_type' => array('name' => $document_type));
    $url = $this->endpoint . "engines/" . $engine . "/document_types.json";
    $response = $this->call_api("POST", $url, $params);
    if($response->status == 200) {
      return json_decode($response->body, true);
    }
  }

  public function delete_document($engine, $document_type, $external_id) {
    $url = $this->endpoint . "engines/" . $engine . "/document_types/" . $document_type . "/documents";
    $url .= '/' . $external_id;
    $this->call_api("DELETE", $url, $params);
  }

public function delete_documents($engine, $document_type, $document_ids) {
    $params = array('documents' => $document_ids);
    $url = $this->endpoint . "engines/" . $engine . "/document_types/" . $document_type . "/documents/bulk_destroy";
    $this->call_api("POST", $url, $params);
  }

  public function create_or_update_document($engine, $document_type, $document) {
    $url = $this->endpoint . "engines/" . $engine . "/document_types/" . $document_type . "/documents/create_or_update";
    $params = array('document' => $document);
    $this->call_api("POST", $url, $params);
  }

  public function create_or_update_documents($engine, $document_type, $documents) {
    $url = $this->endpoint . "engines/" . $engine . "/document_types/" . $document_type . "/documents/bulk_create_or_update";
    $params = array('documents' => $documents);
    $this->call_api("POST", $url, $params);
  }

  public function create_documents($engine, $document_type, $documents) {
    $url = $this->endpoint . "engines/" . $engine . "/document_types/" . $document_type . "/documents/bulk_create";
    $params = array('documents' => $documents);
    $this->call_api("POST", $url, $params);
  }

  public function authorized() {
    $url = $this->endpoint . "engines.json";
    $response = $this->call_api("GET", $url);
    return ($response->status == 200);
  }

  private function call_api($method, $url, $params=array()) {

    if($this->api_key) {
      $params['auth_token'] = $this->api_key;
    } else {
      return new SwiftypeHttpResponse(401,'Unauthorized. You must set your API Key before making a request.');
    }

    if ($method == "GET" || $method == "DELETE") {
      $args = http_build_query($params);
      $url .= '?' . $args;
      $args = '';
    } else {
      $args = json_encode($params);
    }

    $session = curl_init($url);
    curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($session, CURLOPT_POSTFIELDS, $args);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Expect:', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_USERAGENT, "Swiftype Wordpress Plugin/1.0");

    $response = curl_exec($session);
    $status = curl_getinfo($session, CURLINFO_HTTP_CODE);
    curl_close($session);

    return new SwiftypeHttpResponse($status, $response);
  }

}