<template>
    <PPage narrowWidth title="Step 5/5" :breadcrumbs='[{"content":"Step 5/5", "to":"/step-4"}]'>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Apps installed in your store. (Optional)</PHeading>
                    Define the shipping & product-pricing apps installed in your store to avoid any further conflicts.<br /><br />
                    <PFormLayout>
                        <template v-for="(app, index) in apps">
                        <PTextField :id="'appname'+index" connected placeholder="Enter app name"  v-model="app.appname">
                            <PButton slot="connectedRight" icon="DeleteMinor" @click="removeApp(index)"></PButton>
                        </PTextField>
                        </template>
                        <PButton @click="addMore">Add more</PButton>
                    </PFormLayout>
                </PCardSection>
            </PCard>
            <PLayoutSection>
                <PPageActions :primaryAction="{content: 'Continue', onAction: handleButtonEvent}"></PPageActions>
            </PLayoutSection>
        </PLayout>
    </PPage>
</template>

<script>

    export default {
        name: "Step6",
        data() {
            return {
                apps: [{
                    appname: ''
                }],
            }
        },
        computed: {},
        methods: {
            handleAction() {
                this.$router.push('/step-4');
            },
            async handleButtonEvent() {
                this.$router.push('/setup-comptele');

                let param = {
                    step: 6,
                    apps: this.apps,
                };

                try {
                    let {data} = await axios.post('/api/settings/step', param);
                } catch ({response}) {

                }
            },
            addMore() {
              console.log(this.apps);
              this.apps.push({appname: ''});
            },
            removeApp(index) {
                console.log(index);
                this.apps.splice(index, 1);
                console.log(this.apps);
            },
            async getData() {
                try {
                    let that = this;
                    let response = await axios.get('/api/settings/step?step=6');
                    response = response.data;
                    response[0].split(",").forEach(function (app, index) {
                        if (index == 0)
                            that.apps.splice(index,1);
                        that.apps.push({appname: app});
                    });
                    console.log(that.apps);
                } catch ({response}) {

                }
            },
        },
        async created() {
            await this.getData();
        }
    }
</script>

<style scoped>

</style>

