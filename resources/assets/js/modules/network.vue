<template>
<div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Network statistics
                </div>
                <div class="panel-body" v-if="!inprocess">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-center">24 hrs</th>
                                    <th class="text-center">7 days</th>
                                    <th class="text-center">30 days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Active hosts</td>
                                    <td><numberChangeSnippet :value="hostsChange24hr"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="hostsChange7d"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="hostsChange30d"></numberChangeSnippet></td>
                                </tr>
                                <tr>
                                    <td>Total hosts</td>
                                    <td><numberChangeSnippet :value="totalHostsChange24hr"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="totalHostsChange7d"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="totalHostsChange30d"></numberChangeSnippet></td>
                                </tr>
                                <tr>
                                    <td>Total storage</td>
                                    <td><numberChangeSnippet :value="storageChange24hr" suffix="TB"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="storageChange7d" suffix="TB"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="storageChange30d" suffix="TB"></numberChangeSnippet></td>
                                </tr>
                                <tr>
                                    <td>Storage utilization</td>
                                    <td><numberChangeSnippet :value="utilizationChange24hr" suffix="TB"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="utilizationChange7d" suffix="TB"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="utilizationChange30d" suffix="TB"></numberChangeSnippet></td>
                                </tr>
                                <tr>
                                    <td>Avg storage price</td>
                                    <td><numberChangeSnippet :value="priceChange24hr" suffix="SC"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="priceChange7d" suffix="SC"></numberChangeSnippet></td>
                                    <td><numberChangeSnippet :value="priceChange30d" suffix="SC"></numberChangeSnippet></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-body" v-else>
                    <loader></loader>
                </div>
            </div>
        </div>
        <div v-if="versionsData" class="col-md-6">
            <highcharts :options="versionsData" ref="highcharts"></highcharts>
            <br />
        </div>
    </div>
    <div class="row">
        <div v-if="countryData" class="col-md-6">
            <highcharts :options="countryData" ref="highcharts"></highcharts>
        </div>
        <div v-if="continentData" class="col-md-6">
            <highcharts :options="continentData" ref="highcharts"></highcharts>
        </div>
    </div>

    <br />
    <div class="row">
        <div v-if="hostsData && !inprocess" class="col-md-6">
            <highstock :options="hostsData" ref="highcharts"></highstock>
        </div>
        <div v-if="storageData && !inprocess" class="col-md-6">
            <highstock :options="storageData" ref="highcharts"></highstock>
        </div>
    </div>
    <br />

    <div v-if="priceData && !inprocess" class="row">
        <div class="col-md-12">
            <highstock :options="priceData" ref="highcharts"></highstock>
        </div>
    </div>

    <div v-if="inprocess" class="row">
        <div class="col-md-12">
            <loader></loader>
        </div>
    </div>

    <br />
    <div v-if="error" class="alert alert-danger">
        <p><strong>Oops!</strong> Error, {{error}}</p>
        <p><button class="btn btn-primary" @click.prevent="refresh()">Try again</button></p>
    </div>
</div>
</template>

