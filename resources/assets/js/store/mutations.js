export const state = {
    hosts: JSON.parse('[]')
}

export const mutations = {
    hostsLoad(state, {
        hosts
    }) {
        state.hosts = hosts
    },
}
