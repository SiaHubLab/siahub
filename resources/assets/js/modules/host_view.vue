<template>
<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            {{hostData.key}}
            <span class="pull-right label label-info" style="font-size: 100%;">Last seen: {{moment.utc(hostData.last_seen*1000).format('dddd, MMMM Do YYYY, HH:mm:ss z')}}</span>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    Storage: {{totalstorage()}}
                </div>
                <div class="col-md-3">
                    Used: {{used()}}
                </div>
                <div class="col-md-3" v-html="used_percent()">
                </div>
                <div class="col-md-3">
                    Price: {{price()}}
                </div>
            </div>
        </div>
    </div>
    <div v-if="chartData" class="row">
        <div class="col-md-12">
              <highstock :options="chartData" ref="highcharts"></highstock>
        </div>
    </div>
    <br />
    <!-- <br /> -->
    <div class="row">
        <div class="col-md-12">
            <viewer :fields="fields" :data="hostData"></viewer>
        </div>
    </div>

    <div v-show="error" class="alert alert-danger">
        <p><strong>Oops!</strong> Error, {{error}}</p>
        <p><button class="btn btn-primary" @click.prevent="refresh()">Try again</button></p>
    </div>
</div>
</template>

<script>
import Highcharts from 'highcharts';
import { mapGetters } from 'vuex'

export default {
    mounted() {
        Highcharts.setOptions({
            global: {
                useUTC: true,
                timezoneOffset: 0
            }
        });

        this.refresh();
    },
    computed: {
        getHostById(){
            return this.$store.getters.getHostById;
        },
        chartData(){
            if(typeof this.hostData.history !== "object") return false;

            // var data = [];
            // data = this.hostData.history.map((entry) => {
            //     return {
            //         date: entry.created_at,
            //         e: (((entry.storageprice/1e12*4320)*((entry.totalstorage-entry.remainingstorage)/1000/1000/1000/1000))/30).toFixed(2),
            //         s: (entry.totalstorage/1000/1000/1000).toFixed(2),
            //         u: ((entry.totalstorage-entry.remainingstorage)/1000/1000/1000).toFixed(2)
            //     };
            // });
            //return data;
            var options = {
                chart: { type: "area" },
                rangeSelector: {
                    buttons: [
                        { type : 'week', count : 1, text : '1w' },
                        { type : 'month', count : 1, text : '1m' },
                        { type : 'month', count : 3, text : '3m' },
                        { type : 'month', count : 6, text : '6m' },
                        { type : 'year', count : 1, text : '1y' },
                        { type : 'all', count : 1, text : 'All' }
                    ], selected : 1,
                },
                scrollbar: { enabled: false },
                credits: { enabled: false },
                tooltip: {
                    xDateFormat: "%Y-%m-%d %H:%M UTC"
                },
                xAxis: {
                     type: 'datetime',
                },
                yAxis: [{
                    title: {text: 'Storage'},
                    labels: { format: '{value:.2f} GB' },
                    min: 0
                }, {
                    title: {text: 'Earnings'},
                    labels: { format: '{value:.0f} SC/day' },
                    min: 0,
                    opposite: false
                }],
                plotOptions: {
                    area: { fillColor: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 } } }
                },
                series: [{
                    name: 'Storage',
                    turboThreshold: 0,
                    data: this.hostData.history.map((entry) => {
                        console.log(+ new Date(entry.created_at), moment.utc(entry.created_at), moment.utc(entry.created_at).format('x'));
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: entry.totalstorage/1000/1000/1000,
                        };
                    }),
                    tooltip: { valueSuffix: ' GB' },
                     yAxis: 0,
                     color: '#0000ff',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#0000ff').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#0000ff').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:blue">{series.name}: <b>{point.y:.2f} GB</b></span><br/>' },
                }, {
                    name: 'Used storage',
                    turboThreshold: 0,
                    data: this.hostData.history.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: (entry.totalstorage-entry.remainingstorage)/1000/1000/1000
                        };
                    }),
                    tooltip: { valueSuffix: ' GB' },
                    yAxis: 0,
                    color: '#ff0000',
                    fillColor: {
                       stops: [
                           [0, Highcharts.Color('#ff0000').setOpacity(0.3).get('rgba')],
                           [1, Highcharts.Color('#ff0000').setOpacity(0).get('rgba')]
                       ]
                   },
                    tooltip: { pointFormat: '<span style="color:red">{series.name}: <b>{point.y:.2f} GB</b></span><br/>' },
                }, {
                    name: 'Earnings',
                    turboThreshold: 0,
                    data: this.hostData.history.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: ((entry.storageprice/1e12*4320)*((entry.totalstorage-entry.remainingstorage)/1000/1000/1000/1000))/30
                        };
                    }),
                    tooltip: { valueSuffix: ' SC/day' },
                    yAxis: 1,
                    color: '#00ff00',
                    fillColor: 'transparent',
                    tooltip: { pointFormat: '<span style="color:green">{series.name}: <b>{point.y:.2f} SC/day</b></span><br/>' },
                }]
            };
            console.log(Highcharts.getOptions().colors, options);
            return options;
        }
    },
    methods: {
        refresh(){

            // if(this.getHostById(this.$route.params.id)){
            //     this.hostData = this.getHostById(this.$route.params.id);
            //     return true;
            // }

            if(this.loading) return false;

            this.error = false;
            this.loading = true;
            axios.get('/api/host/'+this.$route.params.id)
                .then((response) => {
                    this.hostData = response.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.error = error.response.data;
                    this.loading = false;
                });
        },

        price: function(){
            if(!this.hostData) return 'loading';

            return Math.round(this.hostData.storageprice/1e12*4320)+" SC";
        },
        totalstorage: function(){
            if(!this.hostData) return 'loading';

            return humanFileSize(this.hostData.totalstorage, true);
        },
        used: function(){
            if(!this.hostData) return 'loading';

            var val = this.hostData.totalstorage-this.hostData.remainingstorage;

            return humanFileSize(val, true);
        },
        used_percent: function(){
            if(!this.hostData) return 'loading';

            var percent = Math.round((this.hostData.totalstorage-this.hostData.remainingstorage)/this.hostData.totalstorage*100);
            if(isNaN(percent)) return ':(';

            var color_num = Math.round(percent/100*255);
            var color = "rgba("+color_num+", "+ Math.max(0, 150 - color_num) +", 0, 0.7)"
            return '<div class="progress">\
            <div class="progress-bar" style="background-color: '+color+';width:'+(percent-15)+'%;"></div>\
            <div class="progress-bar" style="background-color: '+color+';width:15%;">'+percent+'%</div>\
            </div>';
        },
    },
    data() {
        return {
            loading: false,
            error: false,
            moment: window.moment,
            hostData: {},
            fields: {
                key: {
                    type: 'text',
                    name: 'Key',
                    key: 'key'
                },
                algorithm: {
                    type: 'text',
                    name: 'Algorithm',
                    key: 'algorithm'
                },
                host: {
                    type: 'text',
                    name: 'Host',
                    key: 'host'
                },
            }
        };
    }
}
</script>
