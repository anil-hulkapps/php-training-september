<template>
    <PPage fullWidth title="Transactions">
        <PButtonGroup slot="primaryAction">
            <PTextContainer>
                <p> Total tax collection for date selection is: {{currency}}{{total_excise_collection}}</p>
            </PTextContainer>
            <pDatePicker id="date_range" format="MM/DD/YYYY" :dateRange="date_range"
                          opens="left" @change="handleDateChange"></pDatePicker>
        </PButtonGroup>

        <PLayout sectioned>
            <PCard :actions="[]">
                <div>
                    <PTabs :tabs="menu" :selected="selectedMenuIndex" @select="selectMenu">

                    </PTabs>
                </div>
                <PCardSection>
                    <template v-if="menu[selectedMenuIndex].id === 'orders'">
                        <PDataTable
                            :columnContentTypes='["text","text","text","numeric","numeric","numeric","numeric"]'
                            :sort="sort"
                            :hasPagination="hasPagination"
                            @input-filter-changed="handleSearch"
                            :pagination="pagination"
                            @sort-changed="handleSortChange"
                            :headings="headings"
                            :hasFilter="true"
                            :loading="isDataLoading"
                            searchPlaceholder="Search by order number, customer, state"
                            :footerContent="(!orders.length) ? 'No Orders Found' : tableFooter">

                            <template slot="filter">
                                <PPopover id="popover_1" :active="status_filter_active" @close="toggleStatusFilter" preferredAlignment="right" full-width>
                                    <PButton slot="activator" @click="toggleStatusFilter" :disclosure="status_filter_active ? 'up' : 'down'">Status</PButton>
                                    <POptionList
                                        slot="content"
                                        :options='order_statuses'
                                        :selected='queryParams.status'
                                        @change="updateStatusFilter"
                                    ></POptionList>
                                </PPopover>
                            </template>
                            <template slot="body">
                                <tr v-for="(order, index) in orders" :key="`row-${index}`" class="Polaris-DataTable__TableRow blackout-days">
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        <PUnstyledLink :url="'https://'+shopDomain+'/admin/orders/'+order.order_id" external>#{{ order.order_number }}</PUnstyledLink>
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.order_date }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.customer }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.taxable_item }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{currency}}{{ order.order_total }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{currency}}{{ order.excise_tax }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.state }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle" style="text-align: right">
                                        <PBadge :status="order.badge" :progress="order.progress">{{ order.status }}</PBadge>
                                    </td>
                                </tr>
                            </template>
                        </PDataTable>
                    </template>
                    <template v-if="menu[selectedMenuIndex].id === 'excise-errors'">
                        <PDataTable
                            :columnContentTypes='["text","text","text","numeric","numeric","numeric","numeric","numeric"]'
                            :sort="sort"
                            :hasPagination="hasPagination"
                            @input-filter-changed="handleSearch"
                            :pagination="pagination"
                            @sort-changed="handleSortChange"
                            :headings="headingsExciseErrors"
                            :hasFilter="true"
                            :loading="isDataLoading"
                            searchPlaceholder="Search by order number, customer, state"
                            :footerContent="(!orders.length) ? 'No Orders Found' : tableFooter">

                            <template slot="filter">
                                <PPopover id="popover_2" :active="status_filter_active2" @close="toggleStatusFilter2" full-width>
                                    <PButton slot="activator" @click="toggleStatusFilter2" :disclosure="status_filter_active2 ? 'up' : 'down'">Status</PButton>
                                    <POptionList
                                        slot="content"
                                        :options='order_statuses'
                                        :selected='queryParams.status'
                                        @change="updateStatusFilter2"
                                    ></POptionList>
                                </PPopover>
                            </template>
                            <template slot="body">
                                <tr v-for="(order, index) in orders" :key="`row-${index}`" class="Polaris-DataTable__TableRow blackout-days">
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        <PUnstyledLink :url="'https://'+shopDomain+'/admin/orders/'+order.order_id" external>#{{ order.order_number }}</PUnstyledLink>
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.order_date }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.customer }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.taxable_item }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{currency}}{{ order.order_total }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        <template v-if="order.failed_reason.length < 20">{{ order.failed_reason }}</template>
                                        <template v-else>{{ order.failed_reason.substring(0,20)+"..." }}</template>
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.state }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        <PBadge :status="order.badge" :progress="order.progress">{{ order.status }}</PBadge>
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle" style="text-align: right">
                                        <PButtonGroup segmented>
                                            <PButton @click="ignoreExcise(order.id)">Ignore Tax</PButton>
                                            <PButton :disabled="order.is_recalcuted" @click="reAttempt(order.order_id)">Calculate & Notify customer</PButton>
                                        </PButtonGroup>
                                    </td>
                                </tr>
                            </template>
                        </PDataTable>
                    </template>
                    <template v-if="menu[selectedMenuIndex].id === 'ignored-orders'">
                        <PDataTable
                            :columnContentTypes='["text","text","text","numeric","numeric","numeric"]'
                            :sort="sort"
                            :hasPagination="hasPagination"
                            @input-filter-changed="handleSearch"
                            :pagination="pagination"
                            @sort-changed="handleSortChange"
                            :headings="headingsIngoredOrders"
                            :hasFilter="true"
                            :loading="isDataLoading"
                            searchPlaceholder="Search by order number, customer, state"
                            :footerContent="(!orders.length) ? 'No Orders Found' : tableFooter">

                            <template slot="body">
                                <tr v-for="(order, index) in orders" :key="`row-${index}`" class="Polaris-DataTable__TableRow blackout-days">
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        <PUnstyledLink :url="'https://'+shopDomain+'/admin/orders/'+order.order_id" external>#{{ order.order_number }}</PUnstyledLink>
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.order_date }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.customer }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.taxable_item }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{currency}}{{ order.order_total }}
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        <template v-if="order.failed_reason.length < 20">{{ order.failed_reason }}</template>
                                        <template v-else>{{ order.failed_reason.substring(0,20)+"..." }}</template>
                                    </td>
                                    <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell&#45;&#45;verticalAlignMiddle Polaris-DataTable__Cell&#45;&#45">
                                        {{ order.state }}
                                    </td>
                                </tr>
                            </template>
                        </PDataTable>
                    </template>
                </PCardSection>
            </PCard>
        </PLayout>
    </PPage>
