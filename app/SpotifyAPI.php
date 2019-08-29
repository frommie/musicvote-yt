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
    $api = new \SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken(\App\Conf::get('sp_access_token'));
    $this->api = $api;
  }

  private function refresh_token() {
    $session = $this->create_session();
    $session->refreshAccessToken(\App\Conf::get('sp_refresh_token'));
    $access_token = $session->getAccessToken();
    $this->api->setAccessToken($access_token);
    $conf = \App\Conf::set('sp_access_token', $access_token);
  }

  private function create_session() {
    return new \SpotifyWebAPI\Session(
      config('services.spotify.clientid'),
      config('services.spotify.clientsecret'),
      route('spotify.cb')
    );
  }

  public function auth() {
    $session = $this->create_session();
    $options = [
      'scope' => [
        'user-read-playback-state',
        'user-read-currently-playing',
        'user-modify-playback-state',
        'playlist-modify',
        'playlist-modify-private',
        'playlist-read-private',
        'streaming',
      ],
    ];
    return $session->getAuthorizeUrl($options);
  }

  public function callback($code) {
    $session = $this->create_session();
    $session->requestAccessToken($code);
    $credentials = array(
      'access_token' => $session->getAccessToken(),
      'refresh_token' => $session->getRefreshToken()
    );
    $this->store_credentials($credentials);
  }

  private function store_credentials($credentials) {
    \App\Conf::set('sp_access_token', $credentials['access_token']);
    \App\Conf::set('sp_refresh_token', $credentials['refresh_token']);
    // TODO move service option
    \App\Conf::set('service', 'spotify');
  }

  public function search($query) {
    try {
      $search = $this->api->search($query, 'track', [
        'market' => 'DE'
      ]);
    } catch (\Exception $e) {
      if ($e->getCode() == 401) {
        $this->refresh_token();
      }
    } finally {
      $search = $this->api->search($query, 'track', [
        'market' => 'DE'
      ]);
    }

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

  private function get_playlist_id() {
    try {
      $user_id = $this->api->me()->id;
    } catch (\Exception $e) {
      if ($e->getCode() == 401) {
        $this->refresh_token();
      }
    } finally {
      $user_id = $this->api->me()->id;
    }

    // list all playlists and search for playlist with name "MusicVote"
    $playlists = $this->api->getUserPlaylists($user_id)->items;
    foreach ($playlists as $pl) {
      if ($pl->name == "MusicVote") {
        $found = $pl->id;
      }
    }
    if (!isset($found)) {
      // create new playlist
      $found = $this->api->createPlaylist([
        'name' => 'MusicVote'
      ])->id;
    }

    \App\Conf::set('sp_playlist_id', $found);

    return $found;
  }

  public function test() {
    $playlist_id = $this->get_playlist_id();

    // compare and update spotify playlist with current vote list
    $sp_playlist = $this->api->getPlaylist($playlist_id);

    // construct array with IDs and positions
    $items = $sp_playlist->tracks->items;
    $sp_playlist = array();
    for ($i = 0; $i < count($items); $i++) {
      array_push($sp_playlist, array('position' => $i, 'id' => $items[$i]->track->id));
    }

    // get app playlist and construct array
    $mv_raw = \App\Playlist::with('detail')->get();
    $mv_pl = array();

    $mv_playlist = array();
    for ($i = 0; $i < count($mv_raw); $i++) {
      $votecount = $mv_raw[$i]->upvotes - $mv_raw[$i]->downvotes;
      array_push($mv_playlist, array('votes' => $votecount, 'id' => $mv_raw[$i]->item_id));
    }

    // sort by calculated votes
    usort($mv_playlist, function($a, $b) {
      // descending
      return $b['votes'] - $a['votes'];
    });

    // construct array with IDs only and replace tracks
    $new_pl = array();
    foreach ($mv_playlist as $item) {
      array_push($new_pl, $item['id']);
    }

    // TODO: compare playlists and delete, add and reorder
    $ret = $this->api->replacePlaylistTracks($playlist_id, $new_pl);

/*
    // get playlist details
    $mv_playlist = $this->api->getPlaylist($found);
    $tracks = $mv_playlist->tracks->total;
    $snapshot_id = $mv_playlist->snapshot_id;

    if ($tracks > 0) {
      // delete all tracks
      $trackOptions = [
        'positions' => range(0, $tracks-1)
      ];

      $new_snapshot_id = $this->api->deletePlaylistTracks($found, $trackOptions, $snapshot_id);
    }

    // insert current playlist
*/
    return $ret;
  }
}
