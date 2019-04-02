<?php

class Query {
  protected $db;
  protected $session_id;

  public function __construct($db, $session_id) {
    $this->db = $db;
    $this->session_id = $session_id;
  }

  public function get_queried_event() {
    $sql = "SELECT event_type FROM query WHERE session_id = :session_id LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["session_id" => $this->session_id]);
    if ($stmt->rowCount() > 0) {
      $queried_event = $stmt->fetch()['event_type'];
      $this->delete_queried_event($queried_event);
      return $queried_event;
    } else {
      return;
    }
  }

  public function delete_queried_event($event_type) {
    $sql = "DELETE FROM query WHERE session_id = :session_id AND event_type = :event_type";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "session_id" => $this->session_id,
      "event_type" => $event_type
    ]);
  }
}
