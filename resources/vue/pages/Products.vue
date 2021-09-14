<template>
    <PPage fullWidth title="Products">
        <PButtonGroup slot="primaryAction">
            <pDatePicker id="date_range" format="MM/DD/YYYY" :dateRange="date_range"
                         opens="left" @change="handleDateChange"></pDatePicker>
        </PButtonGroup>
        <PLayout sectioned>
            <PCard>
                <PCardSection>
                    <PDataTable
                        :columnContentTypes='["text","numeric"]'
                        :sort="sort"
                        :hasPagination="hasPagination"
                        @input-filter-changed="handleSearch"
                        :pagination="pagination"
                        @sort-changed="handleSortChange"
                        :headings="headings"
                        :hasFilter="true"
                        :loading="isDataLoading"
                        searchPlaceholder="Search by Product name"
                        :footerContent="(!products.length) ? 'No Products Found' : tableFooter">
                        <template slot="body">
                            <tr v-for="(product, index) in products" :key="`row-${index}`" class="Polaris-DataTable__TableRow blackout-days">
                                <!--<td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                    <template><PImage :source="product.image_url" :alt="product.title" height="40" width="40" /> {{ product.title }}</template>
                                </td>-->
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                    <PStack alignment="center">
                                        <PStackItem><PThumbnail
                                            :source="product.image_url"
                                            :alt="product.title"
                                        /></PStackItem>
                                        <PStackItem fill=""><PTextStyle>{{ product.title }}</PTextStyle></PStackItem>
                                    </PStack>
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle" style="text-align: right">
                                    <template v-if="product.excise_by_products.length > 0">${{ product.excise_by_products[0].total_excise_tax }}</template>
                                    <template v-else>$0</template>
                                </td>
                            </tr>
                        </template>
                </PDataTable>
                </PCardSection>
            </PCard>
        </PLayout>
    </PPage>
</template>

<script>
    export default {
        name: "Products",
        data() {
            return {
                //statsDate: [helper.currentDateTime(2).startOf('week').format('YYYY-MM-DD'), helper.currentDateTime(2).format('YYYY-MM-DD')],
                queryParams: {
                    page: 1,
                    search: '',
                    start_date: moment(new Date()).subtract(30,'day').format('MM/DD/YYYY'),
                    end_date: moment(new Date()).format('MM/DD/YYYY'),
                    sortBy: 'title',
                    sortOrder: 'ascending',
                },
                products: [],
                hasPagination: false,
                isDataLoading:false,
                tableFooter: '',
                date_range: {
                    startDate: moment(new Date()).subtract(30,'day').format('MM/DD/YYYY'),
                    endDate: moment(new Date()).format('MM/DD/YYYY')
                },
                headings: [{
                    content: 'Product name',
                    value: 'title',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Total tax amount',
                    value: 'total_excise_tax',
                    type: 'text',
                    sortable: false,
                }],
                pagination: {
                    hasPrevious: false,
                    hasNext: false,
                    onNext: this.handleNext,
                    onPrevious: this.handlePrevious
                },
                sort : {value:'demo',direction:'ascending'},
            }
        },
        watch: {
            queryParams: {
                deep: true,
                handler() {
                    this.getData(this.queryParams);
                }
            },
        },
        methods: {
            handleSortChange(sort, direction) {
                this.sort = {value: sort, direction: direction};
                this.queryParams.sortBy = sort;
                this.queryParams.sortOrder = direction;
                this.getData(this.queryParams);
            },
            handleSearch(val) {
                this.queryParams.page = 1;
                this.queryParams.search = val;
                this.getData(this.queryParams);
            },
            handleNext() {
                this.queryParams.page++;
            },
            handlePrevious() {
                this.queryParams.page--;
            },
            handleDateChange(dates) {
                this.queryParams.start_date = moment(dates.startDate).format('MM/DD/YYYY');
                this.queryParams.end_date = moment(dates.endDate).format('MM/DD/YYYY');
            },

            //Get product data
            async getData(parameters={}) {
                try {
                    let response = await axios.get('/api/products/list', { params: parameters });
                    this.products = response.data.data;
                    this.hasPagination = this.products.length > 0 ? true : false;
                    this.pagination.hasPrevious = Boolean(response.data.prev_page_url);
                    this.pagination.hasNext = Boolean(response.data.next_page_url);
                    this.tableFooter = 'Showing '+response.data.from+' - '+response.data.to+' of '+response.data.total+' Products';
                    //console.log(response.data.data);
                } catch ({response}) {

                }
            }
        },
        created() {
            this.getData(this.queryParams);
        }
    }
</script>

<style scoped>

</style>
