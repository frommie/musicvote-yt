<?php

/*
 * Holds session information, sets client type (client / player), logs activity
 */
class Client {
  protected $db;
  protected $client_type;
  protected $session_id;

  /*
   * Constructor
   * @db PDO connection
   * @client_type Type of client ('client' or 'player')
   * @session_id session_id()
   */
  public function __construct($db, $client_type, $session_id) {
    $this->db = $db;
    $this->client_type = $client_type;
    $this->session_id = $session_id;
  }

  /*
   * Logs user in, checks if already registered, if so it logs activity, else register
   */
  public function login() {
    // check if already in db
    $registered = $this->registered();
    if ($registered) {
      // log activity
      $this->log_activity();
    } else {
      $this->register();
    }
  }

  /*
   * Returns true if session_id is already registered in database
   * returns True if session is in table clients
   */
  public function registered() {
    $sql = 'SELECT client_type FROM clients WHERE session_id = :session_id';
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['session_id' => $this->session_id]);
    $result = $stmt->fetch();
    $stmt->closeCursor();
    if (!empty($result)) {
      return true;
    } else {
      return false;
    }
  }

  /*
   * Registers client if not in clients table yet with client type
   */
  public function register() {
    $sql = 'INSERT INTO clients (session_id, client_type, last_activity) VALUES (:session_id, :client_type, :curr_time)';
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      'session_id' => $this->session_id,
      'client_type' => $this->client_type,
      'curr_time' => date('Y-m-d H:i:s')
    ]);
  }

  /*
   * Logs activity if logged in and already registered, updates client type
   */
  public function log_activity() {
    if ($this->registered()) {
      $sql = 'UPDATE clients SET client_type = :client_type, last_activity = :curr_time WHERE session_id = :session_id';
      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        'client_type' => $this->client_type,
        'curr_time' => date('Y-m-d H:i:s'),
        'session_id' => $this->session_id
      ]);
    } else {
      $this->register();
    }
  }

  /*
   * Static function to get current active sessions
   * returns Current active session ids
   */
  public static function get_sessions($db, $client_type) {
    $sql = 'SELECT session_id FROM clients WHERE client_type = :client_type';
    $stmt = $db->prepare($sql);
    $stmt->execute(['client_type' => $client_type]);
    $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $ret;
  }
}
