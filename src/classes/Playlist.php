<?php

class Playlist {
  protected $db;

  public function __construct($db) {
    $this->db = $db;
  }

  public function add_track($track) {
    // insert track to db
  }

  public function get_list() {
    // retrieve list from db
  }
}
