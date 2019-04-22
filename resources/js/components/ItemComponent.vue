<template>
  <div class="box columns" :class="{ 'is-playing': playing }">
    <div class="column is-one-fifth">
      <figure class="image is-4by3">
        <img :src="img" alt="Image">
      </figure>
    </div>
    <div class="column is-three-fifth">
      <p class="is-large">
        <strong>{{ title }}</strong>
      </p>
    </div>
    <div class="column is-one-fifth vote-column">
      <a class="level-item" v-on:click="$emit('upvote')">
        <span class="icon is-medium" :id="id + '_up'">
          <i class="fas fa-arrow-alt-circle-up fa-2x" aria-hidden="true"></i>
        </span>
      </a>
      <span class="is-size-3 vote-number" :id="'votes_' + id">{{ votecount }}</span>
      <a class="level-item" v-on:click="$emit('downvote')">
        <span class="icon is-medium " :id="id + '_down'">
          <i class="fas fa-arrow-alt-circle-down fa-2x" aria-hidden="true"></i>
        </span>
      </a>
    </div>
    {{ id }}   {{ vote }}
  </div>
</template>
<script>
  export default {
    computed: {
    },
    methods: {
    },
    props: ['id', 'title', 'img', 'playing', 'votecount', 'vote', 'position'],
    filters: {
    }
  }
</script>
