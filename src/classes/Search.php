<?php

class Search {
  protected $api;
  protected $query;

  public function __construct($query) {
    // construct API
    $this->query = $query;
  }

  public function get_title() {
    // retrieve title from youtube
  }
}
