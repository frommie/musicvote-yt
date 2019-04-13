<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script>
  var source = new EventSource("/playcontrol");
  source.onmessage = function(event) {
    console.log("test");
    console.log(event.data);
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
    if (video_id == null) {
      video_id = '{{ video_id }}';
    }
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
    // get next video
    $.get("/next", {
    },
    function(data, status){
      player.destroy();
      onYouTubeIframeAPIReady(data);
    });
  }
  </script>
</head>
<body style="margin: 0;">
  <div id="player" style="width:100%; height:100%;"></div>
</body>
</html>
