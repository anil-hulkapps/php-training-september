<template>
    <PPage narrowWidth title="Step 3/5" :breadcrumbs='[{"content":"Step 3/5", "to":step}]'>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PHeading element="h1">Choose products for excise product calculation</PHeading>
                    <p>Select the product(s) you want the app to calculate the Excise Tax.</p><br />
                    <PIcon source="CircleInformationMajor" color="interactive" @click.native="is_modal=true" style="float: left"/>
                    <br/><br/>
                    <PFormLayout>
                        <input type="file" ref="file" name="file" @change="handleFileUpload" />
                        <span v-if="file_upload_error" style="color: #E53935;">{{file_upload_error}}</span>
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
                {label: 'Avalara Synced Products', value: 2},
            ],
            option: 1,
            errors: {},
            selectedValues: [],
            value: [],
            filevar: '',
            file_upload_error: '',
            is_shopify_plus: false,
            is_partner_development: true,
            is_modal: false,
        }
    },
    computed: {
        step() {
            return (this.is_shopify_plus)
                ?'/step-2'
                :'/step-1';
        },
    },
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
            this.submitFile();
            if (this.file_upload_error !== '') {
                return;
            }
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

                this.is_shopify_plus = response.data.shopData.is_shopify_plus;
                this.is_partner_development = response.data.shopData.is_partner_development;

                response = response.data['productForExcise'];
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
span {
    style : "color: #353744";
}

.Polaris-Modal-Dialog__Modal {
    max-width: 120px !important;
}

</style>
