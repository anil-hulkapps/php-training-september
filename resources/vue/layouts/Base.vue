<template>
    <div>
        <router-view/>
        <core-confirm ref="confirm"></core-confirm>
    </div>
</template>

<script>
    import createApp from '@shopify/app-bridge';
    import {History, Toast, TitleBar, Redirect} from '@shopify/app-bridge/actions';
    export default {
        name: "Master",
        components: {},
        data() {
            return {
                is_app_setup: 0
            }
        },
        async beforeCreate() {
            var queryStrings = this.$route.query;
            try {
                if (Object.keys(queryStrings).length) {
                    window.axios.defaults.headers.common['shopify'] = JSON.stringify(queryStrings);

                    this.$root.$shopifyApp = createApp({
                        apiKey: process.env.MIX_SHOPIFY_API_KEY,
                        shopOrigin: this.$route.query.shop,
                        forceRedirect: true,
                    });

                    const titleBarOptions = {
                        title: this.$route.meta.title,
                    };
                    this.titleBar = TitleBar.create(this.$root.$shopifyApp, titleBarOptions);
                } else {
                    throw new Error('Unauthenticated!');
                }
            } catch (error) {
                const redirect = Redirect.create(this.$root.$shopifyApp);
                redirect.dispatch(Redirect.Action.APP, '/login');
            }
        },
        watch: {
            $route(to, from) {
                const history = History.create(this.$root.$shopifyApp);
                history.dispatch(History.Action.PUSH, to.path);
                this.titleBar.set({
                    title: to.meta.title,
                });
            }
        },
        async mounted() {
            this.$root.$confirm = this.$refs.confirm.open;
            this.$root.$toast = this.toastNotification;
        },
        methods: {
            toastNotification(message, isError = false, duration = 5000) {
                const toastNotification = Toast.create(this.$root.$shopifyApp, {
                    message,
                    duration,
                    isError,
                });
                toastNotification.dispatch(Toast.Action.SHOW);
            }
        },
    }
</script>
