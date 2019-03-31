<?php
session_start();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require_once '../src/classes/Database.php';

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
  $body = $request->getParsedBody();
  $video_id = $body['video_id'];
  $direction = $body['direction'];
  $video = Video::with_video_id($this->db, $video_id);
  $video->vote($direction);
  print_r($video->get_votes());
});

$app->post('/search', function ($request, $response) {
  $search_query = $request->getParsedBody()['query'];
  //$response = new Search($search_query);
  $api = new YoutubeAPI($this->db);
  $content = $api->search($search_query);

  print_r($content);
});

$app->get('/player', function ($request, $response) {
  $playlist = new Playlist($this->db);
  $response = $this->view->render($response, 'player.html', [
    'video_id' => $playlist->get_top_video()
  ]);

  return $response;
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
  try {
    print($playlist->remove_playing_video());
  } catch (PlaylistEmptyException $e) {
    print("Empty playlist");
  }
});

$app->get('/playlist', function ($request, $response) {
  $playlist = new Playlist($this->db);

  print_r($playlist->get_playlist());
});

$app->get('/', function ($request, $response) {
  $playlist = new Playlist($this->db);

  $response = $this->view->render($response, 'list.html', [
    'playlist' => $playlist->get_playlist()
  ]);

  return $response;
});

/*
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
  $name = $args['name'];
  $response->getBody()->write("Hello, $name");
  $this->logger->addInfo('Hello: ' . $name);

  return $response;
});

$app->get('/posts', function (Request $request, Response $response) {
  $this->logger->addInfo("Post list");
  $mapper = new PostMapper($this->db);
  $posts = $mapper->getPosts();
  $response = $this->view->render($response, 'posts.html', ['posts' => $posts, 'router' => $this->router]);
  return $response;
});

$app->get('/post/new', function (Request $request, Response $response) {
  $response = $this->view->render($response, 'addpost.html', []);
  return $response;
});

$app->post('/post/new', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  $post_data = [];
  $post_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
  $post_data['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);
  $post = new PostEntity($post_data);
  $mapper = new PostMapper($this->db);
  $mapper->save($post);
});

$app->get('/post/{id}', function (Request $request, Response $response, $args) {
  $post_id = (int)$args['id'];
  $mapper = new PostMapper($this->db);
  $post = $mapper->getPostById($post_id);
  $this->logger->addInfo(print_r($mapper->getPostById(1), true));
  $response = $this->view->render($response, 'postdetail.html', ['post' => $post, 'router' => $this->router]);
  return $response;
})->setName('post-detail');
*/

$app->run();
