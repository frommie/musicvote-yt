<?php

class YoutubeAPI {
  protected $service;

  public function __construct() {
    $client = $this->getClient();
    $service = new Google_Service_YouTube($client);
    $this->service = $service;
  }

  public function getClient() {
    $client = new Google_Client();
    $client->setAuthConfigFile('../client_secret.json');
    $client->setRedirectUri('http://music.test');
    $client->setApprovalPrompt('force');
    $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = '../php-yt-oauth2.json';
    if (file_exists($credentialsPath)) {
      $accessToken = file_get_contents($credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
      $client->refreshToken($client->getRefreshToken());
      file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
  }

  public function test() {
    return $this->channelsListByUsername($this->service, 'snippet,contentDetails,statistics', array('forUsername' => 'GoogleDevelopers'));
  }

  public function channelsListByUsername($service, $part, $params) {
      $params = array_filter($params);
      $response = $service->channels->listChannels(
          $part,
          $params
      );
      return json_encode($response);
  }

  public function search($query) {
    $search = $this->service->search->listSearch('snippet', array('maxResults' => 24, 'q' => $query, 'type' => ''))['items'];
    $arr = array();
    foreach ($search as $item) {
      $cleaned_item = array();
      $cleaned_item['video_id'] = $item['id']['videoId'];
      $cleaned_item['title'] = html_entity_decode($item['snippet']['title']);
      $cleaned_item['img'] = $item['snippet']['thumbnails']['high']['url'];
      array_push($arr, $cleaned_item);
    }
    return json_encode($arr);
  }

  //channelsListByUsername($service, 'snippet,contentDetails,statistics', array('forUsername' => 'GoogleDevelopers'));
}
