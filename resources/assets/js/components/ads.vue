<template>
    <div v-html="ads" class="col-md-12 text-center"></div>
</template>

<script>
    export default {
        watch: {
            '$route': function(){
                this.dyn++;
            }
        },
        computed: {
            ads: function(){
                return "<iframe "+this.dyn+" data-aa='821892' src='//acceptable.a-ads.com/821892?size=Adaptive&background_color=f5f8fa' scrolling='no' style='border:0px; padding:0;overflow:hidden' allowtransparency='true'></iframe>";
            }
        },
        data: function(){
            return {dyn: 1}
        }
    }
</script>