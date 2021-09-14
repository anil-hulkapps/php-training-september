<template>
    <PPage separator :primaryAction="primaryAction" title="Transaction">
            <PCard>
                <template>
                    <div class="Polaris-Tabs__Wrapper Polaris-Tabs__Navigation">
                        <ul role="tablist" class="Polaris-Tabs">
                            <li class="Polaris-Tabs__TabContainer" v-for="(item,index) in items">
                                <router-link :tabindex="index" active-class="Polaris-Tabs__Tab--selected" class="Polaris-Tabs__Tab" :to="item.to" :aria-label="item.title">
                                    <span class="Polaris-Tabs__Title">{{item.title}}</span>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </template>

                <PCardSection>
                    <PDataTable
                        hasPagination
                        :headings="ordersHeading"
                        :tableOptions="options"
                        :hasPagination="hasPagination"
                        @input-filter-changed="handleSearch"
                        @sort-changed="handleSortChange"
                        :footerContent="(!orders.length) ? 'No discount codes found' : ''"
                        :pagination="{
                            hasPrevious: Boolean(options.prev_page_url),
                            hasNext: Boolean(options.next_page_url),
                            onNext: onNext,
                            onPrevious: onPrevious
                        }"
                    >
                        <template slot="filter">
                            <PPopover :active="active" full-width @close="toggleRatingFilter" id="popover_1" zIndex="999">
                                <PButton slot="activator" @click="toggleRatingFilter" :disclosure="active ? 'up' : 'down'">State</PButton>
                                <POptionList
                                    slot="content"
                                    allowMultiple
                                    :selected="selected"
                                    :options="[
                                      {label: 'Rating 1 with a long text', value: '1'},
                                      {label: 'Rating 2', value: '2'},
                                      {label: 'Rating 3', value: '3'},
                                      {label: 'Rating 4', value: '4'},
                                    ]"
                                    @change="updateRatingFilter"
                                ></POptionList>
                            </PPopover>
                            <PPopover :active="active2" @close="toggleRatingFilter" id="popover_2">
                                <PButton slot="activator" @click="toggleRatingFilter2" :disclosure="active2 ? 'up' : 'down'">Status</PButton>
                                <POptionList
                                    slot="content"
                                    allowMultiple
                                    :selected="status"
                                    :options="[
                                      {label: 'Active', value: 'Active'},
                                      {label: 'Pending', value: 'Pending'},
                                      {label: 'Deleted', value: 'Deleted'},
                                    ]"
                                    @change="updateStatusFilter"
                                ></POptionList>
                            </PPopover>
                        </template>
                        <template slot="body" v-for="order in orders">
                            <tr class="Polaris-DataTable__TableRow">
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--firstColumn">
                                    #12546
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle">
                                    01 Jan, 2020
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle">
                                    Rene M.
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                    1
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                    $315
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                    10
                                </td>
                                <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                    <PBadge status='success'>Delivered </PBadge>
                                </td>
                            </tr>
                        </template>
                    </PDataTable>
                </PCardSection>
            </PCard>
        <PCard>

            <PCardSection>
                <PDataTable
                    hasPagination
                    :headings="exciseErrorHeading"
                    :tableOptions="options"
                    @input-filter-changed="handleSearch"
                    :footerContent="(!orders.length) ? 'No orders found' : ''"
                    :pagination="{
                            hasPrevious: Boolean(options.prev_page_url),
                            hasNext: Boolean(options.next_page_url),
                            onNext: onNext,
                            onPrevious: onPrevious
                        }"
                >
                    <template slot="filter">
                        <PPopover :active="active" full-width @close="toggleRatingFilter" id="popover_1" zIndex="999">
                            <PButton slot="activator" @click="toggleRatingFilter" :disclosure="active ? 'up' : 'down'">State</PButton>
                            <POptionList
                                slot="content"
                                allowMultiple
                                :selected="selected"
                                :options="[
                                      {label: 'Rating 1 with a long text', value: '1'},
                                      {label: 'Rating 2', value: '2'},
                                      {label: 'Rating 3', value: '3'},
                                      {label: 'Rating 4', value: '4'},
                                    ]"
                                @change="updateRatingFilter"
                            ></POptionList>
                        </PPopover>
                        <PPopover :active="active2" @close="toggleRatingFilter" id="popover_2">
                            <PButton slot="activator" @click="toggleRatingFilter2" :disclosure="active2 ? 'up' : 'down'">Status</PButton>
                            <POptionList
                                slot="content"
                                allowMultiple
                                :selected="status"
                                :options="[
                                      {label: 'Active', value: 'Active'},
                                      {label: 'Pending', value: 'Pending'},
                                      {label: 'Deleted', value: 'Deleted'},
                                    ]"
                                @change="updateStatusFilter"
                            ></POptionList>
                        </PPopover>
                    </template>
                    <template slot="body" v-for="order in orders">
                        <tr class="Polaris-DataTable__TableRow">
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--firstColumn">
                                #12546
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle">
                                01 Jan, 2020
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle">
                                Rene M.
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                1
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                $315
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                $10
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                Avalara API response timeout
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                <PBadge status='warning'>Pending</PBadge>
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                <PButton class="Polaris-Button--destructive Polaris-Button--iconOnly" :icon="deleteIcon"
                                  ></PButton>
                            </td>
                        </tr>
                    </template>
                </PDataTable>
            </PCardSection>
        </PCard>
        <PCard>

            <PCardSection>
                <PDataTable
                    hasPagination
                    :headings="ignoredOrderHeading"
                    :tableOptions="options"
                    @input-filter-changed="handleSearch"
                    :footerContent="(!orders.length) ? 'No orders found' : ''"
                    :pagination="{
                            hasPrevious: Boolean(options.prev_page_url),
                            hasNext: Boolean(options.next_page_url),
                            onNext: onNext,
                            onPrevious: onPrevious
                        }"
                >
                    <template slot="filter">
                        <PPopover :active="active" full-width @close="toggleRatingFilter" id="popover_1" zIndex="999">
                            <PButton slot="activator" @click="toggleRatingFilter" :disclosure="active ? 'up' : 'down'">State</PButton>
                            <POptionList
                                slot="content"
                                allowMultiple
                                :selected="selected"
                                :options="[
                                      {label: 'Rating 1 with a long text', value: '1'},
                                      {label: 'Rating 2', value: '2'},
                                      {label: 'Rating 3', value: '3'},
                                      {label: 'Rating 4', value: '4'},
                                    ]"
                                @change="updateRatingFilter"
                            ></POptionList>
                        </PPopover>
                        <PPopover :active="active2" @close="toggleRatingFilter" id="popover_2">
                            <PButton slot="activator" @click="toggleRatingFilter2" :disclosure="active2 ? 'up' : 'down'">Status</PButton>
                            <POptionList
                                slot="content"
                                allowMultiple
                                :selected="status"
                                :options="[
                                      {label: 'Active', value: 'Active'},
                                      {label: 'Pending', value: 'Pending'},
                                      {label: 'Deleted', value: 'Deleted'},
                                    ]"
                                @change="updateStatusFilter"
                            ></POptionList>
                        </PPopover>
                    </template>
                    <template slot="body" v-for="order in orders">
                        <tr class="Polaris-DataTable__TableRow">
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--firstColumn">
                                #12546
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle">
                                01 Jan, 2020
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle">
                                Rene M.
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                1
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                $315
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                $10
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                Avalara API response timeout
                            </td>
                            <td class="Polaris-DataTable__Cell Polaris-DataTable__Cell--verticalAlignMiddle Polaris-DataTable__Cell--numeric">
                                <PBadge status='warning'>Pending</PBadge>
                            </td>
                        </tr>
                    </template>
                </PDataTable>
            </PCardSection>
        </PCard>
    </PPage>
