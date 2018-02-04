<template>
<ul class="nav navbar-nav navbar-right">
    <li><router-link to="/network" v-tooltip:bottom="'Storage Utilization'"><span v-html="utilization()"></span></router-link></li>
    <li><router-link to="/network" v-tooltip:bottom="'$/TB/month median price'">Avg Price: ${{averagePrice()}}/{{scPrice()}} SC</router-link></li>
    <li><router-link to="/network" v-tooltip:bottom="'CoinMarketCap Price'">{{ticker.price_btc}} BTC / {{parseFloat(ticker.price_usd).toFixed(4)}}$</router-link></li>
</ul>
</template>

<script>
import { updateHosts } from '../store/actions';
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
    mounted() {
        this.refresh();

        setInterval(this.refresh, 60000);
    },
    computed: mapGetters(['hosts']),
    watch: {
        ticker(){
            this.updateTitle();
        },
        hosts(){
            this.updateTitle();
        }
    },
    methods: {
        updateTitle(){
            document.title = '$'+this.averagePrice()+'/TB | '+this.ticker.price_btc;
        },
        updateHosts(hosts) {
          this.$store.dispatch('updateHosts', hosts);
        },
        refresh(){
            if(this.loading) return false;

            this.error = false;
            this.loading = true;
            axios.get('/api/sia/ticker')
                .then((response) => {
                    this.ticker = response.data;
                    this.loading = false;
                    _.delay(this.loadHosts, 3000);
                })
                .catch((error) => {
                    console.log(error);
                    this.error = error.response.data;
                    this.loading = false;
                });
        },
        loadHosts(){
            if(this.hosts.length > 0) return;

            this.error = false;
            this.loading = true;
            axios.get('/api/hosts/active')
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
                        return a + parseInt(b.totalstorage);
                    }, 0);

            return (raw) ? result:humanFileSize(result, true);
        },
        totalRemainingStorage: function(raw){
            if(!this.hosts) return 'loading';
            var result = this.hosts.reduce(function(a, b){
                        return a + parseInt(b.remainingstorage);
                    }, 0);

            return (raw) ? result:humanFileSize(result, true);
        },
        averagePrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = this.hosts.map(function(b){
                        return (Math.round(parseInt(b.storageprice)/1e12*4320));
                    }, 0);

            return (getMedian(result)*this.ticker.price_usd).toFixed(2);
        },
        scPrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = this.hosts.map(function(b){
                        return (Math.round(parseInt(b.storageprice)/1e12*4320));
                    }, 0);

            return getMedian(result);
        },
        minPrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = Math.min.apply(null, _.filter(this.hosts, (o) => o.storageprice > 0).map(function(item) {
                return item.storageprice;
            }));

            return (result/1e12*4320*this.ticker.price_usd).toFixed(2);
        },
        maxPrice: function(raw){
            if(!this.hosts) return 'loading';
            var result = Math.max.apply(null, _.filter(this.hosts, (o) => o.storageprice > 0).map(function(item) {
                return item.storageprice;
            }));

            return (result/1e12*4320*this.ticker.price_usd).toFixed(2);
        },
        utilization: function(){
            if(!this.hosts) return 'loading';

            var utilization = this.totalStorage(true)-this.totalRemainingStorage(true);
            var percent = Math.round(utilization/this.totalStorage(true)*100);

            var color_num = Math.round(percent/100*255);
            var color = "rgba("+color_num+", "+ Math.max(0, 150 - color_num) +", 0, 0.7)";

            $('[data-toggle="tooltip"]').tooltip();

            return '<div class="progress" style="width:120px;">\
            <div class="progress-bar" style="background-color: '+color+';width:'+(percent-15)+'%;"></div>\
            <div class="progress-bar" style="background-color: '+color+';width:15%;">'+percent+'%</div>\
            <div class="progress-bar" style="color: #000;background-color: transparent;width:'+(100-15-percent)+'%">'+humanFileSize(utilization, true)+' / '+this.totalStorage()+'</div>\
            </div>';
        }
    },
    data() {
        return {
            loading: false,
            error: false,
            ticker: {price_btc: 0, price_usd: 0}
        };
    }
}
</script>
