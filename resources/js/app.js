require('./bootstrap');
import Vue from 'vue'
import 'bulma/css/bulma.css';

import VueRouter from 'vue-router';
import VModal from 'vue-js-modal';

import { library } from '@fortawesome/fontawesome-svg-core'
import { faThumbsUp, faThumbsDown, faPlusCircle } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faThumbsUp, faThumbsDown, faPlusCircle)

Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.config.performance = true;

Vue.use(VueRouter);
Vue.use(VModal, { dynamic: true, injectModalsContainer: true });

import App from './components/App.vue'
import Home from './components/Home.vue'
const Foo = { template: '<div>foo</div>' }

const router = new VueRouter({
  mode: 'history',
  routes: [
    { path: '/foo', component: Foo },
    { path: '/', component: Home }
  ]
})

const app = new Vue({
  el: '#app',
  components: { App },
  router,
})
