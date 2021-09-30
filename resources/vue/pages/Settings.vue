<template>
    <PPage separator title="Settings">
        <PLayout>
            <PLayoutAnnotatedSection title="Avalara account integration" description="Fill in the details and integrate the app with Avalara account">
                <PCard sectioned>
                    <PFormLayout>
                        <PFormLayoutGroup>
                            <PTextField id="username" @input="handleChangeCredentials" label="Username" v-model="username" :error="errors['avalaraCredential.username'] ? errors['avalaraCredential.username'][0] : ''"/>
                            <PTextField id="password" @input="handleChangeCredentials" type='password' label="Password" v-model="password" :error="errors['avalaraCredential.password'] ? errors['avalaraCredential.password'][0] : ''"/>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup>
                            <PTextField id="client_id" @input="handleChangeCredentials" label="Client ID" v-model="company_id" :error="errors['avalaraCredential.company_id'] ? errors['avalaraCredential.company_id'][0] : ''"/>
                            <div></div>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup>
                            <PButton @click="testConnection">Test connection</PButton>
                        </PFormLayoutGroup>
                        <PFormLayoutGroup v-if="connectionMsg">
                            <PTextStyle :variation="connectionVariation">{{ connectionMsg }}</PTextStyle>
                        </PFormLayoutGroup>
                    </PFormLayout>
                </PCard>
            </PLayoutAnnotatedSection>
            <PLayoutAnnotatedSection title="Map custom strings" description="Map the custom strings for orders & line item information to display in the Avalara account">
                <PCard>
                    <PCardSection title="Order Information">
                        <PFormLayout>
                            <PFormLayoutGroup>
                                <PSelect id="custom_string_1" label="Custom String 1" :options="customStringOptions" v-model="orderCustomString1" @change="handleChangeOptionSelect('orderCustomString1', $event)"/>
                                <PSelect id="custom_numeric_1" label="Custom Numeric 1" :options="customNumericOptions" v-model="orderCustomNumeric1" @change="handleChangeOptionSelect('orderCustomNumeric1', $event)"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PSelect id="custom_string_2" label="Custom String 2" :options="customStringOptions" v-model="orderCustomString2" @change="handleChangeOptionSelect('orderCustomString2', $event)"/>
                                <PSelect id="custom_numeric_2" label="Custom Numeric 2" :options="customNumericOptions" v-model="orderCustomNumeric2" @change="handleChangeOptionSelect('orderCustomNumeric2', $event)"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PSelect id="custom_string_3" label="Custom String 3" :options="customStringOptions" v-model="orderCustomString3" @change="handleChangeOptionSelect('orderCustomString3', $event)"/>
                                <PSelect id="custom_numeric_3" label="Custom Numeric 3" :options="customNumericOptions" v-model="orderCustomNumeric3" @change="handleChangeOptionSelect('orderCustomNumeric3', $event)"/>
                            </PFormLayoutGroup>
                        </PFormLayout>
                    </PCardSection>
                    <PCardSection title="line Items">
                        <PFormLayout>
                            <PFormLayoutGroup>
                                <PSelect id="custom_string1" label="Custom String 1" :options="customStringOptions" v-model="lineitemCustomString1" @change="handleChangeOptionSelect('lineitemCustomString1', $event)"/>
                                <PSelect id="custom_numeric1" label="Custom Numeric 1" :options="customNumericOptions" v-model="lineitemCustomNumeric1" @change="handleChangeOptionSelect('lineitemCustomNumeric1', $event)"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PSelect id="custom_string2" label="Custom String 2" :options="customStringOptions" v-model="lineitemCustomString2" @change="handleChangeOptionSelect('lineitemCustomString2', $event)"/>
                                <PSelect id="custom_numeric2" label="Custom Numeric 2" :options="customNumericOptions" v-model="lineitemCustomNumeric2" @change="handleChangeOptionSelect('lineitemCustomNumeric2', $event)"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PSelect id="custom_string3" label="Custom String 3" :options="customStringOptions" v-model="lineitemCustomString3" @change="handleChangeOptionSelect('lineitemCustomString3', $event)"/>
                                <PSelect id="custom_numeric3" label="Custom Numeric 3" :options="customNumericOptions" v-model="lineitemCustomNumeric3" @change="handleChangeOptionSelect('lineitemCustomNumeric3', $event)"/>
                            </PFormLayoutGroup>
                        </PFormLayout>
                    </PCardSection>
                </PCard>
            </PLayoutAnnotatedSection>
            <PLayoutAnnotatedSection title="Add static values for API call" description="Add Static Seller ID, Buyer ID, or any other fields required to calculate the Excise Tax">
                <PCard>
                    <PCardSection>
                        <PFormLayout>
                            <PFormLayoutGroup>
                                <PTextField id="title_transfer_code" label="Title Transfer Code" v-model="title_transfer_code" :error="errors['staticSettings.title_transfer_code'] ? errors['staticSettings.title_transfer_code'][0] : ''"/>
                                <PTextField id="transaction_type" label="Transaction Type" v-model="transaction_type" :error="errors['staticSettings.transaction_type'] ? errors['staticSettings.transaction_type'][0] : ''"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PTextField id="transportation_mode_code" label="Transportation Mode" v-model="transportation_mode_code" :error="errors['staticSettings.transportation_mode_code'] ? errors['staticSettings.transportation_mode_code'][0] : ''"/>
                                <PTextField id="origin" label="Origin" v-model="origin" :error="errors['staticSettings.origin'] ? errors['staticSettings.origin'][0] : ''"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PTextField id="buyer" label="Buyer ID" v-model="buyer" :error="errors['staticSettings.buyer'] ? errors['staticSettings.buyer'][0] : ''"/>
                                <PTextField id="seller" label="Seller ID" v-model="seller" :error="errors['staticSettings.seller'] ? errors['staticSettings.seller'][0] : ''"/>
                            </PFormLayoutGroup>
                            <PFormLayoutGroup>
                                <PTextField id="unit_of_measure" label="Unit Of Measure" v-model="unit_of_measure" :error="errors['staticSettings.unit_of_measure'] ? errors['staticSettings.unit_of_measure'][0] : ''"/>
                                <PTextField id="currency" label="Currency" disabled v-model="currency" :error="errors['staticSettings.currency'] ? errors['staticSettings.currency'][0] : ''"/>
                            </PFormLayoutGroup>
                        </PFormLayout>
                    </PCardSection>
                    <PCardSection>
                        <PFormLayout>
                            <PFormLayoutGroup>
                                <template v-for="(addiField, index) in additionalStaticField">
                                    <PSelect :id="'custom_field'+index" :label="'Custom field '+parseInt(index+1)" :options="customfieldOptions" v-model="addiField.option" @change="handleChangeAdditional('orderCustomString1', $event, index)"/>
                                    <PStack>
                                        <PStackItem>
                                            <PTextField :id="'custom_value'+index" connected :label="'Static value '+parseInt(index+1)" v-model="addiField.value">
                                            </PTextField>
                                        </PStackItem>
                                        <PStackItem>
                                            <PButton style="margin-top: 25px" icon="DeleteMinor" @click="removeAdditionalField(index)"></PButton>
                                        </PStackItem>
                                    </PStack>
                                </template>
                                <PButton @click="addMore">Add more</PButton>
                            </PFormLayoutGroup>
                        </PFormLayout>
                    </PCardSection>
                </PCard>
            </PLayoutAnnotatedSection>
            <PLayoutAnnotatedSection title="Choose products for excise calculation" description="Select the product(s) you want the app to calculate the Excise Tax.">
                <PCard sectioned>
                    <PFormLayout>
                        <PSelect :options="productForExciseOptions" label="Select products" v-model="productForExciseOption" @change="handleChangeProductForExcise"/>
                        <template v-if="productForExciseOption === 2">
                            <PModal :primaryAction="{content: 'Save', onAction: submitFile}" :secondaryActions="[{content:'Cancel', onAction: () => {is_active_modal = false}}]" title="Import products file" :open="is_active_modal" sectioned @close="is_active_modal = false">
                                <PFormLayout>
                                    <input type="file" ref="file" name="file" @change="handleFileUpload" />
                                    <span v-if="file_upload_error" style="color: #E53935;">{{file_upload_error}}</span>
                                </PFormLayout>
                            </PModal>
                            <PButton @click="is_active_modal = true">Import products</PButton>
                        </template>
                    </PFormLayout>
                </PCard>
            </PLayoutAnnotatedSection>
            <PLayoutAnnotatedSection title="Product Code" description="Determine the placement for the Product Code for Avalara Excise Tax calculation">
                <PCard sectioned>
                    <PCheckbox
                        helpText="We will calculate excise tax based on shopify product SKU, So to make sure you have mapped all of the product SKU with the excise product category with Avalara"
                        v-model="confirm_for_product_code" :checked="confirm_for_product_code" :disabled="true" @change="handleCheckboxConfirm" id="helptext_checkbox_2" label="Confirm for product code" />

                </PCard>
            </PLayoutAnnotatedSection>
            <PLayoutAnnotatedSection title="Failover Notification" description="Define a default course of notification during a failure checkout">
                <PCard>
                    <PCardSection title="Place an order with due Excise Tax">
                        <PFormLayout>
                            <PTextField @input="handleChange" :minHeight="100" v-model="dueExciseNotification" multiline id="input_field" label="Note to display in checkout" />
                                <PSelect label="Order identifier" :options="actionIdentifierOptions" v-model="actionIdentifier" @change="handleChangeActionSelect('actionIdentifier', $event)"/>
                                <tags-input element-id="tags" v-model="dueExciseTags" placeholder="" :typeahead="true"></tags-input>
                        </PFormLayout>
                    </PCardSection>
                    <PCardSection title="Unauthorize location">
                        <PFormLayout>
                            <PTextField @input="handleChange" :minHeight="100" v-model="unauthorizeNotification" multiline id="input_field2" label="Note to display in checkout" />
                            <PSelect label="Order identifier" :options="actionIdentifierOptions" v-model="actionIdentifier" @change="handleChangeActionSelect('actionIdentifier', $event)"/>
                            <tags-input element-id="tags" v-model="unauthorizeTags" placeholder="" :typeahead="true"></tags-input>
                        </PFormLayout>
                    </PCardSection>
                </PCard>
            </PLayoutAnnotatedSection>
            <PLayoutSection>
                <PPageActions :primaryAction="{content: 'Save', onAction: submitForm}" :secondaryActions="[{content: 'Cancel'}]"></PPageActions>
            </PLayoutSection>
        </PLayout>
    </PPage>
