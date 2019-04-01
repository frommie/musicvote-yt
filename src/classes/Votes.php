<?php

class Votes {
  protected $db;
  protected $votes;

  public function __construct($db, $session_id) {
    $this->db = $db;
    $sql = "SELECT video_id, direction FROM votes WHERE session_id = :session_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["session_id" => $session_id]);
    $this->votes = array();
    while ($row = $stmt->fetch()) {
      $this->votes[] = array('video_id' => $row['video_id'], 'direction' => $row['direction']);
    }
  }

  public function get_votes() {
    return $this->votes;
  }
}
