<?php

class Event {
  protected $db;

  public function __construct($db, $event_type, $client_type) {
    $this->db = $db;
    // get all clients where client_type
    $sessions = Client::get_sessions($this->db, $client_type);
    foreach ($sessions as $session) {
      $this->enqueue($session['session_id'], $event_type);
    }
  }

  public function enqueue($session_id, $event_type) {
    if (!$this->queried($session_id, $event_type)) {
      $sql = "INSERT IGNORE INTO query (session_id, event_type) VALUES (:session_id, :event_type)";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        'session_id' => $session_id,
        'event_type' => $event_type
      ]);
    }
  }

  public function queried($session_id, $event_type) {
    $sql = "SELECT * FROM query WHERE session_id = :session_id AND event_type = :event_type";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "session_id" => $session_id,
      "event_type" => $event_type
    ]);
    if ($stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }
}
