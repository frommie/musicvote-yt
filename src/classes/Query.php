<?php

/*
 * Manages events query
 */
class Query {
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
   * Gets queried event for client session id
   * returns Queried event, empty if no event queried
   */
  public function get_queried_event() {
    $sql = 'SELECT event_type FROM query WHERE session_id = :session_id LIMIT 1';
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['session_id' => $this->session_id]);
    $result = $stmt->fetchAll();
    $stmt->closeCursor();
    if ($result) {
      $result = $result[0];
      $queried_event = $result['event_type'];
      $this->delete_queried_event($queried_event);
      return $queried_event;
    } else {
      return;
    }
  }

  /*
   * Deletes queried event if event was send to client previously
   */
  public function delete_queried_event($event_type) {
    $sql = 'DELETE FROM query WHERE session_id = :session_id AND event_type = :event_type';
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      'session_id' => $this->session_id,
      'event_type' => $event_type
    ]);
  }
}
