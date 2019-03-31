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
    // call search
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
    $videos = array();
    $video_ids = "";
    foreach ($search as $item) {
      if ($item['id']['videoId'] != "") {
        $arr[$item['id']['videoId']] = array(
          'title' => html_entity_decode($item['snippet']['title']),
          'img' => $item['snippet']['thumbnails']['high']['url']
        );
        $video_ids .= $item['id']['videoId'] . ", ";
      }
    }

    // call contentDetails for video IDs
    $search = $this->service->videos->listVideos('contentDetails', array(
      'id' => $video_ids,
    ))['items'];
    foreach ($search as $item) {
      $arr[$item['id']]['duration'] = YoutubeAPI::ISO8601ToSeconds($item['contentDetails']['duration']);
    }

    // now create Video instances
    foreach ($arr as $video_id => $video) {
      try {
        $video_result = new Video(
          $this->db,
          $video_id,
          $video['title'],
          $video['img'],
          $video['duration']
        );
        $video_result->save();
        array_push($videos, json_decode(strval($video_result)));
      } catch (VideoIDNullException $e) {}
    }
    return json_encode($videos);
  }

  public static function ISO8601ToSeconds($ISO8601){
  	$interval = new \DateInterval($ISO8601);

  	return ($interval->d * 24 * 60 * 60) +
  		($interval->h * 60 * 60) +
  		($interval->i * 60) +
  		$interval->s;
  }
}
