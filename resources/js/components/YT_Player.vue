<template>
  <div style="position: fixed;left: 0;top: 0;">
    <youtube :video-id="item_id" :player-vars="playerVars" :width=playerWidth :height=playerHeight @ended="get_next_item"></youtube>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        item_id: '',
        playerVars: {
          autoplay: 1
        },
        playerWidth: window.innerWidth,
        playerHeight: window.innerHeight
      }
    },
    methods: {
      get_first_item() {
        let self = this;
        window.axios.get('/api/first').then(function (response) {
          self.item_id = response.data;
        });
      },
      get_next_item() {
        window.axios.get('/api/next').then(function (response) {
          this.set_next_item(response.data);
        });
      },
    },
    created() {
      this.get_first_item();
      //this.player.playVideo();
      let es = new EventSource('/api/playcontrol');
      es.addEventListener('message', event => {
        //let data = JSON.parse(event.data);
        if (event.data == "skip") {
          this.get_next_item();
        }
      }, false);

      es.addEventListener('error', event => {
        if (event.readyState == EventSource.CLOSED) {
          console.log('Event was closed');
          console.log(EventSource);
        }
      }, false);
    },
    computed: {
      player() {
        return this.$refs.youtube.player
      }
    }
  }
</script>
