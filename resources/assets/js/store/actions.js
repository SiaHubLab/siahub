import * as types from './mutation-types'

export const updateHosts = ({
    commit
}, hosts) => {
    commit(types.HOSTS, hosts)
}
