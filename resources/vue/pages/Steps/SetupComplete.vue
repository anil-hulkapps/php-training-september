<template>
    <PPage narrowWidth>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Product Code</PHeading>
                    Determine the placement for the Product Code for Avalara Excise Tax calculation<br /><br />
                    <PFormLayout>
                        <PCheckbox
                            helpText="We will calculate excise tax based on shopify product SKU, So to make sure you have mapped all of the product SKU with the excise product category with Avalara"
                            v-model="confirm_for_product_code" :checked="confirm_for_product_code" @change="handleCheck" id="helptext_checkbox_1" label="Confirm for product code" />
                    </PFormLayout>
                </PCardSection>
                <PCardSection>
                    <PHeading element="h1">You're all set.</PHeading>
                    Feel free to reach out if you need any further help.<br /><br />
                    <PButton :disabled="confirm_for_product_code ? false : true" primary @click="handleButtonEvent()">Get Started</PButton>
                </PCardSection>
            </PCard>
            <!--<PCard subdued>
                <PCardSection>
                    <PHeading element="h1">You're all set.</PHeading>
                    Feel free to reach out if you need any further help.<br /><br />
                    <PButton primary @click="handleButtonEvent()">Get Started</PButton>
                </PCardSection>
            </PCard>-->
        </PLayout>
    </PPage>
</template>

<script>

    export default {
        name: "SetupComplete",
        data() {
            return {
                confirm_for_product_code: true,
            }
        },
        computed: {},
        methods: {
            async handleButtonEvent() {
                let param = {
                    step: 'get_started',
                    is_app_setup: 1,
                };

                try {
                    await axios.post('/api/settings/step', param).then(() => {
                        this.$router.push('/transactions');
                    }).catch(error => {
                        if (error.response) {
                            this.errors = error.response.data.errors;
                        }
                    });
                } catch ({response}) {

                }
            },
            handleCheck($event) {
                this.confirm_for_product_code = $event.checked;
            },
        },
        async created() {
        }
    }
</script>

<style scoped>

</style>

