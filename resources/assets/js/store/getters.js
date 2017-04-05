export const hosts = state => {
    return state.hosts
}
export const getHostById = (state, getters) => (id) => {
    return state.hosts.find(host => host.id === parseInt(id));
}
