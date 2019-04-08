$(document).ready(function(){
  get_playlist();
  $("#search").click(function(){
    search();
  });
  $("#search_query").keypress(function (e) {
    if (e.which == 13) {
      search();
      return false;
    }
  });
});

var source = new EventSource("/playcontrol");
source.onmessage = function(event) {
  if (event.data == "voted" || event.data == "next") {
    update_playlist();
  }
};

function get_playlist() {
  $.get("/playlist", function(data) {
    document.getElementById("playlist").innerHTML = construct_playlist(JSON.parse(data));
    update_user_votes();
  });
}

function update_playlist() {
  $.get("/playlist", function(data) {
    document.getElementById("playlist").innerHTML = construct_playlist(JSON.parse(data));
  });
}

function construct_playlist(data) {
  var html = document.createElement("div");
  data.forEach(function(video) {
    if (video.direction == "+") {
      video.direction_up = 1;
    }
    if (video.direction == "-") {
      video.direction_down = 1;
    }
    const box_markup = `
    <div class="box columns ${video.playing ? 'is-playing' : 'is-playlist-item'}">
      <div class="column is-one-fifth">
        <figure class="image is-4by3">
          <img src="${video.img}" alt="Image">
        </figure>
      </div>
      <div class="column is-three-fifth">
        <p class="is-large">
          <strong>${video.title}</strong>
        </p>
      </div>
      <div class="column is-one-fifth vote-column">
        <a class="level-item" onClick="vote('${video.video_id}', '+')">
          <span class="icon is-medium ${video.direction_up ? 'has-text-success' : 'has-text-grey-lighter'}" id="${video.video_id}_up">
            <i class="fas fa-arrow-alt-circle-up fa-2x" aria-hidden="true"></i>
          </span>
        </a>
        <span class="is-size-3 vote-number" id="votes_${video.video_id}">${video.votes}</span>
        <a class="level-item" onClick="vote('${video.video_id}', '-')">
          <span class="icon is-medium ${video.direction_down ? 'has-text-danger' : 'has-text-grey-lighter'}" id="${video.video_id}_down">
            <i class="fas fa-arrow-alt-circle-down fa-2x" aria-hidden="true"></i>
          </span>
        </a>
      </div>
    </div>
    `;
    html.innerHTML += box_markup;
  });
  return html.outerHTML;
}

function update_user_votes() {
  $.get("/get_user_votes", function(data) {
    set_user_votes(JSON.parse(data));
  });
}

function set_user_votes(user_votes) {
  if (user_votes.length > 0) {
    user_votes.forEach(function (user_vote) {
      if (user_vote['direction'] == "+") {
        if (document.getElementById(user_vote['video_id'] + "_up") !== null) {
          document.getElementById(user_vote['video_id'] + "_up").className = "icon is-medium has-text-success";
        }
      } else {
        if (document.getElementById(user_vote['video_id'] + "_down") !== null) {
          document.getElementById(user_vote['video_id'] + "_down").className = "icon is-medium has-text-danger";
        }
      }
    });
  }
}

function search() {
  $.post("/search", {
    query: $("#search_query").val()
  },
  function(data, status){
    document.getElementById("search_result").innerHTML = "";
    document.getElementById("search-modal").className = "modal is-active";

    document.getElementById("search_result").appendChild(show_result(JSON.parse(data)));
  });
}

function show_result(data) {
  var ret_html = document.createElement("div");

  var new_column = document.createElement("div");

  for (let i = 0; i < data.length; i++) {
    ret_html.appendChild(get_box(data[i]));
  }

  return ret_html;
}

function close_search_modal() {
  document.getElementById("search-modal").className = "modal";
}

function get_box(vid) {
  // calculate duration
  var sec_num = parseInt(vid['duration'], 10); // don't forget the second param
  var hours   = Math.floor(sec_num / 3600);
  var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
  var seconds = sec_num - (hours * 3600) - (minutes * 60);

  if (seconds < 10) {seconds = "0"+seconds;}

  if (hours > 0) {
    var duration = hours+':'+minutes+':'+seconds;
    if (minutes < 10) {minutes = "0"+minutes;}
  } else {
    var duration = minutes+':'+seconds;
  }

  var div_box = document.createElement("div");
  div_box.className = "box columns";
  div_box.setAttribute("style", "margin: 10px;");
  // TODO path_for cant be used here so the link to vote is a thing to improve
  const box_markup = `
    <div class="column">
      <figure class="image is-4by3">
        <a onClick="add('${vid.video_id}')"><img src="${vid.img}" alt="Image"></a>
      </figure>
    </div>
    <div class="column is-half">
      <p class="is-large">
        <strong>${vid.title}</strong>
      </p>
    </div>
    <div class="column">
      <p>${duration}</p>
    </div>
    <div class="column is-one-fifth">
      <nav class="level is-mobile">
        <div class="level-left">
          <a class="level-item" onClick="add('${vid.video_id}')" aria-label="reply">
            <span class="icon is-medium" id="${vid.video_id}_add">
              <i class="fas fa-plus-circle fa-2x" aria-hidden="true"></i>
            </span>
          </a>
        </div>
      </nav>
    </div>
  `;
  div_box.innerHTML = box_markup;
  return div_box;
}

function add(video_id) {
  document.getElementById(video_id + "_add").className = "icon is-medium has-text-success";
  vote(video_id, '+');
}

function vote(video_id, direction) {
  $.post("/vote", {
    video_id: video_id,
    direction: direction
  },
  function(data, status){
    // update vote number
    if (data == "") {
      data = 0;
    }
    if (document.getElementById("votes_" + video_id) !== null) {
      document.getElementById("votes_" + video_id).innerHTML = data;
      if (direction == "+") {
        document.getElementById(video_id + "_up").className = "icon is-medium has-text-success";
        document.getElementById(video_id + "_down").className = "icon is-medium has-text-grey-lighter";
      } else {
        document.getElementById(video_id + "_up").className = "icon is-medium has-text-grey-lighter";
        document.getElementById(video_id + "_down").className = "icon is-medium has-text-danger";
      }
    }
  });
}
