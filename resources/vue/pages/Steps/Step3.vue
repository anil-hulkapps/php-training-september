<template>
    <PPage narrowWidth title="Step 3/5" :breadcrumbs='[{"content":"Step 3/5", "to":"/step-2"}]'>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Choose products for excise product calculation</PHeading>
                    Select the product(s) you want the app to calculate the Excise Tax.<br /><br />
                    <PFormLayout>
                        <PSelect :options="options" label="Select products" v-model="option" @change="handleChangeOptionSelect"/>
                        <!--<tags-input element-id="values" v-model="selectedValues" placeholder="" :typeahead="true" v-if="option > 2"></tags-input>-->
                        <!--<PTextField label="Select collections" placeholder="Search collection" v-if="option > 2"/>-->
                        <!--<PButtonGroup>
                            <PTag :tag="{value: 'New', key: 'New'}" />
                        </PButtonGroup>-->
                        <template v-if="option === 2">
                            <PModal :primaryAction="{content: 'Save', onAction: submitFile}" :secondaryActions="[{content:'Cancel', onAction: () => {is_active_modal = false}}]" title="Import products file" :open="is_active_modal" sectioned @close="is_active_modal = false">
                                <PFormLayout>
                                    <input type="file" ref="file" name="file" @change="handleFileUpload" />
                                    <span v-if="file_upload_error" style="color: #E53935;">{{file_upload_error}}</span>
                                </PFormLayout>
                            </PModal>
                            <PButton @click="is_active_modal = true">Import products</PButton>
                        </template>
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
    import VoerroTagsInput from '@voerro/vue-tagsinput';

    export default {
        components: {"tags-input": VoerroTagsInput},
        name: "Step3",
        data() {
            return {
                options: [
                    //{label: 'All', value: 1},
                    {label: 'Avalara Synced Products', value: 2},
                    // {label: 'By Tag', value: 3},
                    // {label: 'By Type', value: 4},
                    // {label: 'By Vendor', value: 5},
                ],
                option: 1,
                errors: {},
                selectedValues: [],
                value: [],
                is_active_modal: false,
                filevar: '',
                file_upload_error: '',
            }
        },
        computed: {},
        methods: {
            handleAction() {
                this.$router.push('/step-2');
            },
            async handleButtonEvent() {
                let param = {
                    step: 3,
                    option: this.option != null ? this.option : 1,
                    value: this.selectedValues.length > 0 ? this.selectedValues : null,
                };

                try {
                    await axios.post('/api/settings/step', param).then(() => {
                        this.$router.push('/step-4');
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
                    let response = await axios.get('/api/settings/step?step=3');
                    response = response.data;
                    this.option = response.option;
                    this.selectedValues = response.value != null ? JSON.parse(response.value) : [];
                } catch ({response}) {

                }
            },
            handleChangeOptionSelect(e) {
                this.option = e;
            },
            submitFile(){
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

