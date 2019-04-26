<template>
  <div class="box columns item-box is-mobile" :class="{ 'is-playing': playing }" :style="{ backgroundImage: 'url(\'' + img + '\')' }">
    <div class="column item-title">
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
