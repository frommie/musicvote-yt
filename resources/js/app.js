require('./bootstrap');
import Vue from 'vue'
import 'bulma/css/bulma.css';

import VueRouter from 'vue-router';
import VModal from 'vue-js-modal';
import VueYoutube from 'vue-youtube'

import { library } from '@fortawesome/fontawesome-svg-core'
import { faThumbsUp, faThumbsDown, faPlusCircle } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faThumbsUp, faThumbsDown, faPlusCircle)

Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.config.performance = true;

Vue.use(VueRouter);
Vue.use(VModal, { dynamic: true, injectModalsContainer: true });
Vue.use(VueYoutube)

import App from './components/App.vue'
import Home from './components/Home.vue'
import Player from './components/Player.vue'

const router = new VueRouter({
  mode: 'history',
  routes: [
    { path: '/play', component: Player },
    { path: '/', component: Home }
  ]
})

const app = new Vue({
  el: '#app',
  components: { App },
  router,
})
