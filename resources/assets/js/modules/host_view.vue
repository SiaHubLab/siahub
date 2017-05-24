<template>
<div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"  v-html="used_percent()">
                    #{{hostData.id}}
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p>Wallet Version: {{hostData.version}}</p>
                            <p v-if="walletOutdated()" class="alert alert-danger">Wallet is outdated, don't forget to upgrade your wallet to 1.2.2</p>
                            <p>Price: {{price()}}</p>
                            <p><span class="label label-info" style="font-size: 100%;">Last success check: {{moment.utc(hostData.last_seen*1000).format('DD/MM/YY HH:mm z')}}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <viewer :fields="fields" :data="hostData"></viewer>
        </div>
    </div>

    <div v-if="chartData" class="row">
        <div class="col-md-12">
              <highstock :options="chartData" ref="highcharts"></highstock>
        </div>
    </div>
    <br />
    <div class="row">
        <div class="col-md-6">
            <viewer :fields="allFields" :data="hostData"></viewer>
        </div>
        <div class="col-md-6">
            <mapPoint :host="hostData.id"></mapPoint>
        </div>
    </div>
    <br />
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
        appMode(){
            return this.$store.getters.appMode;
        },
        chartData(){
            if(typeof this.hostData.history !== "object") return false;
            var that = this;

            var options = {
                chart: {
                    type: "area",
                    backgroundColor: (this.appMode !== "night-mode") ? "#fff":"#252525",
                },
                rangeSelector: {
                    buttonTheme: { // styles for the buttons
                        fill: 'none',
                        stroke: 'none',
                        'stroke-width': 0,
                        r: 8,
                        style: {
                            color: (this.appMode !== "night-mode") ? "#039":"#dadada",
                            fontWeight: 'bold'
                        },
                        states: {
                            hover: {
                            },
                            select: {
                                fill: (this.appMode !== "night-mode") ? "#039":"#dadada",
                                style: {
                                    color: 'white'
                                }
                            }
                            // disabled: { ... }
                        }
                    },
                    inputBoxBorderColor: 'gray',
                    inputBoxWidth: 120,
                    inputBoxHeight: 18,
                    inputStyle: {
                        color: (this.appMode !== "night-mode") ? "#039":"#dadada",
                        fontWeight: 'bold'
                    },
                    labelStyle: {
                        color: 'silver',
                        fontWeight: 'bold'
                    },
                    buttons: [
                        { type : 'week', count : 1, text : '1w' },
                        { type : 'month', count : 1, text : '1m' },
                        { type : 'month', count : 3, text : '3m' },
                        { type : 'month', count : 6, text : '6m' },
                        { type : 'year', count : 1, text : '1y' },
                        { type : 'all', count : 1, text : 'All' }
                    ], selected : 1,
                },
                scrollbar: { liveRedraw: false, enabled: false },
                credits: { enabled: false },
                tooltip: {
                    xDateFormat: "%Y-%m-%d %H:%M UTC"
                },
                xAxis: {
                    type: 'datetime'
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
                }, {
                    title: {text: 'Used'},
                    labels: { format: '{value:.0f} GB/day' },
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
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: entry.totalstorage/1000/1000/1000,
                        };
                    }),
                    tooltip: { valueSuffix: ' GB' },
                     yAxis: 0,
                     color: (this.appMode !== "night-mode") ? "#0000ff":"#3cb6f1",
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color((this.appMode !== "night-mode") ? "#0000ff":"#3cb6f1").setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color((this.appMode !== "night-mode") ? "#0000ff":"#3cb6f1").setOpacity(0).get('rgba')]
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
                    yAxis: 2,
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
            if(this.loading) return false;

            this.error = false;
            this.loading = true;
            axios.get('/api/host/'+this.$route.params.id)
                .then((response) => {
                    this.hostData = response.data;
                    this.loading = false;

                    for(var i in this.hostData){
                        if(i === "history") continue;

                        var formatter = false;

                        if(i == 'score') {
                            formatter = function(str, entry){
                                var scores = JSON.parse(entry.score);
                                var resp = "";
                                for(var z in scores){
                                    resp += "<p>"+z+": "+scores[z]+"</p>"
                                }
                                return resp;
                            };
                        }

                        if(i == 'contractprice' || i == 'maxcollateral') {
                            formatter = function(str, entry){
                                return (Math.round(parseInt(str)/1e24)) + ' SC';
                            };
                        }

                        if(i == 'storageprice' || i == 'collateral') {
                            formatter = function(str, entry){
                                return (Math.round(parseInt(str)/1e12*4320)) + ' SC'
                            };
                        }

                        if(i == 'downloadbandwidthprice' || i == 'uploadbandwidthprice') {
                            formatter = function(str, entry){
                                return (Math.round(parseInt(str)/1e12)) + ' SC'
                            };
                        }

                        this.$set(this.allFields, i, {
                            type: 'text',
                            name: i,
                            key: i,
                            formatter: formatter
                        });
                    }
                })
                .catch((error) => {
                    this.error = error.response.data;
                    this.loading = false;
                });
        },
        walletOutdated: function(){
            if(!this.hostData) return false;
            return versionCompare('1.2.2', this.hostData.version) === 1;
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
            <div class="progress-bar" style="background-color: '+color+';width:15%;">'+percent+'% ('+ this.used() +' / '+ this.totalstorage() +')</div>\
            </div>';
        },
    },
    data() {
        return {
            loading: false,
            error: false,
            moment: window.moment,
            hostData: {},
            allFields: {},
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
                historicuptime: {
                    type: 'text',
                    name: 'Uptime',
                    key: 'historicuptime',
                    formatter: function(str, entry){
                        if(parseInt(entry.historicuptime) > 0) {
                            var uptime = entry.historicuptime/1000000;
                            var rate = 100;
                            if(parseInt(entry.historicdowntime) > 0) {
                                rate = 100-(entry.historicdowntime/entry.historicuptime*100).toFixed(2);
                            }
                            var time = (+new Date())-uptime;
                            return window.moment(time).toNow(true)+' - '+rate+'%'+'<br>*Uptime/downtime data collected locally by SiaHub wallet and may not provide exact information.';
                        } else {
                            return 'not enough data collected';
                        }
                    }
                },
                historicdowntime: {
                    type: 'text',
                    name: 'Downtime',
                    key: 'historicdowntime',
                    formatter: function(str, entry){
                        if(parseInt(entry.historicdowntime) > 0) {
                            var uptime = entry.historicdowntime/1000000;
                            var time = (+new Date())-uptime;
                            return window.moment(time).toNow(true);
                        } else {
                            return "-";
                        }
                    }
                },
                score: {
                    type: 'text',
                    name: 'Score',
                    key: 'score',
                    formatter: function(str, entry){
                        var scores = JSON.parse(entry.score);
                        var score = _.reduce(scores, function(score, val, key){
                             if(key === 'score') return score;

                             return score*Big(val).toFixed(16);
                         }, 1);
                        return Big(score).toFixed(16)+' - Rank #'+entry.rank;
                    }
                }
            }
        };
    }
}
</script>
