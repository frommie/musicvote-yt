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
      <a class="level-item" onClick="">
        <span class="icon is-medium" :id="video_id + '_up'">
          <i class="fas fa-arrow-alt-circle-up fa-2x" aria-hidden="true"></i>
        </span>
      </a>
      <span class="is-size-3 vote-number" :id="'votes_' + video_id">{{ votecount }}</span>
      <a class="level-item" onClick="">
        <span class="icon is-medium " :id="video_id + '_down'">
          <i class="fas fa-arrow-alt-circle-down fa-2x" aria-hidden="true"></i>
        </span>
      </a>
    </div>
    {{ video_id }}   {{ vote }}
  </div>
</template>
<script>
  export default {
    computed: {
    },
    methods: {
    },
    props: ['video_id', 'title', 'img', 'playing', 'votecount', 'vote'],
    filters: {
    }
  }
</script>
