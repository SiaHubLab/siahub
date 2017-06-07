<template>
<div>
    <div class="row">
        <div v-if="hostsChange24hr" class="col-md-2">
            <div :class="'panel '+((hostsChange24hr >= 0) ? 'panel-success':'panel-danger')">
              <div class="panel-heading">
                <h3 class="panel-title" title="Active hosts">Hosts 24h</h3>
              </div>
              <div class="panel-body">
                  <h3 :class="'text-center '+((hostsChange24hr >= 0) ? 'network-change-success':'network-change-danger')">
                      <span v-if="hostsChange24hr >= 0">
                          +{{hostsChange24hr}} <i class="glyphicon glyphicon-chevron-up"></i>
                      </span>
                      <span v-else>
                          {{hostsChange24hr}} <i class="glyphicon glyphicon-chevron-down"></i>
                      </span>
                  </h3>
              </div>
            </div>
        </div>
        <div v-if="hostsChange7d" class="col-md-2">
            <div :class="'panel '+((hostsChange7d >= 0) ? 'panel-success':'panel-danger')">
              <div class="panel-heading">
                <h3 class="panel-title" title="Active hosts">Hosts 7d</h3>
              </div>
              <div class="panel-body">
                  <h3 :class="'text-center '+((hostsChange7d >= 0) ? 'network-change-success':'network-change-danger')">
                      <span v-if="hostsChange7d >= 0">
                          +{{hostsChange7d}} <i class="glyphicon glyphicon-chevron-up"></i>
                      </span>
                      <span v-else>
                          {{hostsChange7d}} <i class="glyphicon glyphicon-chevron-down"></i>
                      </span>
                  </h3>
              </div>
            </div>
        </div>
        <div v-if="storageChange24hr" class="col-md-2">
            <div :class="'panel '+((storageChange24hr >= 0) ? 'panel-success':'panel-danger')">
              <div class="panel-heading">
                <h3 class="panel-title">Storage 24h</h3>
              </div>
              <div class="panel-body">
                  <h3 :class="'text-center '+((storageChange24hr >= 0) ? 'network-change-success':'network-change-danger')">
                      <span v-if="storageChange24hr >= 0">
                          +{{storageChange24hr}} TB <i class="glyphicon glyphicon-chevron-up"></i>
                      </span>
                      <span v-else>
                          {{storageChange24hr}} TB <i class="glyphicon glyphicon-chevron-down"></i>
                      </span>
                  </h3>
              </div>
            </div>
        </div>
        <div v-if="storageChange7d" class="col-md-2">
            <div :class="'panel '+((storageChange7d >= 0) ? 'panel-success':'panel-danger')">
              <div class="panel-heading">
                <h3 class="panel-title">Storage 7d</h3>
              </div>
              <div class="panel-body">
                  <h3 :class="'text-center '+((storageChange7d >= 0) ? 'network-change-success':'network-change-danger')">
                      <span v-if="storageChange7d >= 0">
                          +{{storageChange7d}} TB <i class="glyphicon glyphicon-chevron-up"></i>
                      </span>
                      <span v-else>
                          {{storageChange7d}} TB <i class="glyphicon glyphicon-chevron-down"></i>
                      </span>
                  </h3>
              </div>
            </div>
        </div>

        <div v-if="utilizationChange24hr" class="col-md-2">
            <div :class="'panel '+((utilizationChange24hr >= 0) ? 'panel-success':'panel-danger')">
              <div class="panel-heading">
                <h3 class="panel-title">Utilization 24h</h3>
              </div>
              <div class="panel-body">
                  <h3 :class="'text-center '+((utilizationChange24hr >= 0) ? 'network-change-success':'network-change-danger')">
                      <span v-if="utilizationChange24hr >= 0">
                          +{{utilizationChange24hr}} TB <i class="glyphicon glyphicon-chevron-up"></i>
                      </span>
                      <span v-else>
                          {{utilizationChange24hr}} TB <i class="glyphicon glyphicon-chevron-down"></i>
                      </span>
                  </h3>
              </div>
            </div>
        </div>

        <div v-if="utilizationChange7d" class="col-md-2">
            <div :class="'panel '+((utilizationChange7d >= 0) ? 'panel-success':'panel-danger')">
              <div class="panel-heading">
                <h3 class="panel-title">Utilization 7d</h3>
              </div>
              <div class="panel-body">
                  <h3 :class="'text-center '+((utilizationChange7d >= 0) ? 'network-change-success':'network-change-danger')">
                      <span v-if="utilizationChange7d >= 0">
                          +{{utilizationChange7d}} TB <i class="glyphicon glyphicon-chevron-up"></i>
                      </span>
                      <span v-else>
                          {{utilizationChange7d}} TB <i class="glyphicon glyphicon-chevron-down"></i>
                      </span>
                  </h3>
              </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div v-if="versionsData" class="col-md-3">
              <highcharts :options="versionsData" ref="highcharts"></highcharts>
        </div>
        <div v-if="countryData" class="col-md-6">
              <highcharts :options="countryData" ref="highcharts"></highcharts>
        </div>
        <div v-if="continentData" class="col-md-3">
              <highcharts :options="continentData" ref="highcharts"></highcharts>
        </div>
    </div>

    <br />
    <div class="row">
        <div v-if="hostsData" class="col-md-6">
              <highstock :options="hostsData" ref="highcharts"></highstock>
        </div>
        <div v-if="storageData" class="col-md-6">
              <highstock :options="storageData" ref="highcharts"></highstock>
        </div>
    </div>
    <br />

    <div v-if="priceData" class="row">
        <div class="col-md-12">
              <highstock :options="priceData" ref="highcharts"></highstock>
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
    watch: {
        network(){
            this.storage = [];
            this.used = [];
            this.activeHosts = [];
            this.allHosts = [];
            this.prices = [];

            var dayAgo = moment().subtract(1, 'day');
            var weekAgo = moment().subtract(7, 'day');

            _.forEach(this.network, (entry) => {
                var time = parseInt(moment.utc(entry.created_at).format('x'));
                this.activeHosts.push({x: time, y: entry.active_hosts});
                this.allHosts.push({x: time, y: entry.all_hosts});
                this.prices.push({x: time, y: parseFloat(entry.avg_storageprice)});
                this.storage.push({x: time, y: parseInt(entry.totalstorage)/1000/1000/1000});
                this.used.push({x: time, y: (parseInt(entry.totalstorage)-parseInt(entry.remainingstorage))/1000/1000/1000});

                if(moment(moment.utc(time)).isSameOrBefore(dayAgo)){
                    this.prevHosts24 = entry.active_hosts;
                    this.prevStorage24 = parseInt(entry.totalstorage)/1000/1000/1000/1000;
                    this.prevUtilization24 = (parseInt(entry.totalstorage)-parseInt(entry.remainingstorage))/1000/1000/1000/1000;
                }

                if(moment(moment.utc(time)).isSameOrBefore(weekAgo)){
                    this.prevHosts7d = entry.active_hosts;
                    this.prevStorage7d = parseInt(entry.totalstorage)/1000/1000/1000/1000;
                    this.prevUtilization7d = (parseInt(entry.totalstorage)-parseInt(entry.remainingstorage))/1000/1000/1000/1000;
                }

                this.curHosts = entry.active_hosts;
                this.curStorage = parseInt(entry.totalstorage)/1000/1000/1000/1000;
                this.curUtilization = (parseInt(entry.totalstorage)-parseInt(entry.remainingstorage))/1000/1000/1000/1000;
            });
        }
    },
    computed: {
        appMode(){
            return this.$store.getters.appMode;
        },
        versionsData(){
            if(typeof this.versions !== "object") return false;
            var totalHosts = this.versions.reduce(function(a, b){
                return a + parseInt(b.hosts);
            }, 0);
            var options = _.cloneDeep(this.pieConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff":"#252525",
            options.plotOptions.pie.borderColor = (this.appMode !== "night-mode") ? "#fff":"#dadada",
            options.title.text = 'Sia version';
            options.title.style.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            options.legend.itemStyle.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            options.legend.itemHoverStyle.color = (this.appMode !== "night-mode") ? "#828282":"#dadada";

            options.series = [{
                name: 'Active Hosts',
                colorByPoint: true,
                data: this.versions.map((entry) => {
                    var color_num = Math.round((entry.hosts/totalHosts*100)/100*255);
                    var color = "rgba(0, "+color_num+", "+ Math.max(0, 150 - color_num) +", 0.7)";

                    return {
                        color: color,
                        name: entry.version,
                        y: entry.hosts,
                    };
                }),
            }];
            return options;
        },
        countryData(){
            if(typeof this.countries !== "object") return false;
            var totalHosts = this.countries.reduce(function(a, b){
                return a + parseInt(b.hosts);
            }, 0);

            var coptions = _.cloneDeep(this.pieConfig);
            coptions.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff":"#252525",
            coptions.plotOptions.pie.borderColor = (this.appMode !== "night-mode") ? "#fff":"#dadada",
            coptions.title.text = 'Countries by active hosts';
            coptions.title.style.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            coptions.legend.itemStyle.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            coptions.legend.itemHoverStyle.color = (this.appMode !== "night-mode") ? "#828282":"#dadada";

            coptions.plotOptions.pie.dataLabels = {
                enabled: true,
                color: (this.appMode !== "night-mode") ? "#000":"#dadada",
                borderWidth: 0,
                style: {textOutline: '0'}
            };
            coptions.plotOptions.pie.showInLegend = false;

            coptions.series = [{
                name: 'Active Hosts',
                colorByPoint: true,
                data: this.countries.map((entry) => {
                    var color_num = Math.round((entry.hosts/totalHosts*100)/100*255);
                    var color = "rgba(0, "+color_num+", "+ Math.max(0, 150 - color_num) +", 0.7)";

                    return {
                        color: color,
                        name: entry.country,
                        y: entry.hosts,
                    };
                }),
            }];

            return coptions;
        },
        continentData(){
            if(typeof this.continents !== "object") return false;
            var totalHosts = this.continents.reduce(function(a, b){
                return a + parseInt(b.hosts);
            }, 0);

            var options = _.cloneDeep(this.pieConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff":"#252525",
            options.plotOptions.pie.borderColor = (this.appMode !== "night-mode") ? "#fff":"#dadada",
            options.title.text = 'Continents by active hosts';
            options.title.style.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            options.legend.itemStyle.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            options.legend.itemHoverStyle.color = (this.appMode !== "night-mode") ? "#828282":"#dadada";
            options.series = [{
                name: 'Active Hosts',
                colorByPoint: true,
                data: this.continents.map((entry) => {
                    var color_num = Math.round((entry.hosts/totalHosts*100)/100*255);
                    var color = "rgba(0, "+color_num+", "+ Math.max(0, 150 - color_num) +", 0.7)";

                    return {
                        color: color,
                        name: entry.continent,
                        y: entry.hosts,
                    };
                }),
            }];

            return options;
        },

        hostsChange24hr(){
            if(typeof this.network !== "object") return false;

            var change = this.curHosts-this.prevHosts24;
            return change;
        },
        hostsChange7d(){
            if(typeof this.network !== "object") return false;

            var change = this.curHosts-this.prevHosts7d;
            return change;
        },
        storageChange24hr(){
            if(typeof this.network !== "object") return false;

            var change = this.curStorage-this.prevStorage24;
            return change.toFixed(2);
        },
        storageChange7d(){
            if(typeof this.network !== "object") return false;

            var change = this.curStorage-this.prevStorage7d;
            return change.toFixed(2);
        },
        utilizationChange24hr(){
            if(typeof this.network !== "object") return false;

            var change = this.curUtilization-this.prevUtilization24;
            return change.toFixed(2);
        },
        utilizationChange7d(){
            if(typeof this.network !== "object") return false;

            var change = this.curUtilization-this.prevUtilization7d;
            return change.toFixed(2);
        },
        hostsData(){
            if(typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff":"#252525",
            options.title.text = "Network hosts";
            options.title.style.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            options.rangeSelector = {
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
                }
            };
            options.yAxis = [{
                title: {text: 'Active Hosts'},
                labels: { format: '{value:.2f}' },
                min: 0,
                opposite: false
            }, {
                title: {text: 'Total Hosts'},
                labels: { format: '{value:.0f}' },
                min: 0
            }];

            options.series = [{
                    name: 'Active Hosts',
                    turboThreshold: 0,
                    data: this.activeHosts,
                     yAxis: 0,
                     color: '#00cba0',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#00cba0').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#00cba0').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f}</b></span><br/>' },
                },
                {
                    name: 'Total Hosts',
                    turboThreshold: 0,
                    data: this.allHosts,
                     yAxis: 1,
                     color: '#0069cb',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#0069cb').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#0069cb').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#0069cb">{series.name}: <b>{point.y:.2f}</b></span><br/>' },
                }
            ];

            return options;
        },
        storageData(){
            if(typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff":"#252525",
            options.title.text = "Network storage";
            options.title.style.color = (this.appMode !== "night-mode") ? "#000":"#fff";
            options.rangeSelector = {
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
                }
            };
            options.yAxis = [{
                title: {text: 'Total storage'},
                labels: { format: '{value:.2f}' },
                min: 0,
                opposite: false
            }, {
                title: {text: 'Used storage'},
                labels: { format: '{value:.0f}' },
                min: 0
            }];

            options.series = [{
                    name: 'Total storage',
                    turboThreshold: 0,
                    data: this.storage,
                     yAxis: 0,
                     color: '#0069cb',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#0069cb').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#0069cb').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#0069cb">{series.name}: <b>{point.y:.2f} GB</b></span><br/>' },
                },
                {
                    name: 'Used storage',
                    turboThreshold: 0,
                    data: this.used,
                     yAxis: 1,
                     color: '#00cba0',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#00cba0').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#00cba0').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f} GB</b></span><br/>' },
                }
            ];

            return options;
        },
        priceData(){
            if(typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff":"#252525",
            options.rangeSelector = {
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
                }
            };

            options.title.text = "Network prices";
            options.title.style.color = (this.appMode !== "night-mode") ? "#000":"#fff";

            options.yAxis = [{
                title: {text: 'Avg. price'},
                labels: { format: '{value:.0f}' },
                min: 0
            }];

            options.series = [
                {
                    name: 'Avg. price',
                    turboThreshold: 0,
                    data: this.prices,
                     yAxis: 0,
                     color: '#00cba0',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#00cba0').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#00cba0').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f} SC/TB/Mo</b></span><br/>' },
                }
            ];

            return options;
        },
    },
    methods: {
        refresh(){
            this.error = false;
            axios.get('/api/versions')
                .then((response) => {
                    this.versions = response.data;
                })
                .catch((error) => {
                    this.error = error.response.data;
                });

            axios.get('/api/countries')
                .then((response) => {
                    this.countries = response.data;
                })
                .catch((error) => {
                    this.error = error.response.data;
                });

            axios.get('/api/continents')
                .then((response) => {
                    this.continents = response.data;
                })
                .catch((error) => {
                    this.error = error.response.data;
                });

            axios.get('/api/network')
                .then((response) => {
                    this.network = response.data;
                })
                .catch((error) => {
                    this.error = error.response.data;
                });
        },
    },
    data() {
        return {
            error: false,
            versions: false,
            countries: false,
            continents: false,
            network: false,
            storage: false,
            used: false,
            activeHosts: false,
            allHosts: false,
            prices: false,

            curHosts: 0,
            curStorage: 0,
            curUtilization: 0,

            prevHosts24: 0,
            prevHosts7d: 0,
            prevStorage24: 0,
            prevStorage7d: 0,
            prevUtilization24: 0,
            prevUtilization7d: 0,

            moment: window.moment,

            pieConfig: {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                },
                credits: { enabled: false },
                title: {
                    text: 'Pie title',
                    style: {color: '#000'}
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}% ({point.y})</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                legend: {
                    itemStyle: {color: "#000"},
                    itemHoverStyle: {color: "#828282"}
                },
                series: {}
            },

            stockConfig: {
                title: {
                    text: 'Stock title',
                    style: {color: '#000'}
                },
                legend: {
                    enabled: false,
                    align: 'right',
                    backgroundColor: 'transparent',
                    borderColor: 'transparent',
                    borderWidth: 0,
                    layout: 'vertical',
                    verticalAlign: 'top',
                    y: 100,
                    shadow: true
                },
                chart: {
                    type: "area",
                },
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
                yAxis: [],
                plotOptions: {
                    area: { fillColor: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 } } }
                },
                series: []
            }
        };
    }
}
</script>
