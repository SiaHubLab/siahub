<template>
<div>
    <div class="row">
        <div class="col-md-12" style="padding:0px; margin-top: -20px;">
            <div id="floating-panel">
                <button class="btn btn-info btn-sm" @click.prevent="toggleMarkers()">Toggle markers</button>
                <button class="btn btn-success btn-sm" @click.prevent="toggleHeatmap()">Toggle heatmap</button>
            </div>
            <div class="map" style="width: 100%; height: 90vh;"></div>
        </div>
    </div>
</div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    mounted() {
        this.refresh();
    },
    watch: {
        appMode: function(){
            this.mapInit();
        }
    },
    computed: {
        appMode(){
            return this.$store.getters.appMode;
        },
    },
    methods: {
        toggleMarkers(){
            this.showMarkers = !this.showMarkers;
            this.clearMap();
            this.addMarkers();
        },
        toggleHeatmap(){
            this.heatmap.setMap(this.heatmap.getMap() ? null : this.map);
        },
        clearMap(){
            if (typeof this.markerCluster.clearMarkers === "function") {
                this.markerCluster.clearMarkers();
                google.maps.event.trigger(this.map, 'resize');
            }
        },
        addMarkers(){
            if(!this.showMarkers) return false;

            var points = this.getPoints();

            var image = {
                url: 'img/marker.png',
                size: new google.maps.Size(30, 30),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(30, 30)
             };

            this.markers = [];
            _.each(points, (point) => {

                var marker = new google.maps.Marker({
                    position: point.latlng,
                    map: this.map,
                    icon: image,
                    title: point.host.host
                });

                this.markers.push(marker);

                var contentString = '<h4>'+point.host.host+'</h4>\
                <p>Price: '+Math.round(point.host.storageprice/1e12*4320)+' SC </p>\
                <p>Storage: '+humanFileSize(point.host.totalstorage, true)+'</p>\
                <p>Used: '+humanFileSize(point.host.totalstorage-point.host.remainingstorage, true)+'</p>\
                <p>Announced addr: '+point.host.netaddress+'</p>\
                ';

                var percent = Math.round((point.host.totalstorage-point.host.remainingstorage)/point.host.totalstorage*100);
                if(!isNaN(percent)) {
                    var color_num = Math.round(percent/100*255);
                    var color = "rgba("+color_num+", "+ Math.max(0, 150 - color_num) +", 0, 0.7)"
                    contentString = contentString+'<div class="progress" style="width: 200px;">\
                    <div class="progress-bar" style="background-color: '+color+';width:'+(percent-15)+'%;"></div>\
                    <div class="progress-bar" style="background-color: '+color+';width:15%;">'+percent+'%</div>\
                    </div><br />';
                }

                contentString = contentString+'<p><a href="/host/'+point.host.id+'" class="btn btn-info btn=sm">View Host</a></p>';

                var infowindow = new google.maps.InfoWindow({
                    content: contentString
                });

                marker.addListener('click', function() {
                    infowindow.open(this.map, marker);
                });
            });

            this.markerCluster = new MarkerClusterer(this.map, this.markers, {
                maxZoom: 8,
                imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
            });
        },
        mapInit(){
            var that = this;

            var myLatlng = new google.maps.LatLng(35.8752506, -8.4676082);
            var myOptions = {
              zoom: 3,
              center: myLatlng,
              styles: (this.appMode == "night-mode") ? [
                    {elementType: 'geometry', stylers: [{color: '#242f3e'}]},
                    {elementType: 'labels.text.stroke', stylers: [{color: '#242f3e'}]},
                    {elementType: 'labels.text.fill', stylers: [{color: '#746855'}]},
                    {
                      featureType: 'administrative.locality',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#d59563'}]
                    },
                    {
                      featureType: 'poi',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#d59563'}]
                    },
                    {
                      featureType: 'poi.park',
                      elementType: 'geometry',
                      stylers: [{color: '#263c3f'}]
                    },
                    {
                      featureType: 'poi.park',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#6b9a76'}]
                    },
                    {
                      featureType: 'road',
                      elementType: 'geometry',
                      stylers: [{color: '#38414e'}]
                    },
                    {
                      featureType: 'road',
                      elementType: 'geometry.stroke',
                      stylers: [{color: '#212a37'}]
                    },
                    {
                      featureType: 'road',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#9ca5b3'}]
                    },
                    {
                      featureType: 'road.highway',
                      elementType: 'geometry',
                      stylers: [{color: '#746855'}]
                    },
                    {
                      featureType: 'road.highway',
                      elementType: 'geometry.stroke',
                      stylers: [{color: '#1f2835'}]
                    },
                    {
                      featureType: 'road.highway',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#f3d19c'}]
                    },
                    {
                      featureType: 'transit',
                      elementType: 'geometry',
                      stylers: [{color: '#2f3948'}]
                    },
                    {
                      featureType: 'transit.station',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#d59563'}]
                    },
                    {
                      featureType: 'water',
                      elementType: 'geometry',
                      stylers: [{color: '#17263c'}]
                    },
                    {
                      featureType: 'water',
                      elementType: 'labels.text.fill',
                      stylers: [{color: '#515c6d'}]
                    },
                    {
                      featureType: 'water',
                      elementType: 'labels.text.stroke',
                      stylers: [{color: '#17263c'}]
                    }
                ]:[]
            };

            this.map = new google.maps.Map(that.$el.querySelector('.map'), myOptions);
            this.addMarkers();

            this.heatmap = new google.maps.visualization.HeatmapLayer({
                data: this.getPoints().map(function(host){ return host.latlng; }),
                map: this.map
            });
            this.heatmap.set('radius', this.heatmap.get('radius') ? null : 20);
        },
        getPoints(){
            return this.hosts.map(function(host) {
                return {
                    latlng: new google.maps.LatLng(host.lat, host.lng),
                    host: host.host
                }
            })
        },
        refresh(){
            if(this.loading) return false;

            this.error = false;
            this.loading = true;
            axios.get('/api/map')
                .then((response) => {
                    this.hosts = response.data;
                    this.loading = false;
                    this.mapInit();
                })
                .catch((error) => {
                    console.log(error);
                    this.error = error.response;
                    this.loading = false;
                });
        },
    },
    data() {
        return {
            loading: false,
            error: false,
            hosts: [],
            markers: [],
            map: false,
            heatmap: false,
            clusterer: false,
            showMarkers: true
        };
    }
}
</script>
