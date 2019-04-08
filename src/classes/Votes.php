<?php

/*
 * Manages votes
 */
class Votes {
  protected $db;
  protected $session_id;

  /*
   * Constructor
   * @db PDO connection
   * @session_id Client session id
   */
  public function __construct($db, $session_id) {
    $this->db = $db;
    $this->session_id = $session_id;
  }

  /*
   * Gets votes by user / client session id
   * returns voted video ids and directions for user
   */
  public function get_user_votes() {
    $sql = "SELECT video_id, direction FROM votes WHERE session_id = :session_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["session_id" => $this->session_id]);
    $votes = $stmt->fetchAll();
    $stmt->closeCursor();
    return $votes;
  }

  /*
   * Gets all votes for playlist items
   * returns votes video ids and direction for all users
   */
  public function get_all_votes() {
    $sql = "SELECT video_id, votes FROM playlist";
    $stmt = $this->db->query($sql);
    $arr = $stmt->fetchAll();
    $stmt->closeCursor();
    /*
    foreach ($pdo->query($sql) as $row) {
    }*/
    return $arr;
  }
}
