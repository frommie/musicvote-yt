<?php

class VideoIDNullException extends Exception {}
class VideoNotFoundException extends Exception {}

class Video {
  protected $db;
  protected $video_id;
  protected $title;
  protected $img;
  protected $duration;
  protected $votes;
  protected $playing;

  public function __construct($db, $video_id, $title, $img, $duration, $playing = false) {
    if ($video_id != "") {
      $this->db = $db;
      $this->video_id = $video_id;
      $this->title = $title;
      $this->img = $img;
      $this->duration = $duration;
      $this->votes = $this->get_votes();
      $this->playing = $playing;
    } else {
      throw new VideoIDNullException();
    }
  }

  public static function with_video_id($db, $video_id) {
    $sql = "SELECT v.title, v.img, v.duration, p.playing, p.votes FROM videos v LEFT JOIN playlist p ON v.video_id = p.video_id WHERE v.video_id = :video_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(["video_id" => $video_id]);
    $result = $stmt->fetchAll()[0];
    $stmt->closeCursor();
    if (!empty($result)) {
      return new self($db, $video_id, $result['title'], $result['img'], $result['duration'], $result['playing']);
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
    $stmt->execute(["video_id" => $this->video_id]);
    $result = $stmt->fetchAll();
    $stmt->closeCursor();
    if (!empty($result)) {
      return $result[0]['votes'];
    } else {
      return 0;
    }
  }

  public function is_playing() {
    return $this->playing;
  }

  public function save() {
    if (!$this->exists_on_db()) {
      $sql = "INSERT INTO videos (video_id, title, img, duration) VALUES (:video_id, :title, :img, :duration)";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        'video_id' => $this->video_id,
        'title' => $this->title,
        'img' => $this->img,
        'duration' => $this->duration
      ]);
    }
  }

  public function exists_on_db() {
    $sql = "SELECT * FROM videos WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["video_id" => $this->video_id]);
    $result = $stmt->fetchAll();
    $stmt->closeCursor();
    if (!empty($result[0])) {
      return true;
    } else {
      return false;
    }
  }

  public function exists_in_playlist() {
    $sql = "SELECT * FROM playlist WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["video_id" => $this->video_id]);
    $result = $stmt->fetchAll();
    $stmt->closeCursor();
    if (!empty($result[0])) {
      return true;
    } else {
      return false;
    }
  }

  public function vote($session_id, $direction) {
    // check if already voted for that ID
    $sql = "SELECT direction FROM votes WHERE video_id = :video_id AND session_id = :session_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "video_id" => $this->video_id,
      "session_id" => $session_id
    ]);

    $result = $stmt->fetchAll();
    $stmt->closeCursor();

    if (!empty($result[0])) {
      $result = $result[0];
      // already voted
      $before_direction = $result['direction'];
      if ($before_direction != $direction) { // only senseful case
        if ($before_direction == "+") { // before it was upvoted, so downvote now
          $direction = "-";
          $this->votes--;
        } else {
          $direction = "+"; // before it was downvoted, so upvote now
          $this->votes++;
        }
        // correct entry
        $sql = "UPDATE votes SET direction = :direction WHERE video_id = :video_id AND session_id = :session_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
          "direction" => $direction,
          "video_id" => $this->video_id,
          "session_id" => $session_id
        ]);

        if(!$result) {
          throw new Exception("could not save record");
        }
        $this->save_vote($direction, 2);
      } else {
        // do nothing
        return;
      }
    } else {
      $this->log_vote($session_id, $direction);
      $this->save_vote($direction, 1);
    }
  }

  public function save_vote($operation, $count) {
    // not voted yet
    // check if already in playlist
    if ($this->exists_in_playlist()) {
      $sql = "UPDATE playlist SET votes = votes " . $operation . " " . $count . " WHERE video_id = :video_id";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        "video_id" => $this->video_id
      ]);

      if(!$result) {
        throw new Exception("could not save record");
      }
    } else {
      if ($direction != "down") {
        // insert into playlist
        $this->insert_in_playlist_and_vote();
      }
    }
  }

  public function log_vote($session_id, $direction) {
    $sql = "INSERT INTO votes (session_id, video_id, direction) VALUES (:session_id, :video_id, :direction)";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'session_id' => $session_id,
      'video_id' => $this->video_id,
      'direction' => $direction
    ]);
    if(!$result) {
      throw new Exception("could not save record");
    }
  }

  public function insert_in_playlist_and_vote() {
    $sql = "INSERT INTO playlist (video_id, votes) VALUES (:video_id, :votes)";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $this->video_id,
      'votes' => 1 // called when voted from search
    ]);
    if(!$result) {
      throw new Exception("could not save record");
    }
  }

  public function insert_in_playlist() {
    $sql = "INSERT INTO playlist (video_id, votes) VALUES (:video_id, :votes)";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'video_id' => $this->video_id,
      'votes' => 0 // called when random video inserted into playlist
    ]);
    if(!$result) {
      throw new Exception("could not save record");
    }
  }

  public function playing() {
    $this->playing = 1;
    // set playing status in db
    $sql = "UPDATE playlist SET playing = TRUE WHERE video_id = :video_id";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "video_id" => $this->video_id
    ]);

    if(!$result) {
      throw new Exception("could not save record");
    }
  }

  public function __toString() {
    $arr = array(
      'video_id' => $this->video_id,
      'title' => $this->title,
      'img' => $this->img,
      'duration' => $this->duration,
      'votes' => $this->votes
    );
    return json_encode($arr);
  }
}
