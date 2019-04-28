<?php

/*
 * Empty Playlist Exception
 */
class PlaylistEmptyException extends Exception {}

/*
 * Manages playlist
 */
class Playlist {
  protected $db;
  protected $playlist;
  protected $playing;

  /*
   * Constructor
   * @db PDO connection
   */
  public function __construct($db) {
    $this->db = $db;
    // get playlist from db
    // playing video is first
    $sql = 'SELECT video_id, votes, playing FROM playlist ORDER BY playing DESC, votes DESC';
    $stmt = $this->db->query($sql);

    $results = [];
    $rows = $stmt->fetchAll();
    $stmt->closeCursor();
    $row_count = (int)count($rows);
    for ($i = 0; $i < $row_count; $i++) {
      $videos[] = array('video_id' => $rows[$i]['video_id'], 'playing' => $rows[$i]['playing']);
    }

    $video_count = (int)count($videos);
    for ($i = 0; $i < $video_count; $i++) {
      $results[] = Video::with_video_id($this->db, $videos[$i]['video_id']);
      if ($videos[$i]['playing']) {
        $this->playing = $videos[$i]['video_id'];
      }
    }
    if (count($results) == 0) {
      $results[0] = $this->get_fallback_video();
      $results[0]->insert_in_playlist();
    }
    $this->playlist = $results;
  }

  /*
   * Returns current playlist
   */
  public function pr() {
    return $this->playlist;
  }

  /*
   * Gets current top video id
   * returns top video id
   */
  public function get_top_video() {
    if (count($this->playlist) == 0) {
      // if playlist empty try to get fallback video and set as top video in playlist
      try {
        $this->playlist[0] = $this->get_fallback_video();
        $this->playlist[0]->insert_in_playlist();
        $this->playlist[0]->playing();
      } catch (PlaylistEmptyException $e) {
        die ($e);
      }
    }
    // set playing status to first video in list
    $this->playlist[0]->playing();

    return $this->playlist[0]->get_video_id();
  }

  /*
   * Removes the current playing video from playlist
   * returns Playlist array without current playing video
   */
  public function remove_playing_video() {
    if (count($this->playlist) == 0) {
      throw new PlaylistEmptyException;
    }
    // remove current playing video
    $current_playing_id = $this->playlist[0]->get_video_id();
    $this->remove($current_playing_id); // remove from db
    array_splice($this->playlist, 0, 1);
  }

  /*
   * Gets current playlist with user vote directions and current playing video id
   */
  public function get_playlist($session_id) {
    $arr = array();
    $playlist_count = (int)count($this->playlist);
    for ($i = 0; $i < $playlist_count; $i++) {
      $arr[$this->playlist[$i]->get_video_id()] = json_decode(strval($this->playlist[$i]), true);
    }

    $votes = new Votes($this->db, $session_id);
    $user_votes = $votes->get_user_votes();

    foreach ($arr as $key => $item) {
      if ($item['video_id'] == $this->playing) {
        $arr[$key]['playing'] = 1;
      } else {
        $arr[$key]['playing'] = 0;
      }
    }

    foreach ($user_votes as $user_vote) {
      $arr[$user_vote['video_id']]['direction'] = $user_vote['direction'];
    }
    $arr = array_values($arr);
    return json_encode($arr);
  }

  /*
   * Removes video id from playlist
   */
  public function remove($video_id) {
    // remove video from playlist
    $sql = 'DELETE FROM playlist WHERE video_id = :video_id';
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $video_id
    ]);
    $this->remove_votes($video_id); // remove votes
  }

  /*
   * Removes votes for video id
   */
  public function remove_votes($video_id) {
    // remove votes for video
    $sql = 'DELETE FROM votes WHERE video_id = :video_id';
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $video_id
    ]);
  }

  /*
   * Gets fallback video from fallback playlist if set in config
   * returns fallback video or PlaylistEmptyException if not successfull
   */
  public function get_fallback_video() {
    // first check if fallback playlist is already in db
    if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
      $sql = 'SELECT video_id FROM fallback_playlist ORDER BY random() LIMIT 1';
    } else {
      $sql = 'SELECT video_id FROM fallback_playlist ORDER BY RAND() LIMIT 1';
    }
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll()[0];
    $stmt->closeCursor();
    if (!empty($result)) {
      // return random video from db
      return Video::with_video_id($this->db, $result['video_id']);
    } else {
      if ($this->load_fallback_playlist()) {
        // call again
        return $this->get_fallback_video();
      } else {
        throw new PlaylistEmptyException;
      }
    }
  }

  /*
   * Loads fallback playlist id from config or environment variable
   */
  public function load_fallback_playlist() {
    // check config for playlist id
    $config_file = dirname(__FILE__) . '/../../config.php';
    if (is_readable($config_file)) {
      require $config_file;
      if ($config['fallback_playlist'] != '') {
        $fallback_playlist_id = $config['fallback_playlist'];
      }
    }

    // check if deployed to heroku
    if (getenv('FALLBACK_PLAYLIST') !== false) {
      $fallback_playlist_id = getenv('FALLBACK_PLAYLIST');
    }

    if ($fallback_playlist_id != '') {
      return $this->save_fallback_playlist($fallback_playlist_id);
    } else {
      return false;
    }
  }

  /*
   * Gets playlist information from Youtube API and saves to db
   * returns True if videos saved
   */
  public function save_fallback_playlist($fallback_playlist_id) {
    // API call to get videos from fallback playlist
    $api = new YoutubeAPI($this->db);
    $fallback_videos = json_decode($api->get_playlist_items($fallback_playlist_id), true);
    if (count($fallback_videos) == 0) {
      return false;
    } else {
      $fallback_count =(int)count($fallback_videos);
      for ($i = 0; $i < $fallback_count; $i++) {
        $sql = 'INSERT INTO fallback_playlist (video_id) VALUES (:video_id)';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
          'video_id' => $fallback_videos[$i]['video_id']
        ]);
        if(!$result) {
          throw new Exception('could not save record');
        }
      }
      return true;
    }
  }
}
