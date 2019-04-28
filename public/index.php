<?php
session_start();
$session_id = session_id();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require_once '../src/classes/Database.php';

$config_file = '../config.php';
if (is_readable($config_file)) {
  require $config_file;
}

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$db = new Database;
$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['logger'] = function($c) {
  $logger = new \Monolog\Logger('my_logger');
  $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
  $logger->pushHandler($file_handler);
  return $logger;
};
$container['db'] = $db->get_connection();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates', [
    //'cache' => '../cache'
      'debug' => true
    ]);
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new Slim\Views\TwigExtension($router, $uri));
    $view->addExtension(new \Twig\Extension\DebugExtension());
    return $view;
};

/*
 * Controller for playback, used in client and player
 * returns SSE events
 */
$app->get('/playcontrol', function ($request, $response) {
  global $session_id;
  $controller = new Controller($this->db, $session_id);
  $event = $controller->get_event();
  $body = "";
  if ($event) {
    $body = "data: {$event}\n\n"; // keep double quotes!
  }
  return $response
    ->withHeader('Content-Type', 'text/event-stream')
    ->withHeader('Cache-Control', 'no-cache')
    ->write($body);
});

/*
 * Gets all votes set by current user using session id
 * returns Video IDs in playlist with votes by current user as JSON
 */
$app->get('/get_user_votes', function ($request, $response) {
  global $session_id;
  $votes = new Votes($this->db, $session_id);
  print_r(json_encode($votes->get_user_votes()));
});

/*
 * Selects next video in playlist and removes top video
 * returns 2nd top video as top video gets removed
 */
$app->get('/next', function ($request, $response) {
  $playlist = new Playlist($this->db);
  // create event for clients
  try {
    $playlist->remove_playing_video();
    new Event($this->db, 'next', 'client');
    print($playlist->get_top_video());
  } catch (PlaylistEmptyException $e) {
    print('Empty playlist');
  }
});

/*
 * Gets the current playlist
 * returns Playlist
 */
$app->get('/playlist', function ($request, $response) {
  global $session_id;
  $playlist = new Playlist($this->db);
  print_r($playlist->get_playlist($session_id));
});

/*
 * Search route to get search results
 * @query Search query
 * returns Search array
 */
$app->post('/search', function ($request, $response) {
  $search_query = $request->getParsedBody()['query'];
  //$response = new Search($search_query);
  $api = new YoutubeAPI($this->db);
  $content = $api->search($search_query);
  print_r($content);
});

/*
 * Vote route to set a vote to a video id
 * @video_id Video ID to set a vote to
 * @direction Direction of vote (either '+' or '-')
 * returns Number of votes for voted video
 */
$app->post('/vote', function ($request, $response) {
  // create event for clients
  global $session_id;
  $body = $request->getParsedBody();
  $video_id = $body['video_id'];
  $direction = $body['direction'];
  $video = Video::with_video_id($this->db, $video_id);
  $video->vote($session_id, $direction);
  $votes = $video->get_votes();
  if ($votes < 0) {
    if ($video->is_playing()) {
     // create event for player
     new Event($this->db, 'skip', 'player');
   } else {
     // remove from playlist
     $playlist = new Playlist($this->db);
     $playlist->remove($video_id);
   }
  }
  new Event($this->db, 'voted', 'client');
  print_r($votes);
});

/*
 * Player site to show the videos in the playlist
 * returns player template
 */
$app->get('/player', function ($request, $response) {
  // register player
  global $session_id;
  $client = new Client($this->db, 'player', $session_id);
  $client->login();
  $playlist = new Playlist($this->db);
  $response = $this->view->render($response, 'player.html', [
    'video_id' => $playlist->get_top_video()
  ]);
  return $response;
});

/*
 * Client site to show the current playlist and search function
 * returns client template
 */
$app->get('/', function ($request, $response) {
  global $session_id;
  $client = new Client($this->db, 'client', $session_id);
  $client->login();
  $response = $this->view->render($response, 'client.html');
  return $response;
});

$app->run();
