<template>
    <div class="Polaris-Tabs__Wrapper Polaris-Tabs__Navigation">
        <ul role="tablist" class="Polaris-Tabs">
            <li class="Polaris-Tabs__TabContainer" v-for="(item,index) in items">
                <router-link :tabindex="index" active-class="Polaris-Tabs__Tab--selected" class="Polaris-Tabs__Tab" :to="item.to" :aria-label="item.title">
                    <span class="Polaris-Tabs__Title">{{item.title}}</span>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                items: [
                    {title: 'Transactions', to: '/transactions'},
                    /*{title: 'Transactions', to: '/demo-transactions'},*/
                    {title: 'Products', to: '/products'},
                    {title: 'Settings', to: '/settings'},
                ]
            }
        },
        methods: {
            pushRoute(path) {
                this.$router.push(path);
            },
        }
    }
</script>