</template>

<script>
    export default {
        name: "Transactions",
        data() {
            return {
                status_filter_active: false,
                status_filter_active2: false,
                order_statuses: [
                    {label: 'Fulfilled', value: 1},
                    {label: 'Unfulfilled', value: 2},
                    {label: 'Partially Fulfilled', value: 3},
                    {label: 'Cancelled', value: 4},
                ],



                menu: [
                    {
                        id: 'orders',
                        content: 'Orders',
                        path: '/orders'
                    },
                    {
                        id: 'excise-errors',
                        content: 'Excise Errors',
                        path: '/excise-errors'
                    },
                    {
                        id: 'ignored-orders',
                        content: 'Ignored Orders',
                        path: '/ignored-orders'
                    },

                ],
                selectedMenuIndex: 0,
                queryParams: {
                    page: 1,
                    status: [],
                    search: '',
                    start_date: moment(new Date()).subtract(30,'day').format('MM/DD/YYYY'),
                    end_date: moment(new Date()).format('MM/DD/YYYY'),
                    sortBy: 'order_number',
                    sortOrder: 'descending',
                },
                orders: [],
                hasPagination: false,
                isDataLoading:false,
                tableFooter: '',
                date_range: {
                    startDate: moment(new Date()).subtract(30,'day').format('MM/DD/YYYY'),
                    endDate: moment(new Date()).format('MM/DD/YYYY')
                },
                total_excise_collection: 0,
                headings: [{
                    content: 'Order Number',
                    value: 'order_number',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Order Date',
                    value: 'order_date',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Customer',
                    value: 'customer',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Taxable Items',
                    value: 'taxable_item',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Order Total',
                    value: 'order_total',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Excise Tax',
                    value: 'excise_tax',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'State',
                    value: 'state',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Status',
                    value: 'total_excise_tax',
                    type: 'text',
                    sortable: false,
                }],
                headingsExciseErrors: [{
                    content: 'Order Number',
                    value: 'order_number',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Order Date',
                    value: 'order_date',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Customer',
                    value: 'customer',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Taxable Items',
                    value: 'taxable_item',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Order Total',
                    value: 'order_total',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Failure Reason',
                    value: 'total_excise_tax',
                    type: 'text',
                    sortable: false,
                },{
                    content: 'State',
                    value: 'state',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Status',
                    value: 'total_excise_tax',
                    type: 'text',
                    sortable: false,
                },{
                    content: 'Action',
                    value: 'total_excise_tax',
                    type: 'text',
                    sortable: false,
                }],
                headingsIngoredOrders: [{
                    content: 'Order Number',
                    value: 'order_number',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Order Date',
                    value: 'order_date',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Customer',
                    value: 'customer',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Taxable Items',
                    value: 'taxable_item',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Order Total',
                    value: 'order_total',
                    type: 'text',
                    sortable: true,
                },{
                    content: 'Failure Reason',
                    value: 'total_excise_tax',
                    type: 'text',
                    sortable: false,
                },{
                    content: 'State',
                    value: 'state',
                    type: 'text',
                    sortable: true,
                }],
                pagination: {
                    hasPrevious: false,
                    hasNext: false,
                    onNext: this.handleNext,
                    onPrevious: this.handlePrevious
                },
                sort : {value:'demo',direction:'ascending'},
                currency: '',
                shopDomain: ''
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
            updateStatusFilter(val) {
                this.queryParams.status = val;
            },
            updateStatusFilter2(val) {
                this.queryParams.status = val;
            },
            toggleStatusFilter() {
                this.status_filter_active = !this.status_filter_active;
            },
            toggleStatusFilter2() {
                this.status_filter_active2 = !this.status_filter_active2;
            },
            async reAttempt(order_id) {
                let parameters = {
                    order_id: order_id,
                };
                try {
                    await axios.post('/api/transactions/reattempt-excise', parameters).then((res) => {
                        this.getData(this.queryParams);
                        this.$root.$toast(res.data.data);
                    }).catch(error => {
                        //this.$root.$toast(error.response.data.data, true);
                    });
                } catch ({response}) {

                }
            },
            async ignoreExcise(id) {
                let parameters = {
                    id: id,
                };
                try {
                    await axios.post('/api/transactions/ignore-excise', parameters).then((res) => {
                        this.getData(this.queryParams);
                        this.$root.$toast(res.data.data);
                    }).catch(error => {
                        this.$root.$toast(error.response.data.data, true);
                    });
                } catch ({response}) {

                }
            },
            selectMenu(menuIndex) {
                this.selectedMenuIndex = menuIndex;
                this.queryParams.search = '';
                this.queryParams.status = [];
                this.getData(this.queryParams);
            },
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

            //Get product data
            async getData(parameters={}) {
                try {
                    let url = this.selectedTab();
                    let response = await axios.get(url, { params: parameters });
                    let total_orders = response.data.total_orders ?? 0;
                    let total_excise_errors = response.data.total_excise_errors ?? 0;
                    let total_ignored_orders = response.data.total_ignored_orders ?? 0;
                    this.total_excise_collection = response.data.total_excise_collection ?? 0;
                    response = response.data.data;
                    this.orders = response.data;
                    if (this.selectedMenuIndex == 0) {
                        this.menu[this.selectedMenuIndex].content = "Orders " + '(' + response.total + ')';
                        this.menu[1].content = "Excise Errors " + '(' + total_excise_errors + ')';
                        this.menu[2].content = "Ignored Orders " + '(' + total_ignored_orders + ')';
                    } else if (this.selectedMenuIndex == 1) {
                        this.menu[this.selectedMenuIndex].content = "Excise Errors " + '(' + response.total + ')';
                        this.menu[0].content = "Orders " + '(' + total_orders + ')';
                        this.menu[2].content = "Ignored Orders " + '(' + total_ignored_orders + ')';
                    } else if (this.selectedMenuIndex == 2) {
                        this.menu[this.selectedMenuIndex].content = "Ignored Orders " + '(' + response.total + ')';
                        this.menu[0].content = "Orders " + '(' + total_orders + ')';
                        this.menu[1].content = "Excise Errors " + '(' + total_excise_errors + ')';
                    }
                    this.hasPagination = this.orders.length > 0 ? true : false;
                    this.pagination.hasPrevious = Boolean(response.prev_page_url);
                    this.pagination.hasNext = Boolean(response.next_page_url);
                    this.tableFooter = 'Showing '+response.from+' - '+response.to+' of '+response.total+' Orders';
                } catch ({response}) {

                }
            },
            selectedTab() {
                if (this.selectedMenuIndex == 0) {
                    return '/api/transactions/orders';
                } else if (this.selectedMenuIndex == 1) {
                    return '/api/transactions/excise-errors';
                } else if (this.selectedMenuIndex == 2) {
                    return '/api/transactions/ignored-orders';
                }
            },
            async getShopData() {
                try {
                    let response = await axios.get('/api/settings/store-detail');
                    this.currency = response.data.currency_format;
                    this.shopDomain = response.data.name;
                } catch ({response}) {
                }
            },
            handleUpdate() {
                alert('Handle update for id ');
            },
            handleDelete() {
                alert('Handle delete for id ');
            },
            handleDateChange(dates) {
                this.queryParams.start_date = moment(dates.startDate).format('MM/DD/YYYY');
                this.queryParams.end_date = moment(dates.endDate).format('MM/DD/YYYY');
            }
        },
        created() {
            this.getData(this.queryParams);
            this.getShopData();
        }
    }
</script>

<style scoped>
    .tooltipcustom {
        position: relative;
        display: inline-block;
    }

    .tooltipcustom .tooltiptext {
        visibility: hidden;
        width: 120px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;

        /* Position the tooltip */
        position: absolute;
        z-index: 1;
    }

    .tooltipcustom:hover .tooltiptext {
        visibility: visible;
    }
</style>
