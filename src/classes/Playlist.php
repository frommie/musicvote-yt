<?php

class PlaylistEmptyException extends Exception {}

class Playlist {
  protected $db;
  protected $playlist;
  protected $playing;

  public function __construct($db) {
    $this->db = $db;
    // get playlist from db
    // playing video is first
    $sql = "SELECT video_id, votes, playing FROM playlist ORDER BY playing DESC, votes DESC";
    $stmt = $this->db->query($sql);

    $results = [];
    while ($row = $stmt->fetch()) {
        $results[] = $video = Video::with_video_id($this->db, $row['video_id']);
        if ($row['playing']) {
          $this->playing = $row['video_id'];
        }
    }
    if (count($results) == 0) {
      $results[0] = $this->get_fallback_video();
      $results[0]->insert_in_playlist();
    }
    $this->playlist = $results;
  }

  public function get_top_video() {
    if (count($this->playlist) == 0) {
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

  public function remove_playing_video() {
    if (count($this->playlist) == 0) {
      throw new PlaylistEmptyException;
    }
    // remove current playing video
    $current_playing_id = $this->playlist[0]->get_video_id();
    $this->remove($current_playing_id); // remove from db
    $this->remove_votes($current_playing_id); // remove votes
    array_splice($this->playlist, 0, 1);
  }

  public function get_playlist() {
    $arr = array();
    foreach ($this->playlist as $video) {
      $arr[] = json_decode(strval($video), true);
    }
    foreach ($arr as $key => $item) {
      if ($item['video_id'] == $this->playing) {
        $arr[$key]['playing'] = 1;
      } else {
        $arr[$key]['playing'] = 0;
      }
    }
    return $arr;
  }

  public function remove($video_id) {
    // remove video from playlist
    $sql = "DELETE FROM playlist WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $video_id
    ]);
  }

  public function remove_votes($video_id) {
    // remove votes for video
    $sql = "DELETE FROM votes WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $video_id
    ]);
  }

  public function get_fallback_video() {
    // first check if fallback playlist is already in db
    if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql") {
      $sql = "SELECT video_id FROM fallback_playlist ORDER BY random() LIMIT 1";
    } else {
      $sql = "SELECT video_id FROM fallback_playlist ORDER BY RAND() LIMIT 1";
    }
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
      // return random video from db
      return Video::with_video_id($this->db, $stmt->fetch()['video_id']);
    } else {
      if ($this->load_fallback_playlist()) {
        // call again
        return $this->get_fallback_video();
      } else {
        throw new PlaylistEmptyException;
      }
    }
  }

  public function load_fallback_playlist() {
    // check config for playlist id
    $config_file = dirname(__FILE__) . '/../../config.php';
    if (is_readable($config_file)) {
      require $config_file;
      if ($config['fallback_playlist'] != "") {
        $fallback_playlist_id = $config['fallback_playlist'];
      }
    }

    // check if deployed to heroku
    if (getenv("FALLBACK_PLAYLIST") !== false) {
      $fallback_playlist_id = getenv("FALLBACK_PLAYLIST");
    }

    if ($fallback_playlist_id != "") {
      return $this->save_fallback_playlist($fallback_playlist_id);
    } else {
      return false;
    }
  }

  public function save_fallback_playlist($fallback_playlist_id) {
    // API call to get videos from fallback playlist
    $api = new YoutubeAPI($this->db);
    $fallback_videos = json_decode($api->get_playlist_items($fallback_playlist_id), true);
    if (count($fallback_videos) == 0) {
      return false;
    } else {
      foreach ($fallback_videos as $video) {
        $sql = "INSERT INTO fallback_playlist (video_id) VALUES (:video_id)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
          'video_id' => $video['video_id']
        ]);
        if(!$result) {
          throw new Exception("could not save record");
        }
      }
      return true;
    }
  }
}
