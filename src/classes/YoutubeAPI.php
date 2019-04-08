<?php

/*
 * Manages Youtube API connection
 */
class YoutubeAPI {
  protected $service;
  protected $db;

  /*
   * Constructor
   * @db PDO connection
   */
  public function __construct($db) {
    $this->db = $db;
    $this->client = new Google_Client();
    $this->client->setDeveloperKey($this->get_api_key());
    $this->service = new Google_Service_YouTube($this->client);
  }

  /*
   * Gets API key from config file or environment variable
   * returns API Key
   */
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

  /*
   * Calls search endpoint of Youtube API
   * @query Search query as String
   * returns Search result as JSON
   */
  public function search($query) {
    // call search
    $search = $this->service->search->listSearch('snippet', array(
      'maxResults' => 24,
      'q' => $query,
      'type' => 'video',
      'videoDuration' => 'any', // can be any, long, medium or short
      'videoEmbeddable' => 'true',
      'videoSyndicated' => 'true',
      'regionCode' => 'DE'
    ))['items'];

    $videos = $this->get_video_array($search);

    return json_encode($videos);
  }

  /*
   * Gets video information from API return array
   * returns Videos array
   */
  public function get_video_array($search) {
    $arr = array();
    $video_ids = "";
    $search_count = (int)count($search);
    for ($i = 0; $i < $search_count; $i++) {
      if ($search[$i]['id']['videoId'] != "") {
        $arr[$search[$i]['id']['videoId']] = array(
          'title' => html_entity_decode($search[$i]['snippet']['title']),
          'img' => $search[$i]['snippet']['thumbnails']['high']['url']
        );
        $video_ids .= $search[$i]['id']['videoId'] . ", ";
      }
    }

    $videos = $this->get_video_details($arr, $video_ids);
    return $videos;
  }

  /*
   * Gets detailed video information for video ids from Videos API endpoint
   * @arr Current video array
   * @video_ids Video ids to get detailed information for
   * returns Videos array
   */
  public function get_video_details($arr, $video_ids) {
    $videos = array();
    // call contentDetails for video IDs
    $search = $this->service->videos->listVideos('contentDetails,status', array(
      'id' => $video_ids,
    ))['items'];
    $search_count = (int)count($search);
    for ($i = 0; $i < $search_count; $i++) {
      $arr[$search[$i]['id']]['duration'] = YoutubeAPI::ISO8601ToSeconds($search[$i]['contentDetails']['duration']);
      $arr[$search[$i]['id']]['status'] = $search[$i]['status'];
    }

    // now create Video instances
    foreach ($arr as $video_id => $video) {
      if ($video['status']['privacyStatus'] == "public" && $video['status']['embeddable'] == true) {
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
    }
    return $videos;
  }

  /*
   * Gets playlist items from playlist API endpoint
   * @playlist_id Playlist id to get informations for
   * returns Videos JSON array
   */
  public function get_playlist_items($playlist_id) {
    $playlist_items = $this->service->playlistItems->listPlaylistItems('snippet', array(
      'maxResults' => 25,
      'playlistId' => $playlist_id
    ))['items'];
    $videos = $this->get_playlist_array($playlist_items);
    return json_encode($videos);
  }

  /*
   * Gets playlist information from API return array
   * @items Returned playlist items
   * returns Videos array
   */
  public function get_playlist_array($items) {
    $arr = array();
    $video_ids = "";
    $items_count = (int)count($search);
    for ($i = 0; $i < $items_count; $i++) {
      if ($items[$i]['snippet']['resourceId']['videoId'] != "") {
        $arr[$item['snippet']['resourceId']['videoId']] = array(
          'title' => html_entity_decode($items[$i]['snippet']['title']),
          'img' => $items[$i]['snippet']['thumbnails']['high']['url']
        );
        $video_ids .= $items[$i]['snippet']['resourceId']['videoId'] . ", ";
      }
    }
    $videos = $this->get_video_details($arr, $video_ids);
    return $videos;
  }

  /*
   * Static function to convert ISO 8601 time format to DateInterval
   * return DateInterval
   */
  public static function ISO8601ToSeconds($ISO8601){
  	$interval = new \DateInterval($ISO8601);

  	return ($interval->d * 24 * 60 * 60) +
  		($interval->h * 60 * 60) +
  		($interval->i * 60) +
  		$interval->s;
  }
}
