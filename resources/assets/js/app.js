require('./utils');
require('./bootstrap');

window.moment = require('moment');

import Toasted from 'vue-toasted';
import VueRouter from 'vue-router';
import store from './store';
import VueHighcharts from 'vue-highcharts';
import Highcharts from 'highcharts/highstock';

Vue.directive('tooltip', function(el, binding) {
    $(el).attr('data-toggle', 'tooltip')
        //.attr('title', binding.value)
        .attr('data-placement', binding.arg)
        .attr('trigger', 'hover').tooltip({
            'title': binding.value
        });
});

Vue.component('grid', require('./components/grid.vue'));
Vue.component('viewer', require('./components/viewer.vue'));
Vue.component('stats', require('./modules/stats.vue'));
Vue.component('wallet', require('./components/wallet.vue'));

Vue.use(Toasted, {
    theme: "primary",
    position: "bottom-right",
    duration: 2000
});
Vue.use(VueRouter);
Vue.use(VueHighcharts, {
    Highcharts
});


const home = require('./modules/home.vue');
const host_view = require('./modules/host_view.vue');
const map = require('./modules/map.vue');

const router = new VueRouter({
    mode: 'hash',
    base: __dirname,
    routes: [{
            path: '/',
            component: home
        },
        {
            path: '/host/:id',
            component: host_view
        },
        {
            path: '/map',
            component: map
        }
    ]
})

const BaseVue = Vue.extend({
    router
});


document.querySelectorAll('.app').forEach((node) => {
    new BaseVue({
        el: node,
        store
    });
});
