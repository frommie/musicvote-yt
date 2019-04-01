<?php

class Controller {
  protected $db;

  public function __construct($db) {
    $this->db = $db;
  }

  public function get_event() {
    $sql = "SELECT votes FROM playlist WHERE playing = 1";
    $stmt = $this->db->query($sql);
    $votes = $stmt->fetch()['votes'];
    if ($votes < 0) {
      return "data: skip";
    }
    return;
  }
}
