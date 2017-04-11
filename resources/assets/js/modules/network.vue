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
            console.log(totalHosts);
            var options = {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Sia version'
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
                    series: [{
                        name: 'Active Hosts',
                        colorByPoint: true,
                        data: this.versions.map((entry) => {
                        console.log((entry.hosts/totalHosts*100), entry.hosts);
                            var color_num = Math.round((entry.hosts/totalHosts*100)/100*255);
                            var color = "rgba(0, "+color_num+", "+ Math.max(0, 150 - color_num) +", 0.7)";

                            return {
                                color: color,
                                name: entry.version,
                                y: entry.hosts,
                            };
                        }),
                    }]
                };
            return options;
        },
        countryData(){
            if(typeof this.countries !== "object") return false;
            var totalHosts = this.countries.reduce(function(a, b){
                return a + parseInt(b.hosts);
            }, 0);
            console.log(totalHosts);
            var options = {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Countries by active hosts'
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                        }
                    },
                    series: [{
                        name: 'Active Hosts',
                        colorByPoint: true,
                        data: this.countries.map((entry) => {
                        console.log((entry.hosts/totalHosts*100), entry.hosts);
                            var color_num = Math.round((entry.hosts/totalHosts*100)/100*255);
                            var color = "rgba(0, "+color_num+", "+ Math.max(0, 150 - color_num) +", 0.7)";

                            return {
                                color: color,
                                name: entry.country,
                                y: entry.hosts,
                            };
                        }),
                    }]
                };
            return options;
        },
        continentData(){
            if(typeof this.continents !== "object") return false;
            var totalHosts = this.continents.reduce(function(a, b){
                return a + parseInt(b.hosts);
            }, 0);
            console.log(totalHosts);
            var options = {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Continents by active hosts'
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
                    series: [{
                        name: 'Active Hosts',
                        colorByPoint: true,
                        data: this.continents.map((entry) => {
                        console.log((entry.hosts/totalHosts*100), entry.hosts);
                            var color_num = Math.round((entry.hosts/totalHosts*100)/100*255);
                            var color = "rgba(0, "+color_num+", "+ Math.max(0, 150 - color_num) +", 0.7)";

                            return {
                                color: color,
                                name: entry.continent,
                                y: entry.hosts,
                            };
                        }),
                    }]
                };
            return options;
        }
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
        },
    },
    data() {
        return {
            error: false,
            versions: false,
            countries: false,
            continents: false,
            moment: window.moment,
        };
    }
}
</script>
