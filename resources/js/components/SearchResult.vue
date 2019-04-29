<template>
  <div class="modal is-active">
    <div class="modal-background" @click="$emit('close')"></div>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Suche</p>
        <button class="delete" @click="$emit('close')"></button>
      </header>
      <section class="modal-card-body">
        <item-component
          v-for="item in results"
          v-bind="item"
          class="search-item"
          parent="search"
          :key="item.id"
          v-on:add="add(item.id)"
        ></item-component>
      </section>
    </div>
  </div>
</template>

<script>
  import ItemComponent from './Item.vue';

  export default {
    props: ['results'],
    methods: {
      add(id) {
        window.axios.post(`/api/vote/up/${id}`);
      },
    },
    components: {
      'item-component': ItemComponent
    }
  }
</script>

<style scoped>
.search-item {
  display: inline-block;
}
</style>
