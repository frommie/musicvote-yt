<template>
  <div id="section-search" class="control has-icons-left">
    <input class="input is-medium" type="text" v-model="query" v-on:keyup.enter="search" placeholder="Search">
    <span class="icon is-left">
      <i class="fas fa-search"></i>
    </span>
  </div>
</template>
<script>
  function Item(item) {
    this.id = item.detail.id;
    this.title = item.detail.title;
    this.img = item.detail.img;
    this.playing = item.playing;
    this.votecount = item.votecount;
    this.vote = item.vote;
  }

  import SearchResult from './SearchResult.vue';

  export default {
    data() {
      return {
        results: [],
        query: "",
      }
    },
    computed: {
    },
    methods: {
      search: function(e) {
        window.axios.post('/api/search', { query: this.query }).then(({ data }) => {
          this.$modal.show(SearchResult, {
            results: data
          });
          this.query = "";
        });
      },
      add(id) {
        window.axios.post(`/api/vote/up/${id}`).then(() => {
          // update playlist
          this.read();
        });
      },
    },
  }
</script>
