<template>
    <PPage narrowWidth title="Step 4/5" :breadcrumbs='[{"content":"Step 5/5", "to":"/step-3"}]'>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Add static values for API</PHeading>
                    Add Static Seller ID, Buyer ID or any other fields i.e required to calculate Excise Tax.<br /><br />
                    <PFormLayout>
                        <PFormLayoutGroup>
                            <PTextField id="title_transfer_code" label="Title Transfer Code" v-model="title_transfer_code" :error="errors['title_transfer_code'] ? errors['title_transfer_code'][0] : ''"/>
                            <PTextField id="transaction_type" label="Transaction Type" v-model="transaction_type" :error="errors['transaction_type'] ? errors['transaction_type'][0] : ''"/>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup>
                            <PTextField id="transportation_mode_code" label="Transportation Mode" v-model="transportation_mode_code" :error="errors['transportation_mode_code'] ? errors['transportation_mode_code'][0] : ''"/>
                            <PTextField id="origin" label="Origin" v-model="origin" :error="errors['origin'] ? errors['origin'][0] : ''"/>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup>
                            <PTextField id="buyer" label="Buyer ID" v-model="buyer" :error="errors['buyer'] ? errors['buyer'][0] : ''"/>
                            <PTextField id="seller" label="Seller ID" v-model="seller" :error="errors['seller'] ? errors['seller'][0] : ''"/>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup>
                            <PTextField id="unit_of_measure" label="Unit Of Measure" v-model="unit_of_measure" :error="errors['unit_of_measure'] ? errors['unit_of_measure'][0] : ''"/>
                            <PTextField id="currency" label="Currency" v-model="currency" disabled :error="errors['currency'] ? errors['currency'][0] : ''"/>
                        </PFormLayoutGroup>
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
        name: "Step5",
        data() {
            return {
                title_transfer_code: null,
                transaction_type: null,
                transportation_mode_code: null,
                origin: null,
                buyer: null,
                seller: null,
                unit_of_measure: null,
                currency: 'USD',
                errors: {},
            }
        },
        computed: {},
        methods: {
            handleAction() {
                this.$router.push('/step-3');
            },
            async handleButtonEvent() {
                let param = {
                    step: 5,
                    title_transfer_code: this.title_transfer_code,
                    transaction_type: this.transaction_type ?? null,
                    transportation_mode_code: this.transportation_mode_code ?? null,
                    origin: this.origin ?? null,
                    buyer: this.buyer ?? null,
                    seller: this.seller ?? null,
                    unit_of_measure: this.unit_of_measure ?? null,
                    currency: this.currency ?? null,
                };

                try {
                    await axios.post('/api/settings/step', param).then(() => {
                        this.$router.push('/step-5');
                    }).catch(error => {
                        if (error.response) {
                            this.errors = error.response.data.errors;
                        }
                    });
                } catch ({response}) {

                }
            },
            async getData() {
                try {
                    let response = await axios.get('/api/settings/step?step=5');
                    response = response.data;
                    this.title_transfer_code = response.title_transfer_code;
                    this.transaction_type = response.transaction_type;
                    this.transportation_mode_code = response.transportation_mode_code;
                    this.origin = response.origin;
                    this.buyer = response.buyer;
                    this.seller = response.seller;
                    this.unit_of_measure = response.unit_of_measure;
                    this.currency = response.currency;
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

