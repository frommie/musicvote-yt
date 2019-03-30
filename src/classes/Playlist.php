<?php

class Playlist {
  protected $db;
  protected $playlist;

  public function __construct($db) {
    $this->db = $db;
    // get playlist from db
    $sql = "SELECT video_id, votes FROM playlist ORDER BY votes DESC";
    $stmt = $this->db->query($sql);

    $results = [];
    while($row = $stmt->fetch()) {
        $results[] = $video = Video::with_video_id($this->db, $row['video_id']);
    }
    $this->playlist = $results;
  }

  public function get_next_video() {
    // return next video in list and remove top video
    $next_id = $this->playlist[0]->get_video_id();
    $this->remove($next_id);
    return $next_id;
  }

  public function get_playlist() {
    return $this->playlist;
  }

  public function remove($video_id) {
    $sql = "DELETE FROM playlist WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $video_id
    ]);
  }
}
