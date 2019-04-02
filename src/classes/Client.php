<?php

class Client {
  protected $db;
  protected $client_type;
  protected $session_id;

  public function __construct($db, $client_type, $session_id) {
    $this->db = $db;
    $this->client_type = $client_type;
    $this->session_id = $session_id;
  }

  public function login() {
    // check if already in db
    if (!$this->registered()) {
      $this->register();
    } else {
      // log activity
      $this->log_activity();
    }
  }

  public function registered() {
    $sql = "SELECT * FROM clients WHERE session_id = :session_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["session_id" => $this->session_id]);
    if ($stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function register() {
    $sql = "INSERT INTO clients (session_id, client_type, last_activity) VALUES (:session_id, :client_type, :curr_time)";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'session_id' => $this->session_id,
      'client_type' => $this->client_type,
      'curr_time' => date("Y-m-d H:i:s")
    ]);
  }

  public function log_activity() {
    if ($this->registered()) {
      $sql = "UPDATE clients SET last_activity = :curr_time";
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        'curr_time' => date("Y-m-d H:i:s")
      ]);
    } else {
      $this->register();
    }
  }

  public static function get_sessions($db, $client_type) {
    $sql = "SELECT session_id FROM clients WHERE client_type = :client_type";
    $stmt = $db->prepare($sql);
    $stmt->execute(["client_type" => $client_type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // TODO clean where current_time - last_activity > 60min
}
