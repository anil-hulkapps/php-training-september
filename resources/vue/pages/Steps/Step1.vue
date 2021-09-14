<template>
    <PPage narrowWidth title="Step 1/5" :breadcrumbs='[{"content": "Step 1/5", "to":""}]'>
        <!--<PBreadcrumbs :breadcrumbs="[{id:'breadcrumb_1', content:'Step 1/6', onAction:handleAction}]" /><br />-->
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Connect Avalara Excise tax connector</PHeading>
                    In order to get product information from Avalara, we need following details. We will keep this information confidential and it will be used only for specific purpose.<br /><br />
                    <PFormLayout>
                        <PFormLayoutGroup>
                            <PTextField id="username" label="Username" v-model="username" :error="errors['username'] ? errors['username'][0] : ''"/>
                            <PTextField id="password" type='password' label="Password" v-model="password" :error="errors['password'] ? errors['password'][0] : ''"/>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup>
                            <PTextField id="client_id" label="Client ID" v-model="company_id" :error="errors['company_id'] ? errors['company_id'][0] : ''"/>
                            <div></div>
                        </PFormLayoutGroup>
                        <!--<PFormLayoutGroup>
                            <PTextStyle :variation="connectionVariation">{{ connectionMsg }}</PTextStyle>
                        </PFormLayoutGroup>-->
                    </PFormLayout>
                </PCardSection>
            </PCard>
            <PLayoutSection>
                <PPageActions :primaryAction="{content: 'Continue', onAction: handleButtonEvent}"></PPageActions>
            </PLayoutSection>
            <!--<br /><br />
            <PButton primary @click="handleButtonEvent()">Continue</PButton>-->
        </PLayout>
    </PPage>
</template>

<script>

    export default {
        name: "Step1",
        data() {
            return {
                username: null,
                password: null,
                company_id: null,
                errors: {},
                connectionMsg: '',
                connected: false,
                connectionVariation: 'negative'
            }
        },
        computed: {},
        methods: {
            handleAction() {
                //alert('Action triggered')
            },
            async handleButtonEvent() {
                let param = {
                    step: 1,
                    username: this.username,
                    password: this.password,
                    company_id: this.company_id,
                };

                await axios.post('/api/settings/test-connection', param).then(() => {
                    this.connectionVariation = 'positive';
                    this.connectionMsg = 'Connection successful.';
                    this.connected = true;

                    try {
                        //let {data} = await axios.post('/api/settings/step', param);
                        axios.post('/api/settings/step', param).then(() => {
                            this.$root.$toast(this.connectionMsg);
                            this.$router.push('/step-2');
                        }).catch(error => {
                            if (error.response) {
                                this.errors = error.response.data.errors;
                            }
                        });
                    } catch ({response}) {

                    }

                }).catch(error => {
                    this.connectionMsg = 'Connection failed.';
                    this.connectionVariation = 'negative';
                    this.connected = false;
                    this.$root.$toast(this.connectionMsg, true);
                    return false;
                });

            },
            async getData() {
                let data = {
                    step: 1
                };

                try {
                    //let response = await axios.get('/step-1');
                    let response = await axios.get('/api/settings/step?step=1');
                    response = response.data;
                    this.username = response.username;
                    this.password = response.password;
                    this.company_id = response.company_id;
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

