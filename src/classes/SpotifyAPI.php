<?php

/*
 * Manages Youtube API connection
 */
class SpotifyAPI {
  protected $service;
  protected $db;

  /*
   * Constructor
   * @db PDO connection
   */
  public function __construct($db) {
    $this->db = $db;
    $this->credentials = $this->get_credentials();
    $this->session = new SpotifyWebAPI\Session(
      $this->credentials['client_id'],
      $this->credentials['client_secret'],
      $this->credentials['redirect_uri']
    );

    $this->options = [
      'scope' => [
        'user-read-playback-state',
        'user-read-currently-playing',
        'user-modify-playback-state',
        'playlist-modify-private',
        'playlist-read-private',
        'streaming',
      ],
    ];
  }

  public function get_api() {
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $credentials = $this->get_credentials();
    // Fetch the saved access token from somewhere. A database for example.
    $api->setAccessToken($credentials['access_token']);
    return $api;
  }

  public function auth() {
    return $this->session->getAuthorizeUrl($this->options);
  }

  public function callback($code) {
    $this->session->requestAccessToken($code);
    $credentials = array(
      'access_token' => $this->session->getAccessToken(),
      'refresh_token' => $this->session->getRefreshToken()
    );
    // $this->store_credentials($credentials); // TODO
    return $credentials;
  }

  private function get_credentials() {
    $config_file = dirname(__FILE__) . '/../../config.php';
    if (is_readable($config_file)) {
      require $config_file;
      return array(
        'client_id' => $config['spotify']['client_id'],
        'client_secret' => $config['spotify']['client_secret'],
        'redirect_uri' => $config['spotify']['redirect_uri'],
        'access_token' => $config['spotify']['access_token'],
        'refresh_token' => $config['spotify']['refresh_token'],
      );
    }
    if (getenv('API_KEY') !== false) {
      return getenv('API_KEY');
    }
  }
}