<script>
import Highcharts from 'highcharts';
import {
    mapGetters
} from 'vuex'

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
        network() {
            var that = this;
            that.inprocess = true;

            _.delay(() => {
                that.storage = [];
                that.used = [];
                that.activeHosts = [];
                that.allHosts = [];
                that.prices = [];

                var dayAgo = moment().subtract(1, 'day');
                var weekAgo = moment().subtract(7, 'day');
                var monthAgo = moment().subtract(30, 'day');

                for (let entry of that.network) {
                    var time = parseInt(moment.utc(entry.created_at).format('x'));
                    that.activeHosts.push({
                        x: time,
                        y: entry.active_hosts
                    });
                    that.allHosts.push({
                        x: time,
                        y: entry.all_hosts
                    });
                    that.prices.push({
                        x: time,
                        y: parseFloat(entry.avg_storageprice)
                    });
                    that.storage.push({
                        x: time,
                        y: parseInt(entry.totalstorage) / 1000 / 1000 / 1000 / 1000
                    });
                    that.used.push({
                        x: time,
                        y: (parseInt(entry.totalstorage) - parseInt(entry.remainingstorage)) / 1000 / 1000 / 1000 / 1000
                    });

                    if (moment(moment.utc(time)).isSameOrBefore(dayAgo)) {
                        that.prevPrice24 = parseFloat(entry.avg_storageprice);
                        that.prevHosts24 = entry.active_hosts;
                        that.prevTotalHosts24 = entry.all_hosts;
                        that.prevStorage24 = parseInt(entry.totalstorage) / 1000 / 1000 / 1000 / 1000;
                        that.prevUtilization24 = (parseInt(entry.totalstorage) - parseInt(entry.remainingstorage)) / 1000 / 1000 / 1000 / 1000;
                    }

                    if (moment(moment.utc(time)).isSameOrBefore(weekAgo)) {
                        that.prevPrice7d = parseFloat(entry.avg_storageprice);
                        that.prevHosts7d = entry.active_hosts;
                        that.prevTotalHosts7d = entry.all_hosts;
                        that.prevStorage7d = parseInt(entry.totalstorage) / 1000 / 1000 / 1000 / 1000;
                        that.prevUtilization7d = (parseInt(entry.totalstorage) - parseInt(entry.remainingstorage)) / 1000 / 1000 / 1000 / 1000;
                    }

                    if (moment(moment.utc(time)).isSameOrBefore(monthAgo)) {
                        that.prevPrice30d = parseFloat(entry.avg_storageprice);
                        that.prevHosts30d = entry.active_hosts;
                        that.prevTotalHosts30d = entry.all_hosts;
                        that.prevStorage30d = parseInt(entry.totalstorage) / 1000 / 1000 / 1000 / 1000;
                        that.prevUtilization30d = (parseInt(entry.totalstorage) - parseInt(entry.remainingstorage)) / 1000 / 1000 / 1000 / 1000;
                    }

                    that.curPrice = parseFloat(entry.avg_storageprice);
                    that.curHosts = entry.active_hosts;
                    that.curTotalHosts = entry.all_hosts;
                    that.curStorage = parseInt(entry.totalstorage) / 1000 / 1000 / 1000 / 1000;
                    that.curUtilization = (parseInt(entry.totalstorage) - parseInt(entry.remainingstorage)) / 1000 / 1000 / 1000 / 1000;
                }

                that.inprocess = false;
            }, 1000);

        }
    },
    computed: {
        appMode() {
            return this.$store.getters.appMode;
        },
        versionsData() {
            if (typeof this.versions !== "object") return false;
            var totalHosts = this.versions.reduce(function(a, b) {
                return a + parseInt(b.hosts);
            }, 0);
            var options = _.cloneDeep(this.pieConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff" : "#252525";
            options.plotOptions.pie.borderColor = (this.appMode !== "night-mode") ? "#fff" : "#dadada";
            options.title.text = 'Sia version';
            options.title.style.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            options.legend.itemStyle.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            options.legend.itemHoverStyle.color = (this.appMode !== "night-mode") ? "#828282" : "#dadada";

            options.series = [{
                name: 'Active Hosts',
                colorByPoint: true,
                data: this.versions.map((entry) => {
                    var color_num = Math.round((entry.hosts / totalHosts * 100) / 100 * 255);
                    var color = "rgba(0, " + color_num + ", " + Math.max(0, 150 - color_num) + ", 0.7)";

                    return {
                        color: color,
                        name: entry.version,
                        y: entry.hosts,
                    };
                }),
            }];
            return options;
        },
        countryData() {
            if (typeof this.countries !== "object") return false;
            var totalHosts = this.countries.reduce(function(a, b) {
                return a + parseInt(b.hosts);
            }, 0);

            var coptions = _.cloneDeep(this.pieConfig);
            coptions.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff" : "#252525";
            coptions.plotOptions.pie.borderColor = (this.appMode !== "night-mode") ? "#fff" : "#dadada";
            coptions.title.text = 'Countries by active hosts';
            coptions.title.style.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            coptions.legend.itemStyle.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            coptions.legend.itemHoverStyle.color = (this.appMode !== "night-mode") ? "#828282" : "#dadada";

            coptions.plotOptions.pie.dataLabels = {
                enabled: true,
                color: (this.appMode !== "night-mode") ? "#000" : "#dadada",
                connectorColor: (this.appMode !== "night-mode") ? "#000" : "#dadada",
                borderWidth: 0,
                style: {
                    textOutline: '0'
                }
            };
            coptions.plotOptions.pie.showInLegend = false;

            coptions.series = [{
                name: 'Active Hosts',
                colorByPoint: true,
                data: this.countries.map((entry) => {
                    var color_num = Math.round((entry.hosts / totalHosts * 100) / 100 * 255);
                    var color = "rgba(0, " + color_num + ", " + Math.max(0, 150 - color_num) + ", 0.7)";

                    return {
                        color: color,
                        name: (entry.country !== "") ? entry.country:"Unknown",
                        y: entry.hosts,
                    };
                }),
            }];

            return coptions;
        },
        continentData() {
            if (typeof this.continents !== "object") return false;
            var totalHosts = this.continents.reduce(function(a, b) {
                return a + parseInt(b.hosts);
            }, 0);

            var options = _.cloneDeep(this.pieConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff" : "#252525";
            options.plotOptions.pie.borderColor = (this.appMode !== "night-mode") ? "#fff" : "#dadada";
            options.title.text = 'Continents by active hosts';
            options.title.style.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            options.legend.itemStyle.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            options.legend.itemHoverStyle.color = (this.appMode !== "night-mode") ? "#828282" : "#dadada";
            options.series = [{
                name: 'Active Hosts',
                colorByPoint: true,
                data: this.continents.map((entry) => {
                    var color_num = Math.round((entry.hosts / totalHosts * 100) / 100 * 255);
                    var color = "rgba(0, " + color_num + ", " + Math.max(0, 150 - color_num) + ", 0.7)";

                    return {
                        color: color,
                        name: (entry.continent !== "") ? entry.continent:"Unknown",
                        y: entry.hosts,
                    };
                }),
            }];

            return options;
        },

        hostsChange24hr() {
            if (typeof this.network !== "object") return false;

            var change = this.curHosts - this.prevHosts24;
            return change;
        },
        hostsChange7d() {
            if (typeof this.network !== "object") return false;

            var change = this.curHosts - this.prevHosts7d;
            return change;
        },
        hostsChange30d() {
            if (typeof this.network !== "object") return false;

            var change = this.curHosts - this.prevHosts30d;
            return change;
        },
        totalHostsChange24hr() {
            if (typeof this.network !== "object") return false;

            var change = this.curTotalHosts - this.prevTotalHosts24;
            return change;
        },
        totalHostsChange7d() {
            if (typeof this.network !== "object") return false;

            var change = this.curTotalHosts - this.prevTotalHosts7d;
            return change;
        },
        totalHostsChange30d() {
            if (typeof this.network !== "object") return false;

            var change = this.curTotalHosts - this.prevTotalHosts30d;
            return change;
        },
        storageChange24hr() {
            if (typeof this.network !== "object") return false;

            var change = this.curStorage - this.prevStorage24;
            return change.toFixed(2);
        },
        storageChange7d() {
            if (typeof this.network !== "object") return false;

            var change = this.curStorage - this.prevStorage7d;
            return change.toFixed(2);
        },
        storageChange30d() {
            if (typeof this.network !== "object") return false;

            var change = this.curStorage - this.prevStorage30d;
            return change.toFixed(2);
        },
        utilizationChange24hr() {
            if (typeof this.network !== "object") return false;

            var change = this.curUtilization - this.prevUtilization24;
            return change.toFixed(2);
        },
        utilizationChange7d() {
            if (typeof this.network !== "object") return false;

            var change = this.curUtilization - this.prevUtilization7d;
            return change.toFixed(2);
        },
        utilizationChange30d() {
            if (typeof this.network !== "object") return false;

            var change = this.curUtilization - this.prevUtilization30d;
            return change.toFixed(2);
        },
        priceChange24hr() {
            if (typeof this.network !== "object") return false;

            var change = this.curPrice - this.prevPrice24;
            return change.toFixed(2);
        },
        priceChange7d() {
            if (typeof this.network !== "object") return false;

            var change = this.curPrice - this.prevPrice7d;
            return change.toFixed(2);
        },
        priceChange30d() {
            if (typeof this.network !== "object") return false;

            var change = this.curPrice - this.prevPrice30d;
            return change.toFixed(2);
        },
        hostsData() {
            if (typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff" : "#252525";
            options.title.text = "Network hosts";
            options.title.style.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            options.rangeSelector = {
                selected: 0,
                buttonTheme: { // styles for the buttons
                    fill: 'none',
                    stroke: 'none',
                    'stroke-width': 0,
                    r: 8,
                    style: {
                        color: (this.appMode !== "night-mode") ? "#039" : "#dadada",
                        fontWeight: 'bold'
                    },
                    states: {
                        hover: {},
                        select: {
                            fill: (this.appMode !== "night-mode") ? "#039" : "#dadada",
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
                    color: (this.appMode !== "night-mode") ? "#039" : "#dadada",
                    fontWeight: 'bold'
                },
                labelStyle: {
                    color: 'silver',
                    fontWeight: 'bold'
                }
            };
            options.yAxis = [{
                title: {
                    text: 'Active Hosts'
                },
                labels: {
                },
                min: 0,
                opposite: false
            }, {
                title: {
                    text: 'Total Hosts'
                },
                labels: {
                },
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
                    tooltip: {
                        pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f}</b></span><br/>'
                    },
                },
                {
                    name: 'Total Hosts',
                    turboThreshold: 0,
                    data: this.allHosts,
                    yAxis: 0,
                    color: '#0069cb',
                    fillColor: {
                        stops: [
                            [0, Highcharts.Color('#0069cb').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#0069cb').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: {
                        pointFormat: '<span style="color:#0069cb">{series.name}: <b>{point.y:.2f}</b></span><br/>'
                    },
                }
            ];

            return options;
        },
        storageData() {
            if (typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff" : "#252525",
            options.title.text = "Network storage";
            options.title.style.color = (this.appMode !== "night-mode") ? "#000" : "#fff";
            options.rangeSelector = {
                selected: 0,
                buttonTheme: { // styles for the buttons
                    fill: 'none',
                    stroke: 'none',
                    'stroke-width': 0,
                    r: 8,
                    style: {
                        color: (this.appMode !== "night-mode") ? "#039" : "#dadada",
                        fontWeight: 'bold'
                    },
                    states: {
                        hover: {},
                        select: {
                            fill: (this.appMode !== "night-mode") ? "#039" : "#dadada",
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
                    color: (this.appMode !== "night-mode") ? "#039" : "#dadada",
                    fontWeight: 'bold'
                },
                labelStyle: {
                    color: 'silver',
                    fontWeight: 'bold'
                }
            };
            options.yAxis = [{
                title: {
                    text: 'Total storage'
                },
                labels: {
                },
                min: 0,
                opposite: false
            }, {
                title: {
                    text: 'Used storage'
                },
                labels: {
                },
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
                    tooltip: {
                        pointFormat: '<span style="color:#0069cb">{series.name}: <b>{point.y:.2f} TB</b></span><br/>'
                    },
                },
                {
                    name: 'Used storage',
                    turboThreshold: 0,
                    data: this.used,
                    yAxis: 0,
                    color: '#00cba0',
                    fillColor: {
                        stops: [
                            [0, Highcharts.Color('#00cba0').setOpacity(0.3).get('rgba')],
                            [1, Highcharts.Color('#00cba0').setOpacity(0).get('rgba')]
                        ]
                    },
                    tooltip: {
                        pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f} TB</b></span><br/>'
                    },
                }
            ];

            return options;
        },
        priceData() {
            if (typeof this.network !== "object") return false;

            var options = _.cloneDeep(this.stockConfig);
            options.chart.backgroundColor = (this.appMode !== "night-mode") ? "#fff" : "#252525";
            options.rangeSelector = {
                    selected: 0,
                    buttonTheme: { // styles for the buttons
                        fill: 'none',
                        stroke: 'none',
                        'stroke-width': 0,
                        r: 8,
                        style: {
                            color: (this.appMode !== "night-mode") ? "#039" : "#dadada",
                            fontWeight: 'bold'
                        },
                        states: {
                            hover: {},
                            select: {
                                fill: (this.appMode !== "night-mode") ? "#039" : "#dadada",
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
                        color: (this.appMode !== "night-mode") ? "#039" : "#dadada",
                        fontWeight: 'bold'
                    },
                    labelStyle: {
                        color: 'silver',
                        fontWeight: 'bold'
                    }
                };

            options.title.text = "Network prices";
            options.title.style.color = (this.appMode !== "night-mode") ? "#000" : "#fff";

            options.yAxis = [{
                title: {
                    text: 'Avg. price'
                },
                labels: {
                },
                min: 0
            }];

            options.series = [{
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
                tooltip: {
                    pointFormat: '<span style="color:#00cba0">{series.name}: <b>{point.y:.2f} SC/TB/Mo</b></span><br/>'
                },
            }];

            return options;
        },
    },
    methods: {
        refresh() {
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
            inprocess: true,
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
            curTotalHosts: 0,
            curStorage: 0,
            curUtilization: 0,
            curPrice: 0,

            prevHosts24: 0,
            prevHosts7d: 0,
            prevHosts30d: 0,

            prevTotalHosts24: 0,
            prevTotalHosts7d: 0,
            prevTotalHosts30d: 0,

            prevStorage24: 0,
            prevStorage7d: 0,
            prevStorage30d: 0,

            prevUtilization24: 0,
            prevUtilization7d: 0,
            prevUtilization30d: 0,

            prevPrice24: 0,
            prevPrice7d: 0,
            prevPrice30d: 0,

            moment: window.moment,

            pieConfig: {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                },
                credits: {
                    enabled: false
                },
                title: {
                    text: 'Pie title',
                    style: {
                        color: '#000'
                    }
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}% ({point.y})</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false,
                            connectorColor: '#fff'
                        },
                        showInLegend: true
                    }
                },
                legend: {
                    itemStyle: {
                        color: "#000"
                    },
                    itemHoverStyle: {
                        color: "#828282"
                    }
                },
                series: {}
            },

            stockConfig: {
                title: {
                    text: 'Stock title',
                    style: {
                        color: '#000'
                    }
                },
                legend: {
                    enabled: true
                },
                chart: {
                    type: "area",
                },
                rangeSelector: {},
                scrollbar: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    xDateFormat: "%Y-%m-%d %H:%M UTC"
                },
                xAxis: {
                    type: 'datetime',
                },
                yAxis: [],
                plotOptions: {
                    area: {
                        fillColor: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            }
                        }
                    }
                },
                series: []
            }
        };
    }
}
</script>
