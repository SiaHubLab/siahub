<template>
<div>
    <div class="row">
        <div class="col-md-4">
            <p><input class="form-control" v-model="searchQuery" placeholder="Search"></p>

            <button type="button" class="btn btn-info" @click.pevent="refresh()">Update list</button>

            <div class="btn-group" role="group">
              <button type="button" :class="'btn '+((mode == 'active')?'btn-success active':'btn-default')" @click.pevent="mode='active'">Active hosts</button>
              <button type="button" :class="'btn '+((mode == 'all')?'btn-primary active':'btn-default')" @click.pevent="mode='all'">All hosts</button>
              <button type="button" :class="'btn '+((mode == 'latest')?'btn-warning active':'btn-default')" @click.pevent="mode='latest'">Latest 10 hosts</button>
            </div>
        </div>
    </div>
    <br />
    <grid v-show="!loading && !error" :defaultSort="sortKey" defaultSortOrder="-1" :data="hosts" :columns="gridColumns" :formatters="formatters" :filter-key="searchQuery">1
    </grid>

    <div v-show="error" class="alert alert-danger">
        <p><strong>Oops!</strong> Error, {{error}}</p>
        <p><button class="btn btn-primary" @click.prevent="refresh()">Try again</button></p>
    </div>
</div>
</template>

<script>
import { updateHosts } from '../store/actions';
import { mapGetters, mapActions } from 'vuex'

export default {
    mounted() {
        this.refresh();
        this.searchQuery = this.homeSearch;
    },
    watch: {
        mode: function() {
            this.refresh()
        },
        searchQuery: function(){
            this.updateSearch(this.searchQuery);
        }
    },
    computed: mapGetters(['hosts', 'homeSearch']),
    methods: {
        updateHosts(hosts) {
          this.$store.dispatch('updateHosts', hosts);
        },
        updateSearch(searchString) {
            if(searchString !== null) {
                this.$store.dispatch('updateHomeSearch', searchString);
            }
        },
        refresh(){
            if(this.loading) return false;

            this.error = false;
            this.loading = true;
            axios.get('/api/hosts/'+this.mode)
                .then((response) => {
                    this.updateHosts(response.data);
                    this.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                    this.error = error.response.data;
                    this.loading = false;
                });
        },
        totalStorage: function(raw){
            if(!this.hosts) return 'loading';
            var result = this.hosts.reduce(function(a, b){
                        return a + b.totalstorage;
                    }, 0);

            return (raw) ? result:humanFileSize(result, true);
        },
        totalRemainingStorage: function(raw){
            if(!this.hosts) return 'loading';
            var result = this.hosts.reduce(function(a, b){
                        return a + b.remainingstorage;
                    }, 0);

            return (raw) ? result:humanFileSize(result, true);
        },
        averagePrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = this.hosts.reduce(function(a, b){
                        return a + Math.round(b.storageprice/1e12*4320);
                    }, 0);

            return Math.round(result/this.hosts.length);
        },
        minPrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = Math.min.apply(null, this.hosts.map(function(item) {
                return item.storageprice;
            }));

            return Math.round(result/1e12*4320);
        },
        maxPrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = Math.max.apply(null, this.hosts.map(function(item) {
                return item.storageprice;
            }));

            return Math.round(result/1e12*4320);
        },
        utilization: function(){
            if(!this.hosts) return 'loading';

            var utilization = this.totalStorage(true)-this.totalRemainingStorage(true);
            var percent = Math.round(utilization/this.totalStorage(true)*100);

            var color_num = Math.round(percent/100*255);
            var color = "rgba("+color_num+", "+ Math.max(0, 150 - color_num) +", 0, 0.7)"
            return '<div class="progress">\
            <div class="progress-bar" style="background-color: '+color+';width:'+(percent-15)+'%;"></div>\
            <div class="progress-bar" style="background-color: '+color+';width:15%;">'+percent+'%</div>\
            <div class="progress-bar" style="color: #000;background-color: transparent;width:'+(100-15-percent)+'%">'+humanFileSize(utilization, true)+'</div>\
            </div>';
        }
    },
    data() {
        return {
            loading: false,
            error: false,
            searchQuery: this.homeSearch,
            mode: 'active',
            sortKey: 'used',
            gridColumns: ['score', 'host', 'totalstorage', 'used', 'used_percent', 'price', 'actions'],
            formatters: {
                actions: function(str, entry){
                    return {
                        view: '/host/'+entry.id
                    }
                },
                score: function(str, entry, raw){
                    var scale = (raw) ? 30:8;
                    try {
                        var scores = JSON.parse(entry.score);
                        var score = _.reduce(scores, function(score, val, key){
                            if(key === 'score') return score;

                            return score*Big(val).toFixed(scale);
                         }, 1);
                        return Big(score).toFixed(scale)+' / #'+entry.rank;
                    } catch(err) {
                        return 0;
                    }
                },
                host: function(str, entry, raw){
                    return entry.netaddress;
                },
                price: function(str, entry, raw){
                    if(raw) return Math.round(entry.storageprice/1e12*4320);

                    return Math.round(entry.storageprice/1e12*4320)+" SC";
                },
                totalstorage: function(str, entry, raw){
                    if(raw) return parseInt(entry.totalstorage);

                    return humanFileSize(entry.totalstorage, true);
                },
                used: function(str, entry, raw){
                    var val = entry.totalstorage-entry.remainingstorage;
                    if(raw) return val;

                    return humanFileSize(val, true);
                },
                used_percent: function(str, entry, raw){
                    var percent = Math.round((entry.totalstorage-entry.remainingstorage)/entry.totalstorage*100);
                    if(isNaN(percent)) return ':(';
                    if(raw) return percent;

                    var color_num = Math.round(percent/100*255);
                    var color = "rgba("+color_num+", "+ Math.max(0, 150 - color_num) +", 0, 0.7)"
                    return '<div class="progress">\
                    <div class="progress-bar" style="background-color: '+color+';width:'+(percent-15)+'%;"></div>\
                    <div class="progress-bar" style="background-color: '+color+';width:15%;">'+percent+'%</div>\
                    </div>';
                },
            }
        };
    }
}
</script>
