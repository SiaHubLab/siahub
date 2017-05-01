import Vue from 'vue'
import Vuex from 'vuex'
import * as actions from './actions'
import * as getters from './getters'
import * as types from './mutation-types'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        hosts: [],
        appMode: localStorage.getItem('theme'),
        homeSearch: localStorage.getItem('homeSearch')
    },
    actions,
    getters,
    mutations: {
        [types.HOSTS](state, hosts) {
            state.hosts = hosts;
        },
        [types.APPMODE](state, mode) {
            state.appMode = mode;
        },
        [types.HOMESEARCH](state, mode) {
            state.homeSearch = mode;
        }
    }
})
