<?php

namespace App\Jobs;

use App\Models\ExciseByProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting\AvalaraCredential;
use App\Models\Setting\ProductForExcise;
use App\Models\Setting\ProductIdentifierForExcise;
use App\Models\Setting\StaticSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Helpers;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrdersUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        Log::info("order update job");
        $this->shopDomain = $shopDomain;
        /*$shop = User::where('name', $this->shopDomain)->first();
        $orderRes = $shop->api()->rest('GET', '/admin/orders/'.$data.'.json');
        if (isset($orderRes['body']['order'])) {
            $data = $orderRes['body']['order'];
        }*/
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $shop = User::where(['name' => $this->shopDomain->toNative()])->first();

        Transaction::where([['order_id', $data->id], ['shop_id', $shop->id]])->update(['status' => Helpers::getOrderFulfillmentStatus($data->fulfillment_status)]);
        /*$data = $this->data;

        $shop = User::where(['name' => $this->shopDomain->toNative()])->first();
        $staticSettings = StaticSetting::where('shop_id', $shop->id)->get();

        // Static Settings
        $hasTitleTransferCode = $staticSettings->where('field', 'title_transfer_code')->first();
        $titleTransferCode = $hasTitleTransferCode ? $hasTitleTransferCode->value : 'DEST';
        $hasTransactionType = $staticSettings->where('field', 'transaction_type')->first();
        $transactionType = $hasTransactionType ? $hasTransactionType->value : 'WHOLESALE';
        $hasTransportationModeCode = $staticSettings->where('field', 'transportation_mode_code')->first();
        $transportationModeCode = $hasTransportationModeCode ? $hasTransportationModeCode->value : 'J';
        $hasSeller = $staticSettings->where('field', 'seller')->first();
        $seller = $hasSeller ? $hasSeller->value : '';
        $hasBuyer = $staticSettings->where('field', 'buyer')->first();
        $buyer = $hasBuyer ? $hasBuyer->value : '';
        $hasUnitOfMeasure = $staticSettings->where('field', 'unit_of_measure')->first();
        $unitOfMeasure = $hasUnitOfMeasure ? $hasUnitOfMeasure->value : 'EA';
        $hasCurrency = $staticSettings->where('field', 'currency')->first();
        $currency = $hasCurrency ? $hasCurrency->value : $data->currency;
        $hasOrigin = $staticSettings->where('field', 'origin')->first();
        $origin = $hasOrigin ? $hasOrigin->value : '';

        // Order Custom Fields
        $orderCustomString1 = $staticSettings->where('field', 'order_custom_string1')->first();
        $orderCustomString2 = $staticSettings->where('field', 'order_custom_string2')->first();
        $orderCustomString3 = $staticSettings->where('field', 'order_custom_string3')->first();
        $orderCustomNumeric1 = $staticSettings->where('field', 'order_custom_numeric1')->first();
        $orderCustomNumeric2 = $staticSettings->where('field', 'order_custom_numeric2')->first();
        $orderCustomNumeric3 = $staticSettings->where('field', 'order_custom_numeric3')->first();

        // Line Items Custom Fields
        $itemCustomString1 = $staticSettings->where('field', 'lineitem_custom_string1')->first();
        $itemCustomString2 = $staticSettings->where('field', 'lineitem_custom_string2')->first();
        $itemCustomString3 = $staticSettings->where('field', 'lineitem_custom_string3')->first();
        $itemCustomNumeric1 = $staticSettings->where('field', 'lineitem_custom_numeric1')->first();
        $itemCustomNumeric2 = $staticSettings->where('field', 'lineitem_custom_numeric2')->first();
        $itemCustomNumeric3 = $staticSettings->where('field', 'lineitem_custom_numeric3')->first();

        //Avalara Credential
        $avalaraCredential = AvalaraCredential::where('shop_id', $shop->id)->first();
        $companyId = $avalaraCredential->company_id;
        $apiUsername = $avalaraCredential->username;
        $apiUserPassword = $avalaraCredential->password;

        //setting for excise calculation
        $productForExcise = ProductForExcise::where('shop_id', $shop->id)->first();
        $productIdentifierForExcise = ProductIdentifierForExcise::where('shop_id', $shop->id)->first();

        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $companyId
        ];

        if (!empty($data->note_attributes)) {
            foreach ($data->note_attributes as $noteAttribute) {

                if ($noteAttribute->name === 'transaction_id') {
                    $transactionCode = $noteAttribute->value;
                    $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
                    $invoiceDate = Carbon::parse($data->created_at)->format('Y-m-d H:i:s');

                    $transactionLines = $variantIds = $productIds = $past_fulfilled_items = [];
                    $itemCounter = 0;

                    if (!empty($data->line_items)) {
                        foreach ($data->line_items as $line_item) {
                            if (!empty($line_item->sku)) {
                                $productTags = $shop->api()->rest('GET', '/admin/products/' . $line_item->product_id . '.json');
                                if (isset($productTags['body']['product']) && !empty($productTags['body']['product'])) {
                                    $productTags = $productTags['body']['product']['tags'];
                                }
                                $item['ProductCode'] = $item['itemSKU'] = Str::substr($line_item->sku, 0, 24);
                                $item['tags'] = $productTags;
                                if (!filterRequest($item, $productForExcise, $productIdentifierForExcise)) {
                                    continue;
                                }
                                $variantIds[] = $line_item->variant_id;
                                $productIds[] = $line_item->product_id;
                                $transactionLines[] = [
                                    "TransactionLineMeasures" => null,
                                    "OriginSpecialJurisdictions" => [],
                                    "DestinationSpecialJurisdictions" => [],
                                    "SaleSpecialJurisdictions" => [],
                                    "InvoiceLine" => ++$itemCounter,
                                    //"MovementStartDate" => $invoiceDate,
                                    //"MovementEndDate" => $invoiceDate,
                                    "ProductCode" => $line_item->sku ? Str::substr($line_item->sku, 0, 24) : '',
                                    //"BlendToProductCode" => null,
                                    "UnitPrice" => $line_item->price,
                                    "NetUnits" => $line_item->quantity,
                                    "GrossUnits" => $line_item->quantity,
                                    "BilledUnits" => $line_item->quantity,
                                    //"LineAmount" => null,
                                    //"BillOfLadingNumber" => "",
                                    "BillOfLadingDate" => $invoiceDate,
                                    //"OriginCountryCode" => null,
                                    //"OriginJurisdiction" => null,
                                    //"OriginCounty" => null,
                                    //"OriginCity" => null,
                                    //"OriginPostalCode" => null,
                                    //"OriginType" => null,
                                    "Origin" => $origin,
                                    //"OriginOutCityLimitInd" => null,
                                    //"OriginSpecialJurisdictionInd" => null,
                                    //"OriginExciseWarehouse" => null,
                                    "OriginAddress1" => isset($data->shipping_address) ? $data->shipping_address->address1 : '',
                                    "OriginAddress2" => null,//isset($orderData->shipping_address) ? $orderData->shipping_address->address2 : '',
                                    //"OriginAirportCode" => null,
                                    "DestinationCountryCode" => isset($data->shipping_address) ? $data->shipping_address->country_code : '',
                                    "DestinationJurisdiction" => isset($data->shipping_address) ? $data->shipping_address->province_code : '',
                                    "DestinationCounty" => "",
                                    "DestinationCity" => isset($data->shipping_address) ? $data->shipping_address->city : '',
                                    "DestinationPostalCode" => isset($data->shipping_address) ? $data->shipping_address->zip : '',
                                    //"DestinationType" => null,
                                    //"Destination" => "",
                                    //"DestinationOutCityLimitInd" => null,
                                    //"DestinationSpecialJurisdictionInd" => null,
                                    //"DestinationExciseWarehouse" => null,
                                    "DestinationAddress1" => isset($data->shipping_address) ? $data->shipping_address->address1 : '',
                                    "DestinationAddress2" => isset($data->shipping_address) ? $data->shipping_address->address2 : '',
                                    //"DestinationAirportCode" => null,
                                    //"SaleCountryCode" => "",
                                    //"SaleJurisdiction" => "",
                                    //"SaleCounty" => "",
                                    //"SaleCity" => "",
                                    //"SalePostalCode" => "",
                                    //"SaleType" => "",
                                    //"SaleLocation" => null,
                                    //"SaleOutCityLimitInd" => null,
                                    //"SaleSpecialJurisdictionInd" => null,
                                    //"SaleExciseWarehouse" => "",
                                    //"SaleAddress1" => "",
                                    //"SaleAddress2" => "",
                                    //"SaleAirportCode" => "",
                                    //"CounterCountryCode" => "",
                                    //"CounterJurisdiction" => "",
                                    //"CounterCounty" => "",
                                    //"CounterCity" => "",
                                    //"CounterPostalCode" => "",
                                    //"CounterType" => "",
                                    //"CounterParty" => null,
                                    //"CounterOutCityLimitInd" => null,
                                    //"CounterSpecialJurisdictionInd" => null,
                                    //"CounterExciseWarehouse" => "",
                                    //"CounterFiscalRepInd" => null,
                                    //"CounterAddress1" => "",
                                    //"CounterAddress2" => "",
                                    //"CounterAirportCode" => "",
                                    //"UserData" => "",
                                    //"AlternativeFuelContent" => null,
                                    //"BlendToAltFuelContent" => null,
                                    //"BlendToInd" => null,
                                    "Currency" => $currency,
                                    "UnitOfMeasure" => $unitOfMeasure,
                                    //"FreightUnitPrice" => null,
                                    //"FreightType" => "",
                                    //"FreightLineAmount" => null,
                                    "CustomString1" => $itemCustomString1 ? getCustomString($itemCustomString1->value, $data) : null,
                                    "CustomString2" => $itemCustomString2 ? getCustomString($itemCustomString2->value, $data) : null,
                                    "CustomString3" => $itemCustomString3 ? getCustomString($itemCustomString3->value, $data) : null,
                                    "CustomNumeric1" => $itemCustomNumeric1 ? getCustomNumeric($itemCustomNumeric1->value, $data) : null,
                                    "CustomNumeric2" => $itemCustomNumeric2 ? getCustomNumeric($itemCustomNumeric2->value, $data) : null,
                                    "CustomNumeric3" => $itemCustomNumeric3 ? getCustomNumeric($itemCustomNumeric3->value, $data) : null,
                                    //"NthTimeSale" => null,
                                    "AlternateUnitPrice" => getVariant($this->shopDomain->toNative(), $line_item->variant_id),
                                    //"AlternateLineAmount" => null,
                                    //"TaxIncluded" => null
                                ];
                            }
                        }
                    }


                    $requestDataAdjust = [
                        'TransactionLines' => $transactionLines,
                        'TransactionExchangeRates' => [],
                        'EffectiveDate' => $invoiceDate,
                        'InvoiceDate' => $invoiceDate,
                        'InvoiceNumber' => $data->order_number,
                        //'FuelUseCode' => '',
                        'TitleTransferCode' => $titleTransferCode,
                        'TransactionType' => $transactionType,
                        'TransportationModeCode' => $transportationModeCode,
                        'Seller' => $seller,
                        'Buyer' => $buyer,
                        //'PreviousSeller' => null,
                        //'NextBuyer' => null,
                        //'Middleman' => null,
                        //'CustomsStatus' => '',
                        //'FormAPresentedInd' => null,
                        //'SimplifiedProcedureInd' => null,
                        //'Incoterms' => '',
                        //'PerspectiveBusinessType' => '',
                        //'ChainLeg' => null,
                        //'OrderType' => '',
                        //'TotalDyedUnits' => null,
                        //'TotalReportingTaxes' => 0,
                        //'ReportingCurrency' => '',
                        //'UserData' => '',
                        //'UserTranId' => 'string',
                        //'SourceSystem' => '',
                        'CustomString1' => $orderCustomString1 ? getCustomString($orderCustomString1->value, $data) : null,
                        'CustomString2' => $orderCustomString2 ? getCustomString($orderCustomString2->value, $data) : null,
                        'CustomString3' => $orderCustomString3 ? getCustomString($orderCustomString3->value, $data) : null,
                        'CustomNumeric1' => $orderCustomNumeric1 ? getCustomNumeric($orderCustomNumeric1->value, $data) : null,
                        'CustomNumeric2' => $orderCustomNumeric2 ? getCustomNumeric($orderCustomNumeric2->value, $data) : null,
                        'CustomNumeric3' => $orderCustomNumeric3 ? getCustomNumeric($orderCustomNumeric3->value, $data) : null,
                        //'DebugInd' => null,
                        //'CalculationMethod' => null,
                    ];
                    if (!empty($transactionLines)) {
                        $response = $client->post(env('AVALARA_API_ENDPOINT') . '/AvaTaxExcise/transactions/create', [
                            'auth' => [
                                $apiUsername, $apiUserPassword
                            ],
                            'headers' => $headers,
                            'json' => $requestDataAdjust
                        ]);

                        $products_chunk = array_chunk($productIds, 250);
                        for ($i = 0; $i < count($products_chunk); $i++) {
                            $ids = $products_chunk[$i];
                            $ids = implode(",", $ids);
                            $param = ['limit' => 250, 'ids' => $ids];
                            $data250 = $shop->api()->rest('GET', '/admin/products.json', $param);
                            if (isset($data250['body']['products'])) {
                                foreach ($data250['body']['products'] as $key => $product) {
                                    $tags = explode(",", $product['tags']);
                                    $tags = array_map('trim', $tags);
                                    Product::updateOrCreate([
                                        'shop_id' => $shop->id,
                                        'shopify_product_id' => $product['id'],
                                    ], [
                                        'shop_id' => $shop->id,
                                        'shopify_product_id' => $product['id'],
                                        'title' => $product['title'],
                                        'handle' => $product['handle'],
                                        'vendor' => $product['vendor'],
                                        'tags' => $tags,
                                        'image_url' => !empty($product['image']) ? $product['image']['src'] : null,
                                    ]);

                                    foreach ($product['variants'] as $variant) {
                                        ProductVariant::updateOrCreate([
                                            'shop_id' => $shop->id,
                                            'variant_id' => $variant['id'],
                                        ], [
                                            'shop_id' => $shop->id,
                                            'product_id' => $product['id'],
                                            'variant_id' => $variant['id'],
                                            'option_1_name' => isset($product['options'][0]) ? $product['options'][0]['name'] : null,
                                            'option_1_value' => $variant['option1'],
                                            'option_2_name' => isset($product['options'][1]) ? $product['options'][1]['name'] : null,
                                            'option_2_value' => $variant['option2'],
                                            'option_3_name' => isset($product['options'][2]) ? $product['options'][2]['name'] : null,
                                            'option_3_value' => $variant['option3'],
                                            'sku' => $variant['sku'],
                                            'barcode' => $variant['barcode'],
                                            'price' => $variant['price'],
                                            'compare_at_price' => $variant['compare_at_price'],
                                            'quantity' => $variant['inventory_quantity'],
                                        ]);
                                    }
                                }
                            }
                        }

                        $resData = json_decode($response->getBody());
                        DB::table('avalara_transaction_log')->insert([
                            "status" => $response->getStatusCode(),
                            "ip" => "0.0.0.0",
                            "shop_id" => $shop->id,
                            "total_requested_products" => count($transactionLines),
                            "request_data" => json_encode($requestDataAdjust),
                            "filtered_request_data" => json_encode($requestDataAdjust),
                            "response" => json_encode($resData),
                            "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                            "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                        ]);

                        $transactionObj = new Transaction();
                        $transactionObj->shop_id = $shop->id;
                        $transactionObj->order_id = $data->id;
                        $transactionObj->order_number = $data->order_number;
                        $transactionObj->customer = isset($data->shipping_address) ? $data->shipping_address->name : '';
                        $transactionObj->taxable_item = count($transactionLines);
                        $transactionObj->order_total = $data->total_price;
                        $transactionObj->excise_tax = $resData->Status == 'Success' ? $resData->TotalTaxAmount : 0;
                        $transactionObj->status = getOrderFulfillmentStatus($data->fulfillment_status);
                        $transactionObj->order_date = $data->created_at;
                        $transactionObj->failed_reason = null;
                        //$transactionObj->is_ignore = count($transactionLines);
                        $transactionObj->save();

                        if ($resData->Status == 'Success') {
                            foreach ($resData->TransactionTaxes as $key => $transactionTax) {
                                $exciseByProduct = ExciseByProduct::where('shop_id', $shop->id)
                                    ->where('product_id', $productIds[$key])
                                    ->where('date', Carbon::parse($data->created_at)->format('Y-m-d'))->first();

                                if ($exciseByProduct) {
                                    $exciseByProduct->excise_tax += $transactionTax->TaxAmount;
                                    $exciseByProduct->save();
                                } else {
                                    ExciseByProduct::create([
                                        'shop_id' => $shop->id,
                                        'product_id' => $productIds[$key],
                                        'excise_tax' => $transactionTax->TaxAmount,
                                        'date' => Carbon::parse($data->created_at)->format('Y-m-d')
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }*/

        /*$data = $this->data;

        $shop = User::where('name', $this->shopDomain)->first();
        $staticSettings = StaticSetting::where('shop_id', $shop->id)->get();

        // Static Settings
        $hasTitleTransferCode = $staticSettings->where('field', 'title_transfer_code')->first();
        $titleTransferCode = $hasTitleTransferCode ? $hasTitleTransferCode->value : 'DEST';
        $hasTransactionType = $staticSettings->where('field', 'transaction_type')->first();
        $transactionType = $hasTransactionType ? $hasTransactionType->value : 'WHOLESALE';
        $hasTransportationModeCode = $staticSettings->where('field', 'transportation_mode_code')->first();
        $transportationModeCode = $hasTransportationModeCode ? $hasTransportationModeCode->value : 'J';
        $hasSeller = $staticSettings->where('field', 'seller')->first();
        $seller = $hasSeller ? $hasSeller->value : '';
        $hasBuyer = $staticSettings->where('field' , 'buyer')->first();
        $buyer = $hasBuyer ? $hasBuyer->value : '';
        $hasUnitOfMeasure = $staticSettings->where('field', 'unit_of_measure')->first();
        $unitOfMeasure = $hasUnitOfMeasure ? $hasUnitOfMeasure->value : 'EA';
        $hasCurrency = $staticSettings->where('field', 'currency')->first();
        $currency = $hasCurrency ? $hasCurrency->value : $data['currency'];
        $hasOrigin = $staticSettings->where('field', 'origin')->first();
        $origin = $hasOrigin ? $hasOrigin->value : '';

        // Order Custom Fields
        $orderCustomString1 = $staticSettings->where('field', 'order_custom_string1')->first();
        $orderCustomString2 = $staticSettings->where('field', 'order_custom_string2')->first();
        $orderCustomString3 = $staticSettings->where('field', 'order_custom_string3')->first();
        $orderCustomNumeric1 = $staticSettings->where('field', 'order_custom_numeric1')->first();
        $orderCustomNumeric2 = $staticSettings->where('field', 'order_custom_numeric2')->first();
        $orderCustomNumeric3 = $staticSettings->where('field', 'order_custom_numeric3')->first();

        // Line Items Custom Fields
        $itemCustomString1 = $staticSettings->where('field', 'lineitem_custom_string1')->first();
        $itemCustomString2 = $staticSettings->where('field', 'lineitem_custom_string2')->first();
        $itemCustomString3 = $staticSettings->where('field', 'lineitem_custom_string3')->first();
        $itemCustomNumeric1 = $staticSettings->where('field', 'lineitem_custom_numeric1')->first();
        $itemCustomNumeric2 = $staticSettings->where('field', 'lineitem_custom_numeric2')->first();
        $itemCustomNumeric3 = $staticSettings->where('field', 'lineitem_custom_numeric3')->first();

        //Avalara Credential
        $avalaraCredential = AvalaraCredential::where('shop_id', $shop->id)->first();
        $companyId = $avalaraCredential->company_id;
        $apiUsername = $avalaraCredential->username;
        $apiUserPassword = $avalaraCredential->password;

        //setting for excise calculation
        $productForExcise = ProductForExcise::where('shop_id', $shop->id)->first();
        $productIdentifierForExcise = ProductIdentifierForExcise::where('shop_id', $shop->id)->first();

        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $companyId
        ];

        //if (!empty($data->note_attributes)) {
        //foreach ($data->note_attributes as $noteAttribute) {

        //if ($noteAttribute->name === 'transaction_id') {
        //$transactionCode = $noteAttribute->value;
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $invoiceDate = Carbon::parse($data['created_at'])->format('Y-m-d H:i:s');

        $transactionLines = $variantIds = $productIds = $past_fulfilled_items = [];
        $itemCounter = 0;

        if (!empty($data['line_items'])) {
            foreach ($data['line_items'] as $line_item) {
                if (!empty($line_item['sku'])) {
                    $productTags = $shop->api()->rest('GET', '/admin/products/'.$line_item['product_id'].'.json');
                    if(isset($productTags['body']['product']) && !empty($productTags['body']['product'])) {
                        $productTags = $productTags['body']['product']['tags'];
                    }
                    $item['ProductCode'] = $item['itemSKU'] = Str::substr($line_item['sku'], 0, 24);
                    $item['tags'] = $productTags;
                    if (!filterRequest($item, $productForExcise, $productIdentifierForExcise)) {
                        continue;
                    }
                    $variantIds[] = $line_item['variant_id'];
                    $productIds[] = $line_item['product_id'];
                    $transactionLines[] = [
                        "TransactionLineMeasures" => null,
                        "OriginSpecialJurisdictions" => [],
                        "DestinationSpecialJurisdictions" => [],
                        "SaleSpecialJurisdictions" => [],
                        "InvoiceLine" => ++$itemCounter,
                        //"MovementStartDate" => $invoiceDate,
                        //"MovementEndDate" => $invoiceDate,
                        "ProductCode" => $line_item['sku'] ? Str::substr($line_item['sku'], 0, 24) : '',
                        //"BlendToProductCode" => null,
                        "UnitPrice" => $line_item['price'],
                        "NetUnits" => $line_item['quantity'],
                        "GrossUnits" => $line_item['quantity'],
                        "BilledUnits" => $line_item['quantity'],
                        //"LineAmount" => null,
                        //"BillOfLadingNumber" => "",
                        "BillOfLadingDate" => $invoiceDate,
                        //"OriginCountryCode" => null,
                        //"OriginJurisdiction" => null,
                        //"OriginCounty" => null,
                        //"OriginCity" => null,
                        //"OriginPostalCode" => null,
                        //"OriginType" => null,
                        "Origin" => $origin,
                        //"OriginOutCityLimitInd" => null,
                        //"OriginSpecialJurisdictionInd" => null,
                        //"OriginExciseWarehouse" => null,
                        "OriginAddress1" => isset($data['shipping_address']) ? $data['shipping_address']['address1'] : '',
                        "OriginAddress2" => null,//isset($orderData->shipping_address) ? $orderData->shipping_address->address2 : '',
                        //"OriginAirportCode" => null,
                        "DestinationCountryCode" => isset($data['shipping_address']) ? $data['shipping_address']['country_code'] : '',
                        "DestinationJurisdiction" => isset($data['shipping_address']) ? $data['shipping_address']['province_code'] : '',
                        "DestinationCounty" => "",
                        "DestinationCity" => isset($data['shipping_address']) ? $data['shipping_address']['city'] : '',
                        "DestinationPostalCode" => isset($data['shipping_address']) ? $data['shipping_address']['zip'] : '',
                        //"DestinationType" => null,
                        //"Destination" => "",
                        //"DestinationOutCityLimitInd" => null,
                        //"DestinationSpecialJurisdictionInd" => null,
                        //"DestinationExciseWarehouse" => null,
                        "DestinationAddress1" => isset($data['shipping_address']) ? $data['shipping_address']['address1'] : '',
                        "DestinationAddress2" => isset($data['shipping_address']) ? $data['shipping_address']['address2'] : '',
                        //"DestinationAirportCode" => null,
                        //"SaleCountryCode" => "",
                        //"SaleJurisdiction" => "",
                        //"SaleCounty" => "",
                        //"SaleCity" => "",
                        //"SalePostalCode" => "",
                        //"SaleType" => "",
                        //"SaleLocation" => null,
                        //"SaleOutCityLimitInd" => null,
                        //"SaleSpecialJurisdictionInd" => null,
                        //"SaleExciseWarehouse" => "",
                        //"SaleAddress1" => "",
                        //"SaleAddress2" => "",
                        //"SaleAirportCode" => "",
                        //"CounterCountryCode" => "",
                        //"CounterJurisdiction" => "",
                        //"CounterCounty" => "",
                        //"CounterCity" => "",
                        //"CounterPostalCode" => "",
                        //"CounterType" => "",
                        //"CounterParty" => null,
                        //"CounterOutCityLimitInd" => null,
                        //"CounterSpecialJurisdictionInd" => null,
                        //"CounterExciseWarehouse" => "",
                        //"CounterFiscalRepInd" => null,
                        //"CounterAddress1" => "",
                        //"CounterAddress2" => "",
                        //"CounterAirportCode" => "",
                        //"UserData" => "",
                        //"AlternativeFuelContent" => null,
                        //"BlendToAltFuelContent" => null,
                        //"BlendToInd" => null,
                        "Currency" => $currency,
                        "UnitOfMeasure" => $unitOfMeasure,
                        //"FreightUnitPrice" => null,
                        //"FreightType" => "",
                        //"FreightLineAmount" => null,
                        "CustomString1" => $itemCustomString1 ? $this->getCustomString($itemCustomString1->value, $data) : null,
                        "CustomString2" => $itemCustomString2 ? $this->getCustomString($itemCustomString2->value, $data) : null,
                        "CustomString3" => $itemCustomString3 ? $this->getCustomString($itemCustomString3->value, $data) : null,
                        "CustomNumeric1" => $itemCustomNumeric1 ? $this->getCustomNumeric($itemCustomNumeric1->value, $data) : null,
                        "CustomNumeric2" => $itemCustomNumeric2 ? $this->getCustomNumeric($itemCustomNumeric2->value, $data) : null,
                        "CustomNumeric3" => $itemCustomNumeric3 ? $this->getCustomNumeric($itemCustomNumeric3->value, $data) : null,
                        //"NthTimeSale" => null,
                        "AlternateUnitPrice" => getVariant($this->shopDomain, $line_item['variant_id']),
                        //"AlternateLineAmount" => null,
                        //"TaxIncluded" => null
                    ];
                }
            }
        }


        $requestDataAdjust = [
            'TransactionLines' => $transactionLines,
            'TransactionExchangeRates' => [],
            'EffectiveDate' => $invoiceDate,
            'InvoiceDate' => $invoiceDate,
            'InvoiceNumber' => $data['order_number'],
            //'FuelUseCode' => '',
            'TitleTransferCode' => $titleTransferCode,
            'TransactionType' => $transactionType,
            'TransportationModeCode' => $transportationModeCode,
            'Seller' => $seller,
            'Buyer' => $buyer,
            //'PreviousSeller' => null,
            //'NextBuyer' => null,
            //'Middleman' => null,
            //'CustomsStatus' => '',
            //'FormAPresentedInd' => null,
            //'SimplifiedProcedureInd' => null,
            //'Incoterms' => '',
            //'PerspectiveBusinessType' => '',
            //'ChainLeg' => null,
            //'OrderType' => '',
            //'TotalDyedUnits' => null,
            //'TotalReportingTaxes' => 0,
            //'ReportingCurrency' => '',
            //'UserData' => '',
            //'UserTranId' => 'string',
            //'SourceSystem' => '',
            'CustomString1' => $orderCustomString1 ? $this->getCustomString($orderCustomString1->value, $data) : null,
            'CustomString2' => $orderCustomString2 ? $this->getCustomString($orderCustomString2->value, $data) : null,
            'CustomString3' => $orderCustomString3 ? $this->getCustomString($orderCustomString3->value, $data) : null,
            'CustomNumeric1' => $orderCustomNumeric1 ? $this->getCustomNumeric($orderCustomNumeric1->value, $data) : null,
            'CustomNumeric2' => $orderCustomNumeric2 ? $this->getCustomNumeric($orderCustomNumeric2->value, $data) : null,
            'CustomNumeric3' => $orderCustomNumeric3 ? $this->getCustomNumeric($orderCustomNumeric3->value, $data) : null,
            //'DebugInd' => null,
            //'CalculationMethod' => null,
        ];
        if (!empty($transactionLines)) {
            $response = $client->post(env('AVALARA_API_ENDPOINT').'/AvaTaxExcise/transactions/create', [
                'auth' => [
                    $apiUsername, $apiUserPassword
                ],
                'headers' => $headers,
                'json' => $requestDataAdjust
            ]);

            $products_chunk = array_chunk($productIds, 250);
            for ($i = 0; $i < count($products_chunk); $i++) {
                $ids = $products_chunk[$i];
                $ids = implode(",", $ids);
                $param = ['limit' => 250, 'ids' => $ids];
                $data250 = $shop->api()->rest('GET', '/admin/products.json', $param);
                if (isset($data250['body']['products'])) {
                    foreach ($data250['body']['products'] as $key => $product) {
                        $tags = explode(",", $product['tags']);
                        $tags = array_map('trim', $tags);
                        Product::updateOrCreate([
                            'shop_id' => $shop->id,
                            'shopify_product_id' => $product['id'],
                        ],[
                            'shop_id' => $shop->id,
                            'shopify_product_id' => $product['id'],
                            'title' => $product['title'],
                            'handle' => $product['handle'],
                            'vendor' => $product['vendor'],
                            'tags' => $tags,
                            'image_url' => !empty($product['image']) ? $product['image']['src'] : null,
                        ]);

                        foreach ($product['variants'] as $variant) {
                            ProductVariant::updateOrCreate([
                                'shop_id' => $shop->id,
                                'variant_id' => $variant['id'],
                            ],[
                                'shop_id' => $shop->id,
                                'product_id' => $product['id'],
                                'variant_id' => $variant['id'],
                                'option_1_name' => isset($product['options'][0]) ? $product['options'][0]['name'] : null,
                                'option_1_value' => $variant['option1'],
                                'option_2_name' => isset($product['options'][1]) ? $product['options'][1]['name'] : null,
                                'option_2_value' => $variant['option2'],
                                'option_3_name' => isset($product['options'][2]) ? $product['options'][2]['name'] : null,
                                'option_3_value' => $variant['option3'],
                                'sku' => $variant['sku'],
                                'barcode' => $variant['barcode'],
                                'price' => $variant['price'],
                                'compare_at_price' => $variant['compare_at_price'],
                                'quantity' => $variant['inventory_quantity'],
                            ]);
                        }
                    }
                }
            }

            $resData = json_decode($response->getBody());
            DB::table('avalara_transaction_log')->insert([
                "status" => $response->getStatusCode(),
                "ip" => "0.0.0.0",
                "shop_id" => $shop->id,
                "total_requested_products" => count($transactionLines),
                "request_data" => json_encode($requestDataAdjust),
                "filtered_request_data" => json_encode($requestDataAdjust),
                "response" => json_encode($resData),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $transactionObj = new Transaction();
            $transactionObj->shop_id = $shop->id;
            $transactionObj->order_id = $data['id'];
            $transactionObj->order_number = $data['order_number'];
            $transactionObj->customer = isset($data['shipping_address']) ? $data['shipping_address']['name'] : '';
            $transactionObj->taxable_item = count($transactionLines);
            $transactionObj->order_total = $data['total_price'];
            $transactionObj->excise_tax = $resData->Status == 'Success' ? $resData->TotalTaxAmount : 0;
            $transactionObj->status = getOrderFulfillmentStatus($data['fulfillment_status']);
            $transactionObj->order_date = $data['created_at'];
            $transactionObj->failed_reason = null;
            //$transactionObj->is_ignore = count($transactionLines);
            $transactionObj->save();

            if ($resData->Status == 'Success') {
                foreach ($resData->TransactionTaxes as $key => $transactionTax) {
                    $exciseByProduct = ExciseByProduct::where('shop_id', $shop->id)
                        ->where('product_id', $productIds[$key])
                        ->where('date', Carbon::parse($data['created_at'])->format('Y-m-d'))->first();

                    if ($exciseByProduct) {
                        $exciseByProduct->excise_tax += $transactionTax->TaxAmount;
                        $exciseByProduct->save();
                    } else {
                        ExciseByProduct::create([
                            'shop_id' => $shop->id,
                            'product_id' => $productIds[$key],
                            'excise_tax' => $transactionTax->TaxAmount,
                            'date' => Carbon::parse($data['created_at'])->format('Y-m-d')
                        ]);
                    }
                }
            }
        }
        //}
        //}
        //}*/
    }

    /**
     * @param $stringType
     * @param $data
     *
     * @return string
     */
    function getCustomString($stringType, $data) {
        switch ($stringType) {
            case 5:
                $customerName = '';
                if ($data['customer']) {
                    $customerName = $data['customer']['first_name'].' '.$data['customer']['last_name'];
                }

                return $customerName;
            case 2:
                return $data['order_number'];
            case 3:
                $phone = '';
                if ($data['customer']) {
                    $phone = $data['customer']['phone'];
                }

                return $phone;
            case 4:
                $customerEmail = '';
                if ($data['customer']) {
                    $customerEmail = $data['customer']['email'];
                }

                return $customerEmail;
            case 1:
                return '';
        }
    }

    /**
     * @param $numericType
     * @param $data
     *
     * @return mixed
     */
    function getCustomNumeric($numericType, $data)
    {
        switch($numericType) {
            case 3:
                return $data['total_price'];
            case 2:
                $totalQuantity = 0;
                foreach ($data['line_items'] as $item) {
                    $totalQuantity += $item['quantity'];
                }

                return $totalQuantity;
            case 1:
                return '';
        }
    }
}
