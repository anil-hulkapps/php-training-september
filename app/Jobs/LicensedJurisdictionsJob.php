<?php

namespace App\Jobs;

use App\Models\ExciseByProduct;
use App\Models\Jurisdictions;
use App\Models\LicensedJurisdiction;
use App\Models\Product;
use App\Models\ProductInfo;
use App\Models\ProductVariant;
use App\Models\ScheduelTask;
use App\Models\Transaction;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicensedJurisdictionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $shopId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopId = $this->shopId;
        list(
            $titleTransferCode, $transactionType, $transportationModeCode, $seller, $buyer, $unitOfMeasure, $currency, $origin,
            $orderCustomString1, $orderCustomString2, $orderCustomString3, $orderCustomNumeric1, $orderCustomNumeric2, $orderCustomNumeric3,
            $itemCustomString1, $itemCustomString2, $itemCustomString3, $itemCustomNumeric1, $itemCustomNumeric2, $itemCustomNumeric3
            ) = Helpers::staticSettings($shopId);

        list($companyId, $apiUsername, $apiUserPassword) = Helpers::avalaraCredentials($shopId);
        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $companyId
        ];

        $jurisdictions = Jurisdictions::all();

        $importBatch = ScheduelTask::where('shop_id', $shopId)->latest()->first();
        $productCode = null;
        if ($importBatch) {
            $batchId = $importBatch->id;
            $latestMappedProduct = ProductInfo::where('schedule_id', $batchId)->latest()->first();
            if ($latestMappedProduct) {
                $productCode = $latestMappedProduct->alternate_product_code;
            }
        }
        foreach ($jurisdictions as $jurisdiction) {
            $transactionLines = [];
            $transactionLines[] = [
                "TransactionLineMeasures" => null,
                "OriginSpecialJurisdictions" => [],
                "DestinationSpecialJurisdictions" => [],
                "SaleSpecialJurisdictions" => [],
                "InvoiceLine" => 1,
                "ProductCode" => $productCode,
                "UnitPrice" => 1,
                "NetUnits" => 1,
                "GrossUnits" => 1,
                "BilledUnits" => 1,
                "Origin" => $origin,
                "OriginAddress1" => $jurisdiction->address1,
                "OriginAddress2" => null,
                "DestinationCountryCode" => $jurisdiction->country_code,
                "DestinationJurisdiction" => $jurisdiction->province_code,
                "DestinationCounty" => "",
                "DestinationCity" => $jurisdiction->city,
                "DestinationPostalCode" => $jurisdiction->zip,
                "DestinationAddress1" => $jurisdiction->address1,
                "DestinationAddress2" => $jurisdiction->address2,
                "Currency" => $currency,
                "UnitOfMeasure" => $unitOfMeasure,
            ];

            $requestDataAdjust = [
                'TransactionLines' => $transactionLines,
                'TransactionExchangeRates' => [],
                'EffectiveDate' => "",
                'InvoiceDate' => "",
                'InvoiceNumber' => "",
                'TitleTransferCode' => $titleTransferCode,
                'TransactionType' => $transactionType,
                'TransportationModeCode' => $transportationModeCode,
                'Seller' => $seller,
                'Buyer' => $buyer,
            ];

            if (!empty($transactionLines)) {

                $http = Http::timeout(60)->withHeaders($headers);
                $http->withBasicAuth($apiUsername, $apiUserPassword);
                $response = $http->post(env('AVALARA_API_ENDPOINT') . '/AvaTaxExcise/transactions/create', $requestDataAdjust);

                if ($response->status() == 200) {
                    Log::info(json_encode($response->body()));
                    $responseTemp = json_decode($response->body());
                    $exciseTax = $responseTemp->TotalTaxAmount;
                    $status = $responseTemp->Status;
                    if (Str::lower($status) == 'success' && $exciseTax > 0) {
                        LicensedJurisdiction::updateOrCreate([
                            'jurisdiction' => $jurisdiction->province_code,
                            'country' => $jurisdiction->country,
                            'country_code' => $jurisdiction->country_code,
                            'province' => $jurisdiction->province,
                        ],[
                            'jurisdiction' => $jurisdiction->province_code,
                            'country' => $jurisdiction->country,
                            'country_code' => $jurisdiction->country_code,
                            'province' => $jurisdiction->province,
                        ]);
                    }
                }
            }
        }
    }
}
