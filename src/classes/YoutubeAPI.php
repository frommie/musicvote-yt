<?php

class YoutubeAPI {
  protected $service;

  public function __construct() {
    $this->client = new Google_Client();
    $this->client->setDeveloperKey($this->get_api_key());
    $this->service = new Google_Service_YouTube($this->client);
  }

  private function get_api_key() {
    $config_file = dirname(__FILE__) . '/../../config.php';
    if (is_readable($config_file)) {
      require $config_file;
      return $config['api_key'];
    }
    if (getenv("API_KEY") !== false) {
      return getenv("API_KEY");
    }
  }

  public function search($query) {
    $search = $this->service->search->listSearch('snippet', array('maxResults' => 24, 'q' => $query, 'type' => ''))['items'];
    $arr = array();
    foreach ($search as $item) {
      $cleaned_item = array();
      $cleaned_item['video_id'] = $item['id']['videoId'];
      $cleaned_item['title'] = html_entity_decode($item['snippet']['title']);
      $cleaned_item['img'] = $item['snippet']['thumbnails']['high']['url'];
      array_push($arr, $cleaned_item);
    }
    return json_encode($arr);
  }
}
