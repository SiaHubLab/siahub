require('./utils');
require('./bootstrap');

window.moment = require('moment');
window.Big = require('big.js');

import Toasted from 'vue-toasted';
import VueRouter from 'vue-router';
import store from './store';
import VueHighcharts from 'vue-highcharts';
import Highcharts from 'highcharts/highstock';
import {
    mapGetters
} from 'vuex'

Vue.directive('tooltip', function(el, binding) {
    $(el).attr('data-toggle', 'tooltip')
        .attr('data-placement', binding.arg)
        .attr('trigger', 'hover').tooltip({
            'title': binding.value
        });
});

Vue.component('loader', require('./components/loader.vue'));
Vue.component('numberChangeSnippet', require('./components/numberChangeSnippet.vue'));
Vue.component('grid', require('./components/grid.vue'));
Vue.component('viewer', require('./components/viewer.vue'));
Vue.component('stats', require('./modules/stats.vue'));
Vue.component('theme', require('./modules/theme.vue'));
Vue.component('wallet', require('./components/wallet.vue'));
Vue.component('mapPoint', require('./components/map.vue'));
Vue.component('ads', require('./components/ads.vue'));

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
const network = require('./modules/network.vue');

const router = new VueRouter({
    mode: 'history',
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
        },
        {
            path: '/network',
            component: network
        }
    ]
})

const BaseVue = Vue.extend({
    router
});

var divs = document.querySelectorAll('.app');

[].forEach.call(divs, function(div) {
    // do whatever
    new BaseVue({
        el: div,
        store,
        computed: mapGetters(['appMode']),
        data() {
            return {
                appstyle: this.appMode
            }
        }
    });
});
