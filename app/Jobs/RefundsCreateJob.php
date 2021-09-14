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
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RefundsCreateJob implements ShouldQueue
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
     * RefundsCreateJob constructor.
     *
     * @param $shopDomain
     * @param $data
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
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
        list(
            $titleTransferCode, $transactionType, $transportationModeCode, $seller, $buyer, $unitOfMeasure, $currency, $origin,
            $orderCustomString1, $orderCustomString2, $orderCustomString3, $orderCustomNumeric1, $orderCustomNumeric2, $orderCustomNumeric3,
            $itemCustomString1, $itemCustomString2, $itemCustomString3, $itemCustomNumeric1, $itemCustomNumeric2, $itemCustomNumeric3
            ) = Helpers::staticSettings($shop->id);

        list($companyId, $apiUsername, $apiUserPassword) = Helpers::avalaraCredentials($shop->id);

        $productForExcise = Helpers::productForExcise($shop->id);
        $productIdentifierForExcise = Helpers::productIdentifierForExcise($shop->id);

        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $companyId
        ];

        $orderRes = $shop->api()->rest('GET', '/admin/orders/'.$data->order_id.'.json');
        if (isset($orderRes['body']['order'])) {
            $orderData = $orderRes['body']['order'];

            if (!empty($orderData['note_attributes']) && empty($orderData['cancelled_at'])) {
                foreach ($orderData['note_attributes'] as $noteAttribute) {

                    if ($noteAttribute['name'] === 'transaction_id') {
                        $transactionCode = $noteAttribute['value'];
                        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
                        $invoiceDate = Carbon::parse($orderData['created_at'])->format('Y-m-d H:i:s');
                        $refundDate = Carbon::parse($data->created_at)->format('Y-m-d H:i:s');

                        $transactionLines = $variantIds = $productIds = $past_fulfilled_items = [];
                        $itemCounter = 0;

                        if (!empty($data->refund_line_items)) {
                            foreach ($data->refund_line_items as $line_item) {
                                if (!empty($line_item->line_item->sku)) {

                                    $productTags = $shop->api()->rest('GET', '/admin/products/'.$line_item->line_item->product_id.'.json');
                                    if(isset($productTags['body']['product']) && !empty($productTags['body']['product'])) {
                                        $productTags = $productTags['body']['product']['tags'];
                                    }
                                    $item['ProductCode'] = $item['itemSKU'] = Str::substr($line_item->line_item->sku, 0, 24);
                                    $item['tags'] = $productTags;
                                    if (!filterRequest($item, $productForExcise, $productIdentifierForExcise)) {
                                        continue;
                                    }

                                    $variantIds[] = $line_item->line_item->variant_id;
                                    $productIds[] = $line_item->line_item->product_id;
                                    $transactionLines[] = [
                                        "TransactionLineMeasures" => null,
                                        "OriginSpecialJurisdictions" => [],
                                        "DestinationSpecialJurisdictions" => [],
                                        "SaleSpecialJurisdictions" => [],
                                        "InvoiceLine" => ++$itemCounter,
                                        "ProductCode" => $line_item->line_item->sku ? Str::substr($line_item->line_item->sku, 0, 24) : '',
                                        "UnitPrice" => $line_item->line_item->price,
                                        "NetUnits" => $line_item->quantity,
                                        "GrossUnits" => $line_item->quantity,
                                        "BilledUnits" => -$line_item->quantity,
                                        "BillOfLadingDate" => $invoiceDate,
                                        "Origin" => $origin,
                                        "OriginAddress1" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['address1'] : '',
                                        "OriginAddress2" => null,//isset($orderData['shipping_address']) ? $orderData['shipping_address']['address2'] : '',
                                        "DestinationCountryCode" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['country_code'] : '',
                                        "DestinationJurisdiction" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['province_code'] : '',
                                        "DestinationCounty" => "",
                                        "DestinationCity" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['city'] : '',
                                        "DestinationPostalCode" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['zip'] : '',
                                        "DestinationAddress1" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['address1'] : '',
                                        "DestinationAddress2" => isset($orderData['shipping_address']) ? $orderData['shipping_address']['address2'] : '',
                                        "Currency" => $currency,
                                        "UnitOfMeasure" => $unitOfMeasure,
                                        "CustomString1" => $itemCustomString1 ? $this->getCustomString($itemCustomString1->value, $orderData) : null,
                                        "CustomString2" => $itemCustomString2 ? $this->getCustomString($itemCustomString2->value, $orderData) : null,
                                        "CustomString3" => $itemCustomString3 ? $this->getCustomString($itemCustomString3->value, $orderData) : null,
                                        "CustomNumeric1" => $itemCustomNumeric1 ? $this->getCustomNumeric($itemCustomNumeric1->value, $orderData) : null,
                                        "CustomNumeric2" => $itemCustomNumeric2 ? $this->getCustomNumeric($itemCustomNumeric2->value, $orderData) : null,
                                        "CustomNumeric3" => $itemCustomNumeric3 ? $this->getCustomNumeric($itemCustomNumeric3->value, $orderData) : null,
                                        //"AlternateUnitPrice" => getVariant($this->shopDomain->toNative(), $line_item->line_item->variant_id),
                                    ];
                                }
                            }
                        }

                        $additionalStaticField = Helpers::additionalField($shop->id);
                        $requestDataAdjust = [
                            'TransactionLines' => $transactionLines,
                            'TransactionExchangeRates' => [],
                            'EffectiveDate' => $invoiceDate,
                            'InvoiceDate' => $invoiceDate,
                            'InvoiceNumber' =>$orderData['order_number'],
                            'TitleTransferCode' => $titleTransferCode,
                            'TransactionType' => $transactionType,
                            'TransportationModeCode' => $transportationModeCode,
                            'Seller' => $seller,
                            'Buyer' => $buyer,
                            'PreviousSeller' => isset($additionalStaticField['previous_seller']) ? $additionalStaticField['previous_seller'] : '',
                            'NextBuyer' => isset($additionalStaticField['next_buyer']) ? $additionalStaticField['next_buyer'] : '',
                            'Middleman' => isset($additionalStaticField['middleman']) ? $additionalStaticField['middleman'] : '',
                            'FuelUseCode' => isset($additionalStaticField['fuel_use_code']) ? $additionalStaticField['fuel_use_code'] : '',
                            'CustomString1' => $orderCustomString1 ? $this->getCustomString($orderCustomString1->value, $orderData) : null,
                            'CustomString2' => $orderCustomString2 ? $this->getCustomString($orderCustomString2->value, $orderData) : null,
                            'CustomString3' => $orderCustomString3 ? $this->getCustomString($orderCustomString3->value, $orderData) : null,
                            'CustomNumeric1' => $orderCustomNumeric1 ? $this->getCustomNumeric($orderCustomNumeric1->value, $orderData) : null,
                            'CustomNumeric2' => $orderCustomNumeric2 ? $this->getCustomNumeric($orderCustomNumeric2->value, $orderData) : null,
                            'CustomNumeric3' => $orderCustomNumeric3 ? $this->getCustomNumeric($orderCustomNumeric3->value, $orderData) : null,
                        ];

                        if (!empty($transactionLines)) {

                            $http = Http::timeout(60)->withHeaders($headers);
                            $http->withBasicAuth($apiUsername, $apiUserPassword);
                            $response = $http->post(env('AVALARA_API_ENDPOINT') . '/AvaTaxExcise/transactions/create', $requestDataAdjust);

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

                            DB::table('avalara_transaction_log')->insert([
                                "ip" => "0.0.0.0",
                                "shop_id" => $shop->id,
                                "request_data" => json_encode($requestDataAdjust),
                                "total_requested_products" => count($transactionLines),
                                "response" => $response->status() != 200 ? json_encode($response->body()) : $response->body(),
                                "filtered_request_data" => json_encode($requestDataAdjust),
                                "status" =>$response->status(),
                                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                            ]);

                            $exciseTax = 0;
                            $transactionError = null;
                            if ($response->status() == 200) {
                                $responseTemp = json_decode($response->body());
                                $exciseTax = $responseTemp->TotalTaxAmount;

                                foreach ($responseTemp->TransactionTaxes as $key => $transactionTax) {
                                    if (isset($productIds[$key])) {
                                        $exciseByProduct = ExciseByProduct::where('shop_id', $shop->id)
                                            ->where('product_id', $productIds[$key])
                                            ->where('date', Carbon::parse($orderData['created_at'])->format('Y-m-d'))->first();
                                    }
                                    if ($exciseByProduct) {
                                        $exciseByProduct->excise_tax += $transactionTax->TaxAmount;
                                        $exciseByProduct->save();
                                    } else {
                                        ExciseByProduct::create([
                                            'shop_id' => $shop->id,
                                            'product_id' => $productIds[$key],
                                            'excise_tax' => $transactionTax->TaxAmount,
                                            'date' => Carbon::parse($orderData['created_at'])->format('Y-m-d')
                                        ]);
                                    }
                                }
                            } else {
                                $transactionError = json_encode($response->body());
                            }

                            $transactionObj = new Transaction();
                            $transactionObj->shop_id = $shop->id;
                            $transactionObj->order_id = $data->order_id;
                            $transactionObj->order_number = $orderData['order_number'];
                            $transactionObj->customer = isset($orderData['shipping_address']) ? $orderData['shipping_address']['name'] : '';
                            $transactionObj->taxable_item = count($transactionLines);
                            $transactionObj->order_total = $orderData['total_price'];
                            $transactionObj->excise_tax = $exciseTax;//$resData->Status == 'Success' ? $resData->TotalTaxAmount : 0;
                            $transactionObj->status = Helpers::getOrderFulfillmentStatus($orderData['fulfillment_status']);
                            $transactionObj->order_date = $orderData['created_at'];
                            $transactionObj->state = isset($orderData['shipping_address']) ? $orderData['shipping_address']['province'] : '';
                            $transactionObj->failed_reason = $transactionError;
                            $transactionObj->save();
                        }
                    }
                }
            }
        }
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
