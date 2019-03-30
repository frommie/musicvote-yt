<?php

class VideoIDNullException extends Exception {}
class VideoNotFoundException extends Exception {}

class Video {
  protected $db;
  protected $video_id;
  protected $title;
  protected $img;
  protected $votes;

  public function __construct($db, $video_id, $title, $img) {
    if ($video_id != "") {
      $this->db = $db;
      $this->video_id = $video_id;
      $this->title = $title;
      $this->img = $img;
      $this->votes = $this->get_votes();
    } else {
      throw new VideoIDNullException();
    }
  }

  public static function with_video_id($db, $video_id) {
    $sql = "SELECT title, img FROM videos WHERE video_id = :video_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(["video_id" => $video_id]);
    if ($stmt->rowCount() > 0) {
      $result = $stmt->fetch();
      return new self($db, $video_id, $result['title'], $result['img']);
    } else {
      throw new VideoNotFoundException();
    }
  }

  public function get_video_id() {
    return $this->video_id;
  }

  public function get_votes() {
    $sql = "SELECT votes FROM playlist WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute(["video_id" => $this->video_id]);
    if ($stmt->rowCount() > 0) {
      return $stmt->fetch()['votes'];
    } else {
      return 0;
    }
  }

  public function save() {
    if (!$this->exists_on_db()) {
      $sql = "INSERT INTO videos (video_id, title, img) VALUES (:video_id, :title, :img)";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        'video_id' => $this->video_id,
        'title' => $this->title,
        'img' => $this->img
      ]);
    }
  }

  public function exists_on_db() {
    $sql = "SELECT * FROM videos WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute(["video_id" => $this->video_id]);
    if ($stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function exists_in_playlist() {
    $sql = "SELECT * FROM playlist WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute(["video_id" => $this->video_id]);
    if ($stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function add_vote() {
    $this->votes = $this->votes + 1;
    // check if already in playlist
    if ($this->exists_in_playlist()) {
      $sql = "UPDATE playlist SET votes = votes + 1 WHERE video_id = :video_id";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        "video_id" => $this->video_id
      ]);

      if(!$result) {
          throw new Exception("could not save record");
      }
    } else {
      // insert into playlist
      $sql = "INSERT INTO playlist (video_id, votes) VALUES (:video_id, :votes)";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        'video_id' => $this->video_id,
        'votes' => $this->votes
      ]);
      if(!$result) {
          throw new Exception("could not save record");
      }
    }
  }

  public function __toString() {
    $arr = array(
      'video_id' => $this->video_id,
      'title' => $this->title,
      'img' => $this->img,
      'votes' => $this->votes
    );
    return json_encode($arr);
  }
}
