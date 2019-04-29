<template>
  <div id="section-playlist">
    <item-component
      v-for="item in playlist"
      v-bind="item"
      class="playlist-item moving-item"
      v-bind:style="{ top: 80 + (item.position * 190) + 'px' }"
      :key="item.item_id"
      v-on:upvote="upvote(item.item_id)"
      v-on:downvote="downvote(item.item_id)"
    ></item-component>
  </div>
</template>

<script>
  function Item(item) {
    this.item_id = item.detail.id;
    this.title = item.detail.title;
    this.img = item.detail.img;
    this.playing = item.playing;
    this.votecount = item.votecount;
    this.vote = item.vote;
  }

  import ItemComponent from './Item.vue';

  export default {
    data() {
      return {
        playlist: [],
      }
    },
    methods: {
      read() {
        window.axios.get('/api/playlist').then(({ data }) => {
          var item_ids = [];
          data.forEach(item => {
            item_ids.push(item.item_id);
            // update votecount
            var updated_item = this.playlist.find(pitem => pitem.item_id === item.item_id)
            if (updated_item) {
              if (updated_item.votecount < item.votecount) { // voted up
                document.getElementById(item.item_id + '_up').children[0].className = 'fas fa-arrow-alt-circle-up fa-2x bounce';
                setTimeout(function(){
                  document.getElementById(item.item_id + '_up').children[0].className = 'fas fa-arrow-alt-circle-up fa-2x';
                }, 800);
              }
              if (updated_item.votecount > item.votecount) { // voted down
                document.getElementById(item.item_id + '_down').children[0].className = 'fas fa-arrow-alt-circle-down fa-2x bounce';
                setTimeout(function(){
                  document.getElementById(item.item_id + '_down').children[0].className = 'fas fa-arrow-alt-circle-down fa-2x';
                }, 800);
              }
              updated_item.votecount = item.votecount;
            } else { // item doesnt exist yet - add
              this.playlist.push(new Item(item));
            }
          });
          // remove deleted items
          this.playlist.forEach(function(item, index, playlist_arr) {
            if (!item_ids.includes(item.item_id)) {
              playlist_arr.splice(index, 1);
            }
          });
          this.set_positions();
          this.sort();

        });
      },
      set_positions: function() {
        for (var i = 0; i < this.playlist.length; i++) {
          // TODO
          this.playlist[i].position = i;
        }
      },
      sort() {
        var self = this;
        var newItems = self.playlist.slice().sort(function (a, b) {
          var result;
          if (a.votecount < b.votecount) {
            result = 1
          }
          else if (a.votecount > b.votecount) {
            result = -1
          }
          else {
            result = 0
          }
          return result
        })
        newItems.forEach(function (item, index) {
          item.position = index;
        });
      },
      upvote(id) {
        window.axios.post(`/api/vote/up/${id}`).then(() => {
          // Once AJAX resolves we can update the Crud with the new color
          // update playlist
          this.read();
        });
      },
      downvote(id) {
        window.axios.post(`/api/vote/down/${id}`).then(() => {
          // Once AJAX resolves we can update the Crud with the new color
          // update playlist
          this.read();
        });
      },
    },
    created() {
      let es = new EventSource('/api/playcontrol');
      es.addEventListener('message', event => {
        //let data = JSON.parse(event.data);
        if (event.data == "voted") {
          this.read();
        }
      }, false);

      es.addEventListener('error', event => {
        if (event.readyState == EventSource.CLOSED) {
          console.log('Event was closed');
          console.log(EventSource);
        }
      }, false);
      this.read();
    },
    components: {
      'item-component': ItemComponent
    }
  }
</script>

<style scoped>
.moving-item {
  transition: all 1s ease;
  -webkit-transition: all 1s ease;
  position: absolute;
  left: 0;
}
</style>
