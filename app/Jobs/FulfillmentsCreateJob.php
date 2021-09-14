<?php

namespace App\Jobs;

use App\Models\Setting\AvalaraCredential;
use App\Models\Setting\StaticSetting;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FulfillmentsCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shopDomain;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = json_decode($data);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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

        $orderRes = $shop->api()->rest('GET', '/admin/api/orders/'.$data->order_id.'.json');
        if (!empty($orderRes->body->order)) {
            $orderData = $orderRes->body->order;
            $client = new Client();
            $headers = [
                'Accept' => 'application/json',
                'x-company-id' => $companyId
            ];

            if (!empty($orderData->note_attributes)) {
                foreach ($orderData->note_attributes as $noteAttribute) {
                    if ($noteAttribute->name === 'transaction_id') {
                        $tags = explode(', ', $orderData->tags);
                        $tags[] = 'Transaction-id-' . $noteAttribute->value;

                        $orderObj = [
                            'id' => $orderData->id,
                            'tags' => implode(',', $tags)
                        ];
                        $shop->api()->rest('PUT', '/admin/api/orders/' . $orderData->id . '.json', ['order' => $orderObj]);

                        $parameters['what'] = 'orders';
                        $parameters['resource_id'] = $orderData->id;
                        $parameters['key'] = 'transaction_id';
                        $parameters['value'] = $noteAttribute->value;
                        metafieldsCreate($shop, $parameters);
                    }

                    if ($noteAttribute->name === 'transaction_code') {
                        $transactionCode = $noteAttribute->value;
                        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
                        $invoiceDate = Carbon::parse($orderData->created_at)->format('Y-m-d H:i:s');
                        $fulfillmentDate = null;

                        $transactionLines = $variantIds = $past_fulfilled_items = [];
                        $itemCounter = 0;

                        $fulfillmentDate = Carbon::parse($data->created_at)->format('Y-m-d H:i:s');
                        $orderMetaRes = $shop->api()->rest('GET', '/admin/api/orders/'.$data->order_id.'/metafields.json');
                        if (!empty($orderMetaRes->body->metafields)) {
                            $metaData = $orderMetaRes->body->metafields;
                            foreach ($metaData as $meta) {
                                if ($meta->key == "fulfilled_items") {
                                    $past_fulfilled_items = explode(",", $meta->value);
                                }
                            }
                        }

                        if (!empty($data->line_items)) {
                            foreach ($data->line_items as $fulfill_line_item) {
                                //if (!empty($fulfill_line_item->sku) && !in_array($fulfill_line_item->variant_id, $past_fulfilled_items)) {
                                if (!empty($fulfill_line_item->sku)) {
                                    $variantIds[] = $fulfill_line_item->variant_id;
                                    $transactionLines[] = [
                                        "TransactionLineMeasures" => null,
                                        "OriginSpecialJurisdictions" => [],
                                        "DestinationSpecialJurisdictions" => [],
                                        "SaleSpecialJurisdictions" => [],
                                        "InvoiceLine" => ++$itemCounter,
                                        //"MovementStartDate" => $currentDateTime,
                                        //"MovementEndDate" => $currentDateTime,
                                        "ProductCode" => $fulfill_line_item->sku ? Str::substr($fulfill_line_item->sku, 0, 24) : '',
                                        //"BlendToProductCode" => null,
                                        "UnitPrice" => $fulfill_line_item->price,
                                        "NetUnits" => $fulfill_line_item->quantity,
                                        "GrossUnits" => $fulfill_line_item->quantity,
                                        "BilledUnits" => $fulfill_line_item->quantity,
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
                                        "OriginAddress1" => isset($orderData->shipping_address) ? $orderData->shipping_address->address1 : '',
                                        "OriginAddress2" => null,//isset($orderData->shipping_address) ? $orderData->shipping_address->address2 : '',
                                        //"OriginAirportCode" => null,
                                        "DestinationCountryCode" => isset($orderData->shipping_address) ? $orderData->shipping_address->country_code : '',
                                        "DestinationJurisdiction" => isset($orderData->shipping_address) ? $orderData->shipping_address->province_code : '',
                                        "DestinationCounty" => "",
                                        "DestinationCity" => isset($orderData->shipping_address) ? $orderData->shipping_address->city : '',
                                        "DestinationPostalCode" => isset($orderData->shipping_address) ? $orderData->shipping_address->zip : '',
                                        //"DestinationType" => null,
                                        //"Destination" => "",
                                        //"DestinationOutCityLimitInd" => null,
                                        //"DestinationSpecialJurisdictionInd" => null,
                                        //"DestinationExciseWarehouse" => null,
                                        "DestinationAddress1" => isset($orderData->shipping_address) ? $orderData->shipping_address->address1 : '',
                                        "DestinationAddress2" => isset($orderData->shipping_address) ? $orderData->shipping_address->address2 : '',
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
                                        "CustomString1" => $itemCustomString1 ? getCustomString($itemCustomString1->value, $orderData) : null,
                                        "CustomString2" => $itemCustomString2 ? getCustomString($itemCustomString2->value, $orderData) : null,
                                        "CustomString3" => $itemCustomString3 ? getCustomString($itemCustomString3->value, $orderData) : null,
                                        "CustomNumeric1" => $itemCustomNumeric1 ? getCustomNumeric($itemCustomNumeric1->value, $orderData) : null,
                                        "CustomNumeric2" => $itemCustomNumeric2 ? getCustomNumeric($itemCustomNumeric2->value, $orderData) : null,
                                        "CustomNumeric3" => $itemCustomNumeric3 ? getCustomNumeric($itemCustomNumeric3->value, $orderData) : null,
                                        //"NthTimeSale" => null,
                                        "AlternateUnitPrice" => getVariant($this->shopDomain, $fulfill_line_item->variant_id),
                                        //"AlternateLineAmount" => null,
                                        //"TaxIncluded" => null
                                    ];
                                }
                            }
                        }


                        $requestDataAdjust = [
                            'adjustmentReason' => 0,
                            'adjustmentDescription' => '',
                            'newTransaction' => [
                                'TransactionLines' => $transactionLines,
                                'TransactionExchangeRates' => [],
                                'EffectiveDate' => $fulfillmentDate,
                                'InvoiceDate' => $invoiceDate,
                                'InvoiceNumber' =>$orderData->order_number,
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
                                'CustomString1' => $orderCustomString1 ? getCustomString($orderCustomString1->value, $orderData) : null,
                                'CustomString2' => $orderCustomString2 ? getCustomString($orderCustomString2->value, $orderData) : null,
                                'CustomString3' => $orderCustomString3 ? getCustomString($orderCustomString3->value, $orderData) : null,
                                'CustomNumeric1' => $orderCustomNumeric1 ? getCustomNumeric($orderCustomNumeric1->value, $orderData) : null,
                                'CustomNumeric2' => $orderCustomNumeric2 ? getCustomNumeric($orderCustomNumeric2->value, $orderData) : null,
                                'CustomNumeric3' => $orderCustomNumeric3 ? getCustomNumeric($orderCustomNumeric3->value, $orderData) : null,
                                //'DebugInd' => null,
                                //'CalculationMethod' => null,
                            ],
                        ];

                        if (!empty($transactionLines)) {
                            $response = $client->post(env('AVALARA_API_ENDPOINT').'/AvaTaxExcise/transactions/' . $transactionCode . '/adjust', [
                                'auth' => [
                                    $apiUsername, $apiUserPassword
                                ],
                                'headers' => $headers,
                                'json' => $requestDataAdjust
                            ]);


                            if (!empty($orderMetaRes->body->metafields)) {
                                $metaData = $orderMetaRes->body->metafields;
                                foreach ($metaData as $meta) {
                                    if ($meta->key == "fulfilled_items") {
                                        $fulfilled_items = explode(",", $meta->value);
                                        $fulfilled_items = array_unique(array_merge($fulfilled_items, $variantIds));
                                        $parameters['key'] = 'fulfilled_items';
                                        $parameters['value'] = implode(",", $fulfilled_items);
                                        $parameters['resource_id'] = $meta->id;
                                        metafieldsUpdate($shop, $parameters);
                                    } else {
                                        $parameters['what'] = 'orders';
                                        $parameters['resource_id'] = $orderData->id;
                                        $parameters['key'] = 'fulfilled_items';
                                        $parameters['value'] = implode(",", $variantIds);
                                        metafieldsCreate($shop, $parameters);
                                    }
                                }
                            } else {
                                $parameters['what'] = 'orders';
                                $parameters['resource_id'] = $orderData->id;
                                $parameters['key'] = 'fulfilled_items';
                                $parameters['value'] = implode(",", $variantIds);
                                metafieldsCreate($shop, $parameters);
                            }

                            $resData = json_decode($response->getBody());
                            DB::table('avalara_transaction_log')->insert([
                                "status" => 0,
                                "total_requested_products" => count($transactionLines),
                                "order_id" => $orderData->id,
                                "order_number" => $orderData->order_number,
                                "request_data" => json_encode($requestDataAdjust),
                                "filtered_request_data" => json_encode($requestDataAdjust),
                                "response" => json_encode($resData),
                                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                            ]);

                            $transactionObj = new Transaction();
                            $transactionObj->order_id = $orderData->id;
                            $transactionObj->order_number = $orderData->order_number;
                            $transactionObj->customer = isset($orderData->shipping_address) ? $orderData->shipping_address->name : '';
                            $transactionObj->taxable_item = count($transactionLines);
                            $transactionObj->order_total = $orderData->total_price;
                            $transactionObj->excise_tax = 0;
                            $transactionObj->status = getOrderFulfillmentStatus($orderData->fulfillment_status);
                            $transactionObj->order_date = $orderData->created_at;
                            $transactionObj->failed_reason = count($transactionLines);
                            //$transactionObj->is_ignore = count($transactionLines);
                            $transactionObj->save();
                        }
                    }
                }
            }
        }*/
    }
}
