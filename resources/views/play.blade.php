<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <style>
  html, body {
    margin: 0;
    height: 100%;
    width: 100%;
  }
  </style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script>
  var source = new EventSource("/api/playcontrol");
  source.onmessage = function(event) {
    if (event.data == "skip") {
      get_next_video();
    }
  };

  var tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  var player;

  function onYouTubeIframeAPIReady(video_id) {
    if (video_id === undefined) {
      video_id = '{{ $video_id }}';
    }
    console.log("play " + video_id);
    player = new YT.Player('player', {
      height: '800',
      width: '640',
      videoId: video_id,
      playerVars: { 'autoplay': 1},
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      }
    });
  }

  function onPlayerReady(event) {
    event.target.setVolume(100);
    event.target.playVideo();
  }

  function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.ENDED) {
      get_next_video();
    }
  }

  function get_next_video() {
    console.log("next");
    // get next video
    $.get("/api/next", {
    },
    function(data, status){
      player.destroy();
      onYouTubeIframeAPIReady(data);
    });
  }

  onYouTubeIframeAPIReady('{{ $video_id }}');
  </script>
</head>
<body style="">
  <div id="player" style="width:100%; height:100%;"></div>
</body>
</html>
