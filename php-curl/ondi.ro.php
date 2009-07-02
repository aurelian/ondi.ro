<?php
if(extension_loaded('curl')===false) die("ondi.ro PHP-cURL client requires PHP cURL extension!");

class OndiRequest {

  private $api_key;
  private $server;
  private $format;
  private $formats=array('xml'=>'application/xml','json'=>'application/json','georss'=>'application/georss+xml');
  private $handler;

  public function OndiRequest($api_key, $server='http://ondi.ro') {
    $this->api_key= $api_key;
    $this->server= $server;
    $this->format='application/xml';
    $this->create_handler();
  }

  private function create_handler() {
    if(false===is_null($this->handler)) return;

    $this->handler = curl_init();
    curl_setopt($this->handler, CURLOPT_HEADER, 0);
    curl_setopt($this->handler, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($this->handler, CURLOPT_VERBOSE, 0);
    $x= curl_version();
    $agent= sprintf("ondi.ro PHP-cURL 0.2/(PHP:%s, cURL:%s, API: 0.5) +http://ondi.ro/clients.html", PHP_VERSION, $x['version']);
    curl_setopt($this->handler, CURLOPT_USERAGENT, $agent);
    curl_setopt($this->handler, CURLOPT_ENCODING, 'gzip,deflate');
  }

  public function locate($name, $page= 1, $format= null) {
    $this->setup_format($format);
    $data= array(
      'api_key' => $this->api_key,
      'name'    => $name,
      'page'    => (int)$page
    );
    return $this->get('/api/locate', $data);
  }

  public function reverse($lat, $long, $radius= 2.0, $page= 1, $format= null) {
    $this->setup_format($format);
    $data= array(
      'api_key'   => $this->api_key,
      'latitude'  => $lat,
      'longitude' => $long,
      'radius'    => $radius,
      'page'      => (int)$page
    );
    return $this->get('/api/reverse', $data);
  }

  public function where($text, $page= 1, $format= null) {
    $this->setup_format($format);
    $data= array(
      'api_key' => $this->api_key,
      'text'    => $text,
      'page'    => (int)$page
    );
    return $this->post('/api/where', $data);
  }

  protected function get($base_url, $data) {
    curl_setopt($this->handler, CURLOPT_HTTPGET, 1);
    curl_setopt($this->handler, CURLOPT_URL, sprintf("%s%s?%s", $this->server, $base_url, http_build_query($data)));
    return new OndiResponse($this->handler);
  }

  protected function post($base_url, $data) {
    curl_setopt($this->handler, CURLOPT_POST, 1);
    curl_setopt($this->handler, CURLOPT_URL, $this->server . $base_url);
    curl_setopt($this->handler, CURLOPT_POSTFIELDS, http_build_query($data));
    return new OndiResponse($this->handler);
  }

  protected function setup_format($format) {
    if(true===is_null($format)) {
      // nothing. we have a default
    } elseif(in_array($format, array_keys($this->formats))) {
      $this->format= $this->formats[$format];
    } elseif(in_array($format, array_values($this->formats))) {
      $this->format= $format;
    } else {
      throw new Exception(sprintf("Invalid format `%s', please use one of: %s", $format, join(', ', array_keys($this->formats))));
    }
    curl_setopt($this->handler, CURLOPT_HTTPHEADER, array("Accept: $this->format") );
  }

}

class OndiResponse {
  
  private $data;
  private $handler;

  public function OndiResponse($handler) {
    $this->handler= $handler;
    $this->data= curl_exec($this->handler);
  }

  public function data() {
    return $this->data;
  }

  public function __toString() {
    return $this->data();
  }

}
