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
export default {
    mounted() {
        this.refresh();
    },
    computed: {
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
                this.markers.push(new google.maps.Marker({
                    position: point,
                    map: this.map,
                    icon: image,
                    title: 'Hello World!'
                }));
            });

            this.markerCluster = new MarkerClusterer(this.map, this.markers, {
                imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
            });

        },
        mapInit(){
            var that = this;

            var myLatlng = new google.maps.LatLng(35.8752506, -8.4676082);
            // map options,
            var myOptions = {
              zoom: 3,
              center: myLatlng
            };

            this.map = new google.maps.Map(that.$el.querySelector('.map'), myOptions);
            this.addMarkers();

            this.heatmap = new google.maps.visualization.HeatmapLayer({
                data: this.getPoints(),
                map: this.map
            });
            this.heatmap.set('radius', this.heatmap.get('radius') ? null : 20);
        },
        getPoints(){
            return this.hosts.map(function(host) {
                return new google.maps.LatLng(host.lat, host.lng)
            })
        },
        refresh(){

            // if(this.getHostById(this.$route.params.id)){
            //     this.hostData = this.getHostById(this.$route.params.id);
            //     return true;
            // }

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
