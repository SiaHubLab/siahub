export const state = {
    hosts: JSON.parse('[]'),
    appMode: localStorage.getItem('theme'),
    homeSearch: localStorage.getItem('homeSearch')
}

export const mutations = {
    hostsLoad(state, {
        hosts
    }) {
        state.hosts = hosts
    },
    appModeChange(state, {
        mode
    }) {
        state.appMode = mode
    },
    homeSearchChange(state, {
        mode
    }) {
        state.appMode = mode
    },
}
