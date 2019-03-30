<?php

class YoutubeAPI {
  protected $service;
  protected $db;

  public function __construct($db) {
    $this->db = $db;
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
    $search = $this->service->search->listSearch('snippet', array(
      'maxResults' => 24,
      'q' => $query,
      'type' => 'video',
      'videoDuration' => 'short', // can be any, long, medium or short
      'videoEmbeddable' => 'true',
      'videoSyndicated' => 'true',
      'regionCode' => 'DE'
    ))['items'];
    $arr = array();
    foreach ($search as $item) {
      try {
        $video_result = new Video($this->db, $item['id']['videoId'], html_entity_decode($item['snippet']['title']), $item['snippet']['thumbnails']['high']['url']);
        $video_result->save();
        array_push($arr, json_decode(strval($video_result)));
      } catch (VideoIDNullException $e) {}
    }
    return json_encode($arr);
  }
}
