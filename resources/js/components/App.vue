<template>
  <div id="app">
    <div class="heading">
      <input v-model="query" v-on:keyup.enter="search" placeholder="Search" />
    </div>
    <div>
      <modals-container
        v-on:add="add($event)"
      />
      <item-component
        v-for="item in playlist"
        v-bind="item"
        class="moving-item"
        v-bind:style="{ top: ((item.position + 1) * 150) + 'px' }"
        :key="item.video_id"
        v-on:upvote="update(item.id, 1)"
        v-on:downvote="update(item.id, -1)"
        @update="update"
        @delete="del"
      ></item-component>
    </div>
  </div>
</template>

<script>
  function Item(item) {
    this.id = item.detail.id;
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
        playlist: [],
        query: "",
        result: [],
      }
    },
    methods: {
      create() {
        // To do
      },
      add(id) {
        this.update(id, 1);
      },
      search: function(e) {
        window.axios.post('/api/search', { query: this.query }).then(({ data }) => {
          this.result = data;
          this.$modal.show({
            template: `
              <div>
                <h1>Ergebnisse</h1>
                <div v-for="result in results">
                  <div class="columns" v-on:click="$emit('add', result.id)">
                    <div class="column">
                      <img :src="result.img_url" />
                    </div>
                    <div class="column">
                      <p>{{ result.title }}</p>
                    </div>
                  </div>
                </div>
              </div>
            `,
            props: ['results']
          }, {
            results: data
          }, {
            height: 'auto'
          }, {
            'before-close': (event) => { console.log('this will be called before the modal closes'); }
          });
        });
      },
      read() {
        window.axios.get('/api/playlist').then(({ data }) => {
          data.forEach(item => {
            if (this.playlist.find(pitem => pitem.id === item.video_id)) {
              this.playlist.find(pitem => pitem.id === item.video_id).votecount = item.votecount;
            } else {
              this.playlist.push(new Item(item));
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
      update(id, vote) {
        vote = parseInt(vote);
        window.axios.post(`/api/vote/${id}`, { vote }).then(() => {
          // Once AJAX resolves we can update the Crud with the new color
          // update playlist
          this.read();
          this.playlist.find(item => item.id === id).vote = vote;
        });
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

<style scoped>
.moving-item {
  transition: all 1s ease;
  -webkit-transition: all 1s ease;
  position: absolute;
}
</style>
