<?php
session_start();

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

$app->post('/vote', function ($request, $response) {
  // create event for clients
  $body = $request->getParsedBody();
  $video_id = $body['video_id'];
  $direction = $body['direction'];
  $video = Video::with_video_id($this->db, $video_id);
  $video->vote(session_id(), $direction);
  new Event($this->db, 'voted', 'client');
  $votes = $video->get_votes();
  if ($video->is_playing() && $votes < 0) {
    // create event for player
    new Event($this->db, 'skip', 'player');
  }
  print_r($votes);
});

$app->post('/search', function ($request, $response) {
  $search_query = $request->getParsedBody()['query'];
  //$response = new Search($search_query);
  $api = new YoutubeAPI($this->db);
  $content = $api->search($search_query);

  print_r($content);
});

$app->get('/get_all_votes', function ($request, $response) {
  $votes = new Votes($this->db, session_id());
  print_r(json_encode($votes->get_all_votes()));
});

$app->get('/get_user_votes', function ($request, $response) {
  $votes = new Votes($this->db, session_id());
  print_r(json_encode($votes->get_user_votes()));
});

$app->get('/play', function ($request, $response) {
  $playlist = new Playlist($this->db);
  try {
    print($playlist->get_top_video());
  } catch (PlaylistEmptyException $e) {
    print("Empty playlist");
  }
});

$app->get('/next', function ($request, $response) {
  $playlist = new Playlist($this->db);
  // create event for clients
  try {
    $playlist->remove_playing_video();
    new Event($this->db, 'next', 'client');
    print($playlist->get_top_video());
  } catch (PlaylistEmptyException $e) {
    print("Empty playlist");
  }
});

$app->get('/playlist', function ($request, $response) {
  $session_id = session_id();
  $playlist = new Playlist($this->db);

  print_r($playlist->get_playlist($session_id));
});

$app->get('/test', function ($request, $response) {
  $pl = new Playlist($this->db);
  print_r($pl->pr());
});

$app->get('/playcontrol', function ($request, $response) {
  $session_id = session_id();
  $controller = new Controller($this->db, $session_id);
  $body = $response->getBody();
  $body->write($controller->get_event()."\n\n");

  return $response
    ->withHeader('Content-Type', 'text/event-stream')
    ->withHeader('Cache-Control', 'no-cache')
    ->withBody($body);
});

$app->get('/player', function ($request, $response) {
  // register player
  $session_id = session_id();
  $client = new Client($this->db, "player", $session_id);
  $client->login();

  $playlist = new Playlist($this->db);
  $response = $this->view->render($response, 'player.html', [
    'video_id' => $playlist->get_top_video()
  ]);

  return $response;
});

$app->get('/', function ($request, $response) {
  $session_id = session_id();
  $client = new Client($this->db, "client", $session_id);
  $client->login();

  $response = $this->view->render($response, 'list.html');

  return $response;
});

$app->run();
