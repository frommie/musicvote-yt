<?php

/*
 * VideoIDNullException is thrown if video_id is empty
 */
class VideoIDNullException extends Exception {}

/*
 * VideoNotFoundException is thrown if video_id was not found in video table
 */
class VideoNotFoundException extends Exception {}

/*
 * Manages video information
 */
class Video {
  protected $db;
  protected $video_id;
  protected $title;
  protected $img;
  protected $duration;
  protected $votes;
  protected $playing;

  /*
   * Constructor
   * @db PDO connection
   * @video_id Video id of video
   * @title Title information for video
   * @img URL to thumbnail image
   * @duration Duration of video in seconds
   * @playing True if current playing in playlist, default false
   */
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

  /*
   * Loads video object by video_id
   * @db PDO connection
   * @video_id Video id of video to load
   * returns Video object
   */
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

  /*
   * Gets id of video
   * returns Video id
   */
  public function get_video_id() {
    return $this->video_id;
  }

  /*
   * Gets votes for video
   * returns Number of votes for video id
   */
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

  /*
   * Returns playing information
   * returns True if curently playing
   */
  public function is_playing() {
    return $this->playing;
  }

  /*
   * Saves video to videos table
   */
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

  /*
  * Checks if video already exists in videos table
  * returns True if video already exists in videos table
   */
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

  /*
   * Checks if video already exists in playlist
   * returns True if video already exists in playlist
   */
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

  /*
   * Sets vote number for video by checking for before votes by user, so it correct the voting
   * e.g. User votes '-' before and now '+' --> video should be voted +2
   * TODO extend for '-' and '-' to remove vote
   * @session_id Client session id
   * @direction Direction of vote ('+' or '-')
   */
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

  /*
   * Saves vote to database
   * @operation Vote direction ('-' or '+')
   * @count Number of votes to save
   */
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

  /*
   * Logs vote for user
   * @session_id Client session id
   * @direction Direction of vote
   */
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

  /*
   * Inserts video in playlist and sets vote to 1
   */
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

  /*
   * Inserts video in playlist
   */
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

  /*
   * Sets playing attribute to video
   */
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

  /*
   * Returns video information in JSON String
   * returns JSON
   */
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