</template>

<script>
    export default {
        name: "Settings",
        data() {
            return {
                hasPagination: false,
                ordersHeading: [
                    {content: 'Order Number', value: 'order_number', type: 'text', sortable: false},
                    {content: 'Order Date', value: 'order_date', type: 'text', sortable: false},
                    {content: 'Customer', value: 'customer', type: 'text', sortable: false},
                    {content: 'Taxable Items', value: 'taxable_items', type: 'numeric', sortable: false},
                    {content: 'Order Total', value: 'order_total', type: 'numeric', sortable: false},
                    {content: 'Excise Tax', value: 'excise_tax', type: 'numeric', sortable: false},
                    {content: 'Status', value: 'status', type: 'numeric', sortable: false},
                ],
                exciseErrorHeading: [
                    {content: 'Order Number', value: 'order_number', type: 'text', sortable: false},
                    {content: 'Order Date', value: 'order_date', type: 'text', sortable: false},
                    {content: 'Customer', value: 'customer', type: 'text', sortable: false},
                    {content: 'Taxable Items', value: 'taxable_items', type: 'numeric', sortable: false},
                    {content: 'Order Total', value: 'order_total', type: 'numeric', sortable: false},
                    {content: 'Excise Tax', value: 'excise_tax', type: 'numeric', sortable: false},
                    {content: 'Failure reason', value: 'failure_reason', type: 'text', sortable: false},
                    {content: 'Status', value: 'status', type: 'numeric', sortable: false},
                    {content: 'Actions', value: 'actions', type: 'numeric', sortable: false},
                ],
                ignoredOrderHeading: [
                    {content: 'Order Number', value: 'order_number', type: 'text', sortable: false},
                    {content: 'Order Date', value: 'order_date', type: 'text', sortable: false},
                    {content: 'Customer', value: 'customer', type: 'text', sortable: false},
                    {content: 'Taxable Items', value: 'taxable_items', type: 'numeric', sortable: false},
                    {content: 'Order Total', value: 'order_total', type: 'numeric', sortable: false},
                    {content: 'Excise Tax', value: 'excise_tax', type: 'numeric', sortable: false},
                    {content: 'Failure reason', value: 'failure_reason', type: 'text', sortable: false},
                    {content: 'Status', value: 'status', type: 'numeric', sortable: false},
                ],
                orders: [1,2,3,4,5,6],
                options: {
                    limit: 10,
                    page: null,
                    sort_by: 'id',
                    sort_order: 'desc',
                },
                items: [
                    {title: 'Orders (500)', to: '/orders'},
                    {title: 'Excise Errors (20)', to: '/excise-errors'},
                    {title: 'Ignored Orders (4)', to: 'ignored-orders'},
                ]
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
            handleNext() {
                this.queryParams.page++;
            },
            handlePrevious() {
                this.queryParams.page--;
            },
            pushRoute(path) {
                this.$router.push(path);
            },
            handleSearch(val) {
                this.queryParams.page = 1;
                this.queryParams.search = val;
                this.getData(this.queryParams);
            },
            handleSortChange(sort, direction) {
                this.sort = {value: sort, direction: direction};
                this.queryParams.sortBy = sort;
                this.queryParams.sortOrder = direction;
                this.getData(this.queryParams);
            },
            async getData(parameters={}) {
                try {

                } catch ({response}) {

                }
            },
        },
        created() {
            this.getData();
        }
    }
</script>

<style scoped>

</style>
