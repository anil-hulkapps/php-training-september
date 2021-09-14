<template>
    <div>
        <router-view/>
    </div>
</template>

<script>
    export default {
        name: "Onboarding",
        components: {},
        created() {
            this.getShopData();
        },
        methods: {
            async getShopData() {
                try {
                    let response = await axios.get('/api/settings/store-detail');
                    if (response.data.is_app_setup === 1) {
                        this.$router.push('/transactions');
                    }
                } catch ({response}) {

                }
            }
        },
    }
</script>
