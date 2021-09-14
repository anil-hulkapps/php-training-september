<template>
    <PPage narrowWidth title="Step 4/6" :breadcrumbs='[{"content":"Step 4/6", "to":"/step-3"}]'>
        <PLayout sectioned>
            <PCard subdued>
                <PCardSection>
                    <PHeading element="h1">Product Code</PHeading>
                    Determine the placement for the Product Code for Avalara Excise Tax calculation<br /><br />
                    <PFormLayout>
                        <PCheckbox
                            helpText="We will calculate excise tax based on shopify product SKU, So to make sure you have mapped all of the product SKU with the excise product category with Avalara"
                            v-model="confirm_for_product_code" :checked="confirm_for_product_code" @change="handleCheck" id="helptext_checkbox_1" label="Confirm for product code" />
                        <!--<PSelect :options="optionsPattern" label="Search products" v-model="identifier" @change="handleChangePatternSelect"/>
                        <PFormLayoutGroup>
                            <PSelect :options="options" v-model="option" @change="handleChangeOptionSelect"/>
                            <PTextField v-model="value" :error="errors['value'] ? errors['value'][0] : ''"/>
                        </PFormLayoutGroup>-->
                    </PFormLayout>
                </PCardSection>
            </PCard>
            <PLayoutSection v-if="confirm_for_product_code">
                <PPageActions :primaryAction="{content: 'Continue', onAction: handleButtonEvent}"></PPageActions>
            </PLayoutSection>
        </PLayout>
    </PPage>
</template>

<script>

    export default {
        name: "Step4",
        data() {
            return {
                optionsPattern: [
                    {label: 'By Tag Pattern', value: 1},
                    {label: 'By Product SKU', value: 2},
                ],
                options: [
                    {label: 'Start With', value: 1},
                    {label: 'End With', value: 2},
                    {label: 'Contains', value: 3},
                ],
                identifier: 1,
                option: 1,
                confirm_for_product_code: true,
                value: null,
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
                    step: 4,
                    identifier: this.identifier != null ? this.identifier : 1,
                    option: this.option != null ? this.option : 1,
                    value: this.value,
                    confirm: this.confirm_for_product_code == true ? 1 : 0,
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
                    let response = await axios.get('/api/settings/step?step=4');
                    response = response.data;
                    this.identifier = response.identifier;
                    this.option = response.option;
                    this.value = response.value;
                    this.confirm_for_product_code = response.confirm == 1 ? true : false;
                } catch ({response}) {

                }
            },
            handleCheck($event) {
                this.confirm_for_product_code = $event.checked;
            },
            handleChangePatternSelect(e) {
                this.identifier = e;
            },
            handleChangeOptionSelect(e) {
                this.option = e;
            }
        },
        async created() {
            await this.getData();
        }
    }
</script>

<style scoped>

</style>

