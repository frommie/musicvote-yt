<template>
  <div id="app">
    <div class="heading">
      <item-component
        v-for="item in playlist"
        v-bind="item"
        :key="item.video_id"
        @update="update"
        @delete="del"
      ></item-component>
    </div>
    <div>
      <button @click="create()">Add</button>
    </div>
  </div>
</template>

<script>
  function Item(item) {
    this.video_id = item.detail.id;
    this.title = item.detail.title;
    this.img = item.detail.img_url;
    this.playing = item.playing;
    this.votecount = item.votecount;
    this.vote = item.vote;
  }

  import ItemComponent from './ItemComponent.vue';

  export default {
    data() {
      return {
        playlist: []
      }
    },
    methods: {
      create() {
        // To do
      },
      read() {
        window.axios.get('/api/playlist').then(({ data }) => {
          data.forEach(item => {
            this.playlist.push(new Item(item));
          });
        });
      },
      update(id, color) {
        // To do
      },
      del(id) {
        // To do
      }
    },
    created() {
      this.read();
    },
    components: {
      ItemComponent
    }
  }
</script>
