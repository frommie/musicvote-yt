<?php

class Controller {
  protected $db;
  protected $session_id;
  protected $client_type;

  public function __construct($db, $session_id) {
    $this->db = $db;
    $this->session_id = $session_id;
    $this->client_type = $this->get_client_type();
  }

  public function get_client_type() {
    $sql = "SELECT client_type FROM clients WHERE session_id = :session_id LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["session_id" => $this->session_id]);
    if ($stmt->rowCount() > 0) {
      return $stmt->fetch()['client_type'];
    } else {
      return;
    }
  }

  public function get_event() {
    $query = new Query($this->db, $this->session_id);
    $event = $query->get_queried_event();
    if ($event) {
      return "data: " . $event;
    } else {
      return;
    }
  }

  public function cleanup() {
    // last_activity older than 60 minutes
    // clean clients
    // clean query
  }
}
