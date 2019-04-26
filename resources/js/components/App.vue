<template>
  <div class="container">
    <search-component></search-component>
    <playlist-component></playlist-component>
  </div>
</template>

<script>
  import SearchComponent from './Search.vue';
  import PlaylistComponent from './Playlist.vue';

  export default {
    data() {
    },
    methods: {
    },
    created() {
    },
    components: {
      'playlist-component': PlaylistComponent,
      'search-component': SearchComponent
    }
  }
</script>
