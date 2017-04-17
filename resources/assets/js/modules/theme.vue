<template>
    <ul class="nav navbar-nav navbar-right">
        <li>
            <a href="#" @click.prevent="toggleTheme()" v-html="(this.appMode === '') ? 'Night':'Day'"></a>
        </li>
    </ul>
</template>

<script>
import { updateAppMode } from '../store/actions';
import { mapGetters, mapActions } from 'vuex'

export default {
    mounted(){
        this.loadTheme();
    },
    computed: mapGetters(['appMode']),
    watch: {
        appMode: function() {
            this.loadTheme();
        }
    },
    methods: {
        updateAppMode(mode) {
          this.$store.dispatch('updateAppMode', mode);
        },
        toggleTheme(){
            this.updateAppMode((this.appMode == "night-mode") ? '':'night-mode');
        },
        loadTheme() {
            if(this.appMode == "night-mode") {
                document.body.classList.add('night-mode');
            } else {
                document.body.classList.remove('night-mode');
            }
        }
    }
}
</script>
