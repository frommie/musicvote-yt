<template>
  <div id="section-search" class="control has-icons-left">
    <input class="input is-medium" type="text" v-model="query" v-on:keyup.enter="search" placeholder="Search">
    <span class="icon is-left">
      <i class="fas fa-search"></i>
    </span>
  </div>
</template>
<script>
  export default {
    computed: {
    },
    methods: {
      search: function(e) {
        window.axios.post('/api/search', { query: this.query }).then(({ data }) => {
          this.result = data;
          this.$modal.show({
            template: `
              <div class="modal is-active">
                <div class="modal-background"></div>
                <div class="modal-card">
                  <header class="modal-card-head">
                    <p class="modal-card-title">Suche</p>
                    <button class="delete" @click="$emit('close')"></button>
                  </header>
                  <section class="modal-card-body">
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
                  </section>
                </div>
              </div>
            `,
            props: ['results']
          }, {
            results: data
          }, {
            height: '100%'
          }, {
          });
        });
      },
      add(id) {
        window.axios.post(`/api/vote/up/${id}`).then(() => {
          // Once AJAX resolves we can update the Crud with the new color
          // update playlist
          this.read();
        });
      },
    },
    props: ['query'],
    filters: {
    }
  }
</script>
