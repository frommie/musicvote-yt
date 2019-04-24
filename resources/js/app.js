
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import 'bulma/css/bulma.css';

import VModal from 'vue-js-modal';


window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('playlist-component', require('./components/App.vue').default);
Vue.use(VModal, { dynamic: true });

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    data: {
      event: null
    },
    created() {
      this.setup_stream();
    },
    methods: {
      setup_stream() {
        let es = new EventSource('/api/playcontrol');
        es.addEventListener('message', event => {
          //let data = JSON.parse(event.data);
          this.event = event;
        }, false);

        es.addEventListener('error', event => {
          if (event.readyState == EventSource.CLOSED) {
            console.log('Event was closed');
            console.log(EventSource);
          }
        }, false);
      }
    }
});
