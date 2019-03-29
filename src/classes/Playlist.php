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
        $results[] = new Video($row['video_id'], $row['votes']);
    }
    $this->playlist = $results;
  }

  public function get_next_video() {
    return $this->playlist[0];
  }

  public function get_playlist() {
    return $this->playlist;
  }
}
