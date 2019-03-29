<?php

class Video {
  protected $video_id;
  protected $votes;
  protected $db;

  public function __construct($db, $video_id, $votes) {
    $this->video_id = $video_id;
    $this->votes = $votes;
  }

  public function get_video_id() {
    return $this->video_id;
  }

  public function get_votes() {
    return $this->votes;
  }

  public function add_vote() {
    $this->votes = $this->votes + 1;
    $sql = "UPDATE playlist SET votes = votes + 1 WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "video_id" => $this->video_id
    ]);

    if(!$result) {
        throw new Exception("could not save record");
    }
  }
}
