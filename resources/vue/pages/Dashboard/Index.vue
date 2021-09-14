<template>
    <PPage>
        <PLayout sectioned>
            <PHeading>Helen, Thank you for installing our app</PHeading>
            <p>Get ready to experience the freedom to stay focused on your business, not on tax management.</p><br /><br />
            <PCard sectioned title="Introduction">
                Welcome to Praella! We give Shopify stores the mechanisms to thrive. This app is the only connector that bridges Avalara with Shopify and supports brands set up automated Excise Tax compliance.
            </PCard>
            <PCard sectioned title="How it works">
                The app closely works with Avalara to calculate Excise Tax for all the Shopify Plus transactions triggered by what your customers buy at the checkout.
            </PCard>
            <PCard sectioned title="Setup">
                <PCardSection>
                    <PCardSubsection>
                        The app requires a few adjustments to the theme code. Book a call & get direct help from our customer support with the tweaks.<br /><br />
                        Ensure to stay equipped with the below list of details during the onboarding process:<br /><br />
                        1. Avalara Account<br />
                        2. Product configured in Avalara with excise taxation<br />
                        3. Test3<br />
                        4. Test4<br /><br /><br />
                        <PButton primary>Book a call</PButton><br /><br />
                        <router-link to="/dashboard">I am technical sound and I want to configure on my own</router-link>
                        <!--<PLink to="/dashboard">I am technical sound and I want to configure on my own</PLink>-->
                    </PCardSubsection>
                </PCardSection>
            </PCard>
        </PLayout>
    </PPage>
</template>

<script>

    import download from 'downloadjs';
    export default {
        name: "Dashboard",
        data() {
            return {
                headings: [
                    {content: '', value: 'state', type: 'checkbox', sortable: false},
                    {content: 'State', value: 'state', type: 'text', sortable: true},
                    {content: 'Zip', value: 'zip', type: 'text', sortable: false},
                    {content: 'Shipping Status', value: 'shipping_status', type: 'text', sortable: false}
                ],
                rows: [],
                options: {
                    limit: 10,
                    page: null,
                    sort_by: 'id',
                    sort_order: 'desc',
                    state: null,
                    search: null,
                },
                disabledSyncBtn: false,
                states: [],
                state_zipcode_ids: [],
                bulkActionsActive: false,
                showImportModal: false,
                form: {
                    importFile: null
                },
                loading: true,
                emptySearchImage: process.env.MIX_APP_URL + '/images/empty-search-state.svg',
                selected: [],
                selectedAll: false,
                currentState: null,
                csv: {
                    processing: false
                },
            }
        },
        computed: {},
        methods: {
            importAction() {
                this.showImportModal = true;
            },
            hideImportModal() {
                this.showImportModal = false;
            },
            handleFileUpload() {
                this.form.importFile = this.$refs.file.files[0];
            },
            async importStateZipCodes() {
                try {
                    let formData = new FormData();
                    formData.append('importFile', this.form.importFile);
                    let {data} = await axios.post(process.env.MIX_APP_URL + '/api/dashboard/import', formData, {headers: {'Content-Type': 'multipart/form-data'}});
                    this.form.samplekit_csv = '';
                    this.$root.$toast(data.message);
                    this.hideImportModal();
                    this.getStateZipCodes();
                    this.getStates();
                } catch ({response}) {
                    if (response.data.message) {
                        for (const [key, value] of Object.entries(response.data.errors)) {
                            this.errors[key] = value[0];
                        }
                        if (!response.data.errors) {
                            this.$root.$toast(response.data.message, true);
                        }
                    }
                }
            },
            async exportAction() {
                /*let shop, hash;
                let hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
                for (let i = 0; i < hashes.length; i++) {
                    hash = hashes[i].split('=');
                    if (hash[0] == 'shop')
                        shop = hash[0] + '=' + hash[1];
                }
                let url = process.env.MIX_APP_URL + '/api/dashboard/export?state=' + this.options.state;
                window.location.href = url;*/
                let {data} = await axios.get(process.env.MIX_APP_URL + '/api/dashboard/export?state=' + this.options.state);
                download(data, "state_zipcode.csv", "application/Csv");
            },
            async getStates() {
                let {data} = await axios.get(process.env.MIX_APP_URL + '/api/dashboard/states');

                this.states.push({value: null, label: 'Select All'});
                if (data.length > 0) {
                    data.forEach(allState => {
                        this.states.push({value: allState.id, label: allState.state});
                    });
                }
            },
            handleState(stateId) {
                this.options.state = stateId;

                this.states.forEach(allState => {
                    if (allState.value && allState.value == stateId) this.currentState = allState.label;
                    else if (allState.label === 'Select All') this.currentState = null;
                });

                this.getStateZipCodes();
            },
            async getStateZipCodes() {
                let params = {
                    limit: this.options.limit,
                    page: this.options.current_page,
                    sort_by: this.options.sort_by,
                    sort_order: this.options.sort_order,
                    state: this.options.state,
                    search: this.options.search,
                };

                let {data} = await axios.get(process.env.MIX_APP_URL + '/api/dashboard', {params: params});
                this.rows = data.data;
                delete data.data;
                this.options.current_page = data.current_page;
                this.options.prev_page_url = data.prev_page_url;
                this.options.next_page_url = data.next_page_url;
                this.options.total = data.total;
            },
            onPrevious() {
                this.options.current_page--;
                this.getStateZipCodes();
            },
            onNext() {
                this.options.current_page++;
                this.getStateZipCodes();
            },
            searchZipCodes(event) {
                this.options.search = event;
                this.getStateZipCodes();
            },
            toggleSelected(item) {
                this.selected = item.selected ? this.rows.map(state_zipcode => state_zipcode.id) : [];
                this.selectedAll = item.selectedMore;
            },
            toggleStatusActive() {
                this.changeStatus(true);
            },
            toggleStatusInactive() {
                this.changeStatus(false);
            },
            async changeStatus(status) {
                let params = {
                    status: status,
                    selected: this.selected,
                    selected_all: this.selectedAll
                };
                let {data} = await axios.post(process.env.MIX_APP_URL + '/api/dashboard/update-shipping-status', params);
                if (data) {
                    this.selected = [];
                    this.selectedAll = false;
                    this.getStateZipCodes();
                    this.$root.$toast(data.message);
                }
            },
            updateSelected(id, checked) {
                if (checked) this.selected.push(id);
                if (!checked) this.selected.splice(this.selected.indexOf(id), 1);
            },
            async downloadSampleFile() {
                let {data} = await axios.get(process.env.MIX_APP_URL + '/api/dashboard/download-sample-file');
                download(data, "sample-file.csv", "application/Csv");
            }
        },
        created() {
            //this.getStateZipCodes();
            //this.getStates();
        }
    }
</script>

<style scoped>

</style>