</template>

<script>
    import VoerroTagsInput from '@voerro/vue-tagsinput';
    export default {
        components: {"tags-input": VoerroTagsInput},
        name: "Settings",
        data() {
            return {
                primaryAction: {
                    content: 'Save',
                    onAction: this.submitForm,
                },
                productForExciseOptions: [
                    {label: 'Avalara Synced Products', value: 2},
                ],
                orderOptions: [
                    {label: 'Start With', value: 1},
                    {label: 'End With', value: 2},
                    {label: 'Contains', value: 3},
                ],
                customStringOptions: [
                    {label: 'Customer Name', value: 5},
                    {label: 'Order Number', value: 2},
                    {label: 'Customer Contact Number', value: 3},
                    {label: 'Customer Email', value: 4},
                    {label: 'None', value: 1},
                ],
                customfieldOptions: [
                    {label: 'Previous Seller', value: 1},
                    {label: 'Next Buyer', value: 2},
                    {label: 'Middleman', value: 3},
                    /*{label: 'FuelUseCode', value: 4},*/
                ],
                customNumericOptions: [
                    {label: 'Order Total', value: 3},
                    {label: 'Order Quantity', value: 2},
                    {label: 'None', value: 1},
                ],
                optionsPattern: [
                    {label: 'By Tag Pattern', value: 1},
                    {label: 'By Product SKU', value: 2},
                ],
                productCodeOptions: [
                    {label: 'Start With', value: 1},
                    {label: 'End With', value: 2},
                    {label: 'Contains', value: 3},
                ],
               actionOptions: [
                    {label: 'Place an order with due Excise Tax', value: 1},
                    {label: 'Unauthorize location', value: 3},
                ],
                actionIdentifierOptions: [
                    {label: 'Tag', value: 1},
                ],
                username: null,
                password: null,
                company_id: null,
                confirm_order: true,
                confirm_for_product_code: true,
                orderCustomString1: 1,
                orderCustomString2: 1,
                orderCustomString3: 1,
                orderCustomNumeric1: 1,
                orderCustomNumeric2: 1,
                orderCustomNumeric3: 1,
                lineitemCustomString1: 1,
                lineitemCustomString2: 1,
                lineitemCustomString3: 1,
                lineitemCustomNumeric1: 1,
                lineitemCustomNumeric2: 1,
                lineitemCustomNumeric3: 1,

                title_transfer_code: null,
                transaction_type: null,
                transportation_mode_code: null,
                origin: null,
                buyer: null,
                seller: null,
                unit_of_measure: null,
                currency: null,
                additionalStaticField: [],

                productForExciseOption: 1,
                productForExciseSelectedValues: [],
                dueExciseTags: [],
                unauthorizeTags: [],

                option: 1,
                identifier: 1,
                actionSelect: 1,
                actionIdentifier: 1,
                productCodeOption: 1,
                productCodeValue: null,
                tagValue: '',
                checkoutMessage: '',
                dueExciseNotification: '',
                unauthorizeNotification: '',
                tempcheckoutMessage: {},

                errors: {},

                connectionMsg: '',
                connected: false,
                connectionVariation: 'negative',

                is_active_modal: false,
                filevar: '',
                file_upload_error: '',
            }
        },
        watch: {
            additionalStaticField: {
                deep: true,
                handler() {
                }
            },
        },
        methods: {
            async submitForm() {
                let param = {
                    step: 'settings',
                    avalaraCredential: {
                        username: this.username,
                        password: this.password,
                        company_id: this.company_id,
                    },
                    productForExcise: {
                        option: this.productForExciseOption != null ? this.productForExciseOption : 1,
                        value: this.productForExciseSelectedValues.length > 0 ? this.productForExciseSelectedValues : null,
                    },
                    productIdentifierForExcise: {
                        identifier: this.identifier != null ? this.identifier : 1,
                        option: this.productCodeOption != null ? this.productCodeOption : 1,
                        value: this.productCodeValue,
                    },
                    staticSettings: {
                        title_transfer_code: this.title_transfer_code,
                        transaction_type: this.transaction_type ?? null,
                        transportation_mode_code: this.transportation_mode_code ?? null,
                        origin: this.origin ?? null,
                        buyer: this.buyer ?? null,
                        seller: this.seller ?? null,
                        unit_of_measure: this.unit_of_measure ?? null,
                        currency: this.currency ?? null,
                        order_custom_string1: this.orderCustomString1 ?? null,
                        order_custom_string2: this.orderCustomString2 ?? null,
                        order_custom_string3: this.orderCustomString3 ?? null,
                        order_custom_numeric1: this.orderCustomNumeric1 ?? null,
                        order_custom_numeric2: this.orderCustomNumeric2 ?? null,
                        order_custom_numeric3: this.orderCustomNumeric3 ?? null,
                        lineitem_custom_string1: this.lineitemCustomString1 ?? null,
                        lineitem_custom_string2: this.lineitemCustomString2 ?? null,
                        lineitem_custom_string3: this.lineitemCustomString3 ?? null,
                        lineitem_custom_numeric1: this.lineitemCustomNumeric1 ?? null,
                        lineitem_custom_numeric2: this.lineitemCustomNumeric2 ?? null,
                        lineitem_custom_numeric3: this.lineitemCustomNumeric3 ?? null,
                        confirm_order: this.confirm_order == true ? 1 : 0,
                        additionalStaticField: this.additionalStaticField,
                    },
                    failoverCheckout: {
                        actionSelect: this.actionSelect ?? null,
                        actionIdentifier: this.actionIdentifier ?? null,
                        tagValue: this.tagValue,
                        //checkoutMessage: this.checkoutMessage,
                        checkoutMessage: this.tempcheckoutMessage,
                        dueExciseNotification: this.dueExciseNotification,
                        unauthorizeNotification: this.unauthorizeNotification,
                        dueExciseTags: this.dueExciseTags.length > 0 ? this.dueExciseTags : null,
                        unauthorizeTags: this.unauthorizeTags.length > 0 ? this.unauthorizeTags : null,
                    }
                };

                //console.log(param);
                try {
                    await axios.post('/api/settings/step', param).then(() => {
                        this.$root.$toast('Saved');
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
                    let response = await axios.get('/api/settings/step?step=settings');
                    response = response.data;

                    // set avalara credentials
                    this.username = response.avalaraCredential.username;
                    this.password = response.avalaraCredential.password;
                    this.company_id = response.avalaraCredential.company_id;
                    this.productForExciseOption = response.productForExcise.option;
                    this.productForExciseSelectedValues = response.productForExcise.value != null ? JSON.parse(response.productForExcise.value) : [];

                    // set static values
                    this.title_transfer_code = response.staticSettings.title_transfer_code;
                    this.transaction_type = response.staticSettings.transaction_type;
                    this.transportation_mode_code = response.staticSettings.transportation_mode_code;
                    this.origin = response.staticSettings.origin;
                    this.buyer = response.staticSettings.buyer;
                    this.seller = response.staticSettings.seller;
                    this.unit_of_measure = response.staticSettings.unit_of_measure;
                    this.currency = response.staticSettings.currency;
                    this.orderCustomString1 = response.staticSettings.order_custom_string1 ? parseInt(response.staticSettings.order_custom_string1) : 1;
                    this.orderCustomString2 = response.staticSettings.order_custom_string2 ? parseInt(response.staticSettings.order_custom_string2) : 1;
                    this.orderCustomString3 = response.staticSettings.order_custom_string3 ? parseInt(response.staticSettings.order_custom_string3) : 1;
                    this.orderCustomNumeric1 = response.staticSettings.order_custom_numeric1 ? parseInt(response.staticSettings.order_custom_numeric1) : 1;
                    this.orderCustomNumeric2 = response.staticSettings.order_custom_numeric2 ? parseInt(response.staticSettings.order_custom_numeric2) : 1;
                    this.orderCustomNumeric3 = response.staticSettings.order_custom_numeric3 ? parseInt(response.staticSettings.order_custom_numeric3) : 1;
                    this.lineitemCustomString1 = response.staticSettings.lineitem_custom_string1 ? parseInt(response.staticSettings.lineitem_custom_string1) : 1;
                    this.lineitemCustomString2 = response.staticSettings.lineitem_custom_string2 ? parseInt(response.staticSettings.lineitem_custom_string2) : 1;
                    this.lineitemCustomString3 = response.staticSettings.lineitem_custom_string3 ? parseInt(response.staticSettings.lineitem_custom_string3) : 1;
                    this.lineitemCustomNumeric1 = response.staticSettings.order_custom_numeric1 ? parseInt(response.staticSettings.order_custom_numeric1) : 1;
                    this.lineitemCustomNumeric2 = response.staticSettings.order_custom_numeric2 ? parseInt(response.staticSettings.order_custom_numeric2) : 1;
                    this.lineitemCustomNumeric3 = response.staticSettings.order_custom_numeric3 ? parseInt(response.staticSettings.order_custom_numeric3) : 1;
                    this.confirm_order = response.staticSettings.confirm_order == 1 ? true : false;

                    let tempStatic = [];
                    let counter = 1;
                    for (const [key, value] of Object.entries(response.staticSettings)) {
                        if(key.includes('additional_custom_value')) {
                            tempStatic['additional_custom_option'.concat(counter)] = value;
                            counter = counter+1;
                        }
                    }
                    for (const [key, value] of Object.entries(response.staticSettings)) {
                        if(key.includes('additional_custom_option'))
                            this.additionalStaticField.push({customField: 'additionalCustomField'+parseInt(this.additionalStaticField.length + 1), option: parseInt(value), value: tempStatic[key]});
                    }


                    //set product code
                    this.identifier = response.productIdentifierForExcise ? response.productIdentifierForExcise.identifier : 1;
                    this.productCodeOption = response.productIdentifierForExcise ? response.productIdentifierForExcise.option : 1;
                    this.productCodeValue = response.productIdentifierForExcise ? response.productIdentifierForExcise.value : '';

                    //set failover checkout
                    this.actionSelect = response.failoverCheckout.action;
                    this.actionIdentifier = response.failoverCheckout.identifier;
                    this.tagValue = response.failoverCheckout.value;
                    this.dueExciseNotification = response.failoverCheckout[0].message;
                    this.unauthorizeNotification = response.failoverCheckout[1].message;
                    this.dueExciseTags = response.failoverCheckout[0].tags != null ? JSON.parse(response.failoverCheckout[0].tags) : [];
                    this.unauthorizeTags = response.failoverCheckout[1].tags != null ? JSON.parse(response.failoverCheckout[1].tags) : [];

                } catch ({response}) {

                }
            },
            handleChangeProductForExcise(e) {
                this.productForExciseOption = e;
            },
            handleChangeOptionSelect(field, e) {
                this[field] = e;
            },
            handleChangeAdditional(field, e, index) {
                this[field] = e;
                this.additionalStaticField[index].option = e;
                console.log(this.additionalStaticField[index]);
            },
            handleChangeActionSelect(field, e) {
                this[field] = e;

                if (field == 'actionSelect') {
                    this.checkoutMessage = this.tempcheckoutMessage[this.actionSelect];
                }
            },
            handleChange(e) {
                this.tempcheckoutMessage[this.actionSelect] = e;
            },
            handleCheck($event) {
                this.confirm_order = $event.checked;
            },
            handleCheckboxConfirm($event) {
                this.confirm_for_product_code = $event.checked;
            },
            addMore() {
                this.additionalStaticField.push({customField: 'additionalCustomField'+parseInt(this.additionalStaticField.length + 1), option: 1, value: ''});
            },
            removeAdditionalField(index) {
                this.additionalStaticField.splice(index, 1);
            },
            async testConnection()
            {
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
                }).catch(error => {
                    this.connectionMsg = 'Connection failed.';
                    this.connectionVariation = 'negative';
                    this.connected = false;

                    return false;
                });
            },
            handleChangeCredentials() {
                this.connectionMsg = 'Before save credentials please test connection';
                this.connectionVariation = 'negative';
            },
            submitFile() {
                let that = this;
                let formData = new FormData();
                if(that.filevar.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    that.file_upload_error = 'Invalid file type.';
                } else {
                    that.file_upload_error = '';
                    formData.append('product_type', 2);
                    formData.append('file', this.filevar);
                    axios.post( '/import-product', formData, {headers: {'Content-Type': 'multipart/form-data'}}
                    ).then(function(response){
                        that.is_active_modal = false;
                        that.$root.$toast('Products have been imported.');
                    }).catch(function(response){
                        console.log('FAILURE!!');
                    });
                }
            },
            handleFileUpload(){
                this.filevar = this.$refs.file.files[0];
            }
        },
        async created() {
            await this.getData();
        }
    }
</script>

<style scoped>

</style>
