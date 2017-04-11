<template>
<div>
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
        versionsData(){
            if(typeof this.versions !== "object") return false;
            var totalHosts = this.versions.reduce(function(a, b){
                return a + parseInt(b.hosts);
            }, 0);
            var options = _.cloneDeep(this.pieConfig);
            options.title.text = 'Sia version';
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
            coptions.title.text = 'Countries by active hosts';

            coptions.plotOptions.pie.dataLabels = {
                enabled: true
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
            options.title.text = 'Continents by active hosts';
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

        hostsData(){
            if(typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.title = {text: "Network hosts"};

            options.yAxis = [{
                title: {text: 'Active Hosts'},
                labels: { format: '{value:.2f}' },
                min: 0
            }, {
                title: {text: 'Total Hosts'},
                labels: { format: '{value:.0f}' },
                min: 0,
                opposite: false
            }];

            options.series = [{
                    name: 'Active Hosts',
                    turboThreshold: 0,
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: entry.active_hosts,
                        };
                    }),
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
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: entry.all_hosts,
                        };
                    }),
                     yAxis: 0,
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
            options.title = {text: "Network storage"};

            options.yAxis = [{
                title: {text: 'Total storage'},
                labels: { format: '{value:.2f}' },
                min: 0
            }, {
                title: {text: 'Used storage'},
                labels: { format: '{value:.0f}' },
                min: 0,
                opposite: false
            }];

            options.series = [{
                    name: 'Total storage',
                    turboThreshold: 0,
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: parseInt(entry.totalstorage)/1000/1000/1000,
                        };
                    }),
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
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: (parseInt(entry.totalstorage)-parseInt(entry.remainingstorage))/1000/1000/1000,
                        };
                    }),
                     yAxis: 0,
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
            options.title = {text: "Network prices"};

            options.yAxis = [{
                title: {text: 'Max. price'},
                labels: { format: '{value:.2f}' },
                min: 0
            }, {
                title: {text: 'Avg. price'},
                labels: { format: '{value:.0f}' },
                min: 0,
                opposite: false
            }, {
                title: {text: 'Min. price'},
                labels: { format: '{value:.0f}' },
                min: 0
            }];

            options.series = [{
                    name: 'Max. price',
                    turboThreshold: 0,
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: parseFloat(entry.max_storageprice),
                        };
                    }),
                     yAxis: 0,
                     color: '#0069cb',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#0069cb').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#0069cb').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#0069cb">{series.name}: <b>{point.y:.2f} SC/TB/Mo</b></span><br/>' },
                },
                {
                    name: 'Avg. price',
                    turboThreshold: 0,
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: parseFloat(entry.avg_storageprice),
                        };
                    }),
                     yAxis: 0,
                     color: '#00cba0',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#00cba0').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#00cba0').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f} SC/TB/Mo</b></span><br/>' },
                },
                {
                    name: 'Min. price',
                    turboThreshold: 0,
                    data: this.network.map((entry) => {
                        return {
                            x: parseInt(moment.utc(entry.created_at).format('x')),
                            y: parseFloat(entry.min_storageprice),
                        };
                    }),
                     yAxis: 0,
                     color: '#cb004c',
                     fillColor: {
                        stops: [
                            [0, Highcharts.Color('#cb004c').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#cb004c').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: { pointFormat: '<span style="color:#cb004c">{series.name}: <b>{point.y:.2f} SC/TB/Mo</b></span><br/>' },
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
            moment: window.moment,

            pieConfig: {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                credits: { enabled: false },
                title: {
                    text: 'Pie title'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
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
                series: {}
            },

            stockConfig: {
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
