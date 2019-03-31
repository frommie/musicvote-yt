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
    while($row = $stmt->fetch()) {
        $results[] = $video = Video::with_video_id($this->db, $row['video_id']);
        if ($row['playing']) {
          $this->playing = $row['video_id'];
        }
    }
    $this->playlist = $results;
  }

  public function get_top_video() {
    if (count($this->playlist) == 0) {
      throw new PlaylistEmptyException;
    }
    // set playing status to first video in list
    $this->playlist[0]->playing();

    return $this->playlist[0]->get_video_id();
  }

  public function remove_playing_video() {
    if (count($this->playlist) == 1) {
      throw new PlaylistEmptyException;
    }
    // remove current playing video
    $current_playing_id = $this->playlist[0]->get_video_id();
    $this->remove($current_playing_id); // remove from db
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
}
