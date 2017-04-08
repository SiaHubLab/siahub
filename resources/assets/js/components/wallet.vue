<template>
<div class="row">
    <div v-if="!wallet" class="col-md-8 col-md-offset-2 alert alert-danger">
        <p>Our wallet under maintenance, data updates paused. Sorry for inconvenience!</p>
    </div>
</div>
</template>

<script>
export default {
    mounted() {
        this.refresh();

        setInterval(() => {
             console.log('updating wallet status');
             this.refresh();
         }, 60000);
    },
    methods: {
        refresh(){
            if(this.loading) return false;

            this.error = false;
            this.loading = true;
            axios.get('/api/wallet_status')
                .then((response) => {
                    this.wallet = response.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.error = error.response.data;
                    this.loading = false;
                });
        },
    },
    data() {
        return {
            loading: false,
            error: false,
            wallet: true,
        };
    }
}
</script>
