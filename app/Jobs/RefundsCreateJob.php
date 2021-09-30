<?php

namespace App\Jobs;

use App\Models\AvalaraExciseTaxProduct;
use App\Models\AvalaraTransactionLog;
use App\Models\ExciseByProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use App\Traits\Helpers;
use App\Traits\TransactionHelpers;
use Carbon\Carbon;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Helpers, TransactionHelpers;

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

        $shop = User::where(['name' => $this->shopDomain])->first();

        list($companyId, $apiUsername, $apiUserPassword) = $this->avalaraCredentials($shop->id);

        $isShopifyPlus = $shop->is_shopify_plus;
        $productForExcise = Helpers::productForExcise($shop->id);
        $productIdentifierForExcise = $this->productIdentifierForExcise($shop->id);
        $avalaraProductVariantId = AvalaraExciseTaxProduct::where('shop_id', $shop->id)->value('avalara_variant_id');
        $isRefundRequired = false;
        $fulfillableQuantity = 0;
        $isAvalaraItemRefund = false;
        $totalExciseTax = 0;
        $refundedExciseTax = 0;

        $headers = [
            'Accept'       => 'application/json',
            'x-company-id' => $companyId,
        ];

        $orderRes = $shop->api()->rest('GET', '/admin/orders/'.$data->order_id.'.json');
        if (isset($orderRes['body']['order'])) {
            $orderData = $orderRes['body']['order'];
            foreach ($orderData['line_items'] as $lineItem) {
                if ($lineItem['variant_id'] === $avalaraProductVariantId) {
                    $isRefundRequired = true;
                    $totalExciseTax = $lineItem['pre_tax_price'];
                    continue;
                }
                $fulfillableQuantity += $lineItem['fulfillable_quantity'];
            }
            foreach ($orderData['refunds'] as $refund) {
                if ($refund['note'] === 'Excise Tax refunded') {
                    $refundedExciseTax += $refund['transactions'][0]['amount'];
                }
            }
            if ($fulfillableQuantity === 0) {
                $isAvalaraItemRefund = true;
            }

            if (!empty($orderData['note_attributes']) && empty($orderData['cancelled_at'])) {

                $invoiceDate = Carbon::parse($data->created_at)->format('Y-m-d H:i:s');

                $transactionLines = $variantIds = $productIds = [];
                $itemCounter = 0;
                if (!empty($data->refund_line_items)) {
                    foreach ($data->refund_line_items as $line_item) {
                        if ($line_item->line_item->variant_id === $avalaraProductVariantId) {
                            $isRefundRequired = false;
                        }
                        if (!empty($line_item->line_item->sku)) {

                            $productTags = $shop->api()->rest('GET',
                                '/admin/products/'.$line_item->line_item->product_id.'.json');
                            if (isset($productTags['body']['product']) && !empty($productTags['body']['product'])) {
                                $productTags = $productTags['body']['product']['tags'];
                            }
                            $item['ProductCode'] = $item['itemSKU'] = Str::substr($line_item->line_item->sku, 0, 24);
                            $item['tags'] = $productTags;
                            if (!filterRequest($item, $productForExcise, $productIdentifierForExcise, $shop->id)) {
                                continue;
                            }

                            ++$itemCounter;
                            $variantIds[] = $line_item->line_item->variant_id;
                            $productIds[] = $line_item->line_item->product_id;
                            $computedLineItem = (object) $line_item->line_item;
                            $computedLineItem->invoice_date = $invoiceDate;
                            $computedLineItem->quantity = $line_item->quantity;
                            $computedLineItem->invoice_line = $itemCounter;
                            $transactionLines[] = $this->prepareTransactionLine($shop->id, $computedLineItem,
                                (object) $orderData['shipping_address'], false, (object) $orderData);
                        }
                    }
                }

                $avalaraRequestData = $this->prepareAvalaraRequestData($shop->id, $invoiceDate,
                    $orderData['order_number'], (object) $orderData);
                $avalaraRequestData['TransactionLines'] = $transactionLines;

                if (!empty($transactionLines)) {

                    $newService = new TransactionService();
                    $newService->setCredentials($apiUsername, $apiUserPassword, $companyId);
                    $response = $newService->calculateExcice($avalaraRequestData);

                    $newService->dataStore($productIds, $shop, $avalaraRequestData, $transactionLines, $response);

                    $exciseTax = 0;
                    $transactionError = null;
                    if ($response->status() == 200) {
                        $responseTemp = json_decode($response->body());
                        $exciseTax = $responseTemp->TotalTaxAmount;

                        foreach ($responseTemp->TransactionTaxes as $key => $transactionTax) {
                            if (isset($productIds[$key])) {
                                $exciseByProduct = ExciseByProduct::where('shop_id', $shop->id)
                                    ->where('product_id', $productIds[$key])
                                    ->where('date', Carbon::parse($orderData['created_at'])->format('Y-m-d'))
                                    ->first();
                            }
                            if ($exciseByProduct) {
                                $exciseByProduct->excise_tax += $transactionTax->TaxAmount;
                                $exciseByProduct->save();
                            } else {
                                ExciseByProduct::create([
                                    'shop_id'      => $shop->id,
                                    'product_id'   => $productIds[$key],
                                    'excise_tax'   => $transactionTax->TaxAmount,
                                    'date'         => Carbon::parse($orderData['created_at'])->format('Y-m-d'),
                                ]);
                            }
                        }
                    } else {
                        $transactionError = json_encode($response->body());
                    }

                    $refundTransaction = Transaction::where('shop_id', $shop->id)->where('order_id',
                        $data->order_id)->first();
                    if ($refundTransaction) {
                        $refundParentId = $refundTransaction->id;
                    }

                    $transactionObj = new Transaction();
                    $transactionObj->shop_id = $shop->id;
                    $transactionObj->order_id = $data->order_id;
                    $transactionObj->parent_id = $refundParentId ?? null;
                    $transactionObj->order_number = $orderData['order_number'];
                    $transactionObj->customer = isset($orderData['shipping_address']) ? $orderData['shipping_address']['name'] : '';
                    $transactionObj->taxable_item = count($transactionLines);
                    $transactionObj->order_total = $orderData['total_price'];
                    $transactionObj->excise_tax = $exciseTax;//$resData->Status == 'Success' ? $resData->TotalTaxAmount : 0;
                    $transactionObj->status = $this->getOrderFulfillmentStatus($orderData['fulfillment_status']);
                    $transactionObj->financial_status = $this->getOrderFinancialStatus($orderData['financial_status']);
                    $transactionObj->order_date = $data->created_at;
                    $transactionObj->state = isset($orderData['shipping_address']) ? $orderData['shipping_address']['province'] : '';
                    $transactionObj->failed_reason = $transactionError;
                    $transactionObj->save();
                }
            }
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
        switch ($numericType) {
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
