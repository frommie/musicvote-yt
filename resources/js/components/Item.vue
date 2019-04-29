<template>
  <div class="box columns item-box is-mobile" :class="{ 'is-playing': playing }" :style="{ backgroundImage: 'url(\'' + img + '\')' }" v-on="{ click: parent === 'search' ? add : nothing }">
    <div class="column item-title">
      <p class="is-large">
        <strong>{{ title }}</strong>
      </p>
    </div>
    <div v-if="parent !== 'search'" class="column is-one-fifth vote-column">
      <a class="level-item" v-on:click="$emit('upvote')">
        <span class="icon is-medium" :id="item_id + '_up'">
          <i class="fas fa-arrow-alt-circle-up fa-2x" aria-hidden="true"></i>
        </span>
      </a>
      <span class="is-size-3 vote-number" :id="'votes_' + item_id">{{ votecount }}</span>
      <a class="level-item" v-on:click="$emit('downvote')">
        <span class="icon is-medium " :id="item_id + '_down'">
          <i class="fas fa-arrow-alt-circle-down fa-2x" aria-hidden="true"></i>
        </span>
      </a>
    </div>
    <div v-else class="column is-one-fifth add-column">
      <a class="addbutton">
        <span class="icon is-medium">
          <i class="fas fa-plus-circle fa-2x" aria-hidden="true"></i>
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
      add() {
        this.$emit('add');
      },
      nothing() {
        // non-function to suppress warning
      },
    },
    props: ['item_id', 'title', 'img', 'playing', 'votecount', 'vote', 'position', 'parent'],
    filters: {
    }
  }
</script>
