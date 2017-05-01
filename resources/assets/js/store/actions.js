import * as types from './mutation-types'

export const updateHosts = ({
    commit
}, hosts) => {
    commit(types.HOSTS, hosts)
}

export const updateAppMode = ({
    commit
}, mode) => {
    localStorage.setItem('theme', mode);
    commit(types.APPMODE, mode);
}

export const updateHomeSearch = ({
    commit
}, mode) => {
    localStorage.setItem('homeSearch', mode);
    commit(types.HOMESEARCH, mode);
}
