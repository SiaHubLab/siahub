export const state = {
    hosts: JSON.parse('[]'),
    appMode: localStorage.getItem('theme')
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
}
