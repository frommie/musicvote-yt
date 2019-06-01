<?php

namespace App;

/*
 * Manages Youtube API connection
 */
class SpotifyAPI {
  protected $service;

  /*
   * Constructor
   * @db PDO connection
   */
  public function __construct() {
    $this->session = new \SpotifyWebAPI\Session(
      config('services.spotify.clientid'),
      config('services.spotify.clientsecret'),
      route('spotify.cb')
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

  public static function api() {
    $api = new \SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken(\App\Conf::get('sp_access_token'));
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
    $this->store_credentials($credentials);
  }

  private function store_credentials($credentials) {
    $conf = \App\Conf::set('sp_access_token', $credentials['access_token']);
    $conf->save();
    $conf = \App\Conf::set('sp_refresh_token', $credentials['refresh_token']);
    $conf->save();
    $conf = \App\Conf::set('service', 'spotify');
    $conf->save();
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

  public static function search($query) {
    $search = self::api()->search($query, 'track', [
      'market' => 'DE'
    ]);
    $results = array();
    foreach ($search->tracks->items as $item) {
      $artist_name = "";
      foreach ($item->artists as $artist) {
        $artist_name = $artist->name . " ft ";
      }
      $artist_name = substr($artist_name, 0, -4);
      $result_item = \App\Item::FirstOrCreate(['id' => $item->id, 'title' => $artist_name . " - " . $item->name, 'img' => $item->album->images[0]->url]);
      array_push($results, $result_item);
    }
    return $results;
  }
}
