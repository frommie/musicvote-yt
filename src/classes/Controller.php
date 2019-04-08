<?php

/*
 * Controller sends query entries to client sessions with proper type
 */
class Controller {
  protected $db;
  protected $session_id;
  protected $client_type;

  /*
   * Constructor
   * @db PDO connection
   * @session_id Client session id
   */
  public function __construct($db, $session_id) {
    $this->db = $db;
    $this->session_id = $session_id;
    $this->client_type = $this->get_client_type();
  }

  /*
   * Returns client type for given session id
   * returns client type (client / player)
   */
  public function get_client_type() {
    $sql = "SELECT client_type FROM clients WHERE session_id = :session_id LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["session_id" => $this->session_id]);
    $result = $stmt->fetchAll()[0];
    $stmt->closeCursor();
    if (!empty($result)) {
      return $result['client_type'];
    } else {
      return;
    }
  }

  /*
   * Gets event for current session id
   * returns SSE event
   */
  public function get_event() {
    $query = new Query($this->db, $this->session_id);
    $event = $query->get_queried_event();
    if ($event) {
      return "data: " . $event;
    } else {
      return;
    }
  }

  /*
   * Cleans up session database
   * TODO
   */
  public function cleanup() {
    // last_activity older than 60 minutes
    // clean clients
    // clean query
  }
}
