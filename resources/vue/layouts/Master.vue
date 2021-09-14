<template>
    <div>
        <core-nav />
        <router-view/>
    </div>
</template>

<script>
    export default {
        name: "Master",
        components: {},
        created() {
            this.getShopData();
        },
        methods: {
            async getShopData() {
                try {
                    let response = await axios.get('/api/settings/store-detail');
                    if (response.data.is_app_setup !== 1) {
                        this.$router.push('/welcome-page');
                    } else {
                        var urls = ["/products", "/settings"];
                        if (!urls.includes(this.$router.currentRoute.path)) {
                            this.$router.push('/transactions');
                        }
                    }
                } catch ({response}) {

                }
            }
        },
    }
</script>
