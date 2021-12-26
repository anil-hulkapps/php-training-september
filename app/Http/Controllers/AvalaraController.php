<?php

namespace App\Http\Controllers;

use App\Models\LicensedJurisdiction;
use App\Models\ProductInfo;
use App\Models\Setting\AvalaraCredential;
use App\Models\Setting\FailoverCheckout;
use App\Models\Setting\ProductForExcise;
use App\Models\Setting\ProductIdentifierForExcise;
use App\Models\Setting\StaticSetting;
use App\Models\User;
use App\Traits\Helpers;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AvalaraController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|string
     */
    public function create(Request $request)
    {
        $input = $request->all();
        $requestData = $request['data'];
        $data = $input;
        $requestDataAdjust = $requestData;
        $transactionLines = $requestData['TransactionLines'];
        $shop = $request->get('shop');


        list($companyId, $apiUsername, $apiUserPassword) = Helpers::avalaraCredentials($shop->id);

        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $companyId,
        ];

        list(
                $titleTransferCode, $transactionType, $transportationModeCode, $seller, $buyer, $unitOfMeasure, $currency, $origin,
                $orderCustomString1, $orderCustomString2, $orderCustomString3, $orderCustomNumeric1, $orderCustomNumeric2, $orderCustomNumeric3,
                $itemCustomString1, $itemCustomString2, $itemCustomString3, $itemCustomNumeric1, $itemCustomNumeric2, $itemCustomNumeric3
            ) = Helpers::staticSettings($shop->id);


        $productForExcise = Helpers::productForExcise($shop->id);
        $productIdentifierForExcise = Helpers::productIdentifierForExcise($shop->id);

        $requestDataAdjust['TransactionLines'] = [];
        foreach ($transactionLines as $item) {
            $isLicensedJurisdiction = LicensedJurisdiction::where('jurisdiction', $item['DestinationJurisdiction'])->exists();

            //If product's destinationJurisdiction can be found in our database, It will terminate script.
            if (!$isLicensedJurisdiction) {
                $failover = Helpers::failoverCheckout($shop->id, 1);
                return response(["error" => $failover['failoverMessage']], $failover['statusCode']);
                break;
            }

            if ($this->filterRequest($item, $productForExcise, $productIdentifierForExcise)) {
                unset($item['tags'], $item['itemSKU']);
                $item['UnitOfMeasure'] = $unitOfMeasure;
                $item['Currency'] = $currency;
                $item['Origin'] = $origin;
                $requestDataAdjust['TransactionLines'][] = $item;
            }

        }

        if (!empty($requestDataAdjust['TransactionLines'])) {
            $requestDataAdjust['TitleTransferCode'] = $titleTransferCode;
            $requestDataAdjust['TransactionType'] = $transactionType;
            $requestDataAdjust['TransportationModeCode'] = $transportationModeCode;
            $requestDataAdjust['Seller'] = $seller;
            $requestDataAdjust['Buyer'] = $buyer;

            $http = Http::timeout(60)->withHeaders($headers);
            $http->withBasicAuth($apiUsername, $apiUserPassword);
            $response = $http->post(env('AVALARA_API_ENDPOINT') . '/AvaTaxExcise/transactions/create', $requestDataAdjust);

            DB::table('avalara_transaction_log')->insert([
                "ip" => $request->ip(),
                "shop_id" => $shop->id,
                "request_data" => json_encode($requestData),
                "total_requested_products" => count($transactionLines),
                "response" => $response->status() != 200 ? json_encode($response->body()) : $response->body(),
                "filtered_request_data" => json_encode($requestDataAdjust),
                "status" =>$response->status(),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            if ($response->status() != 200) {
                $failover = Helpers::failoverCheckout($shop->id);
                return response(["error" => $failover['failoverMessage'], "disable_checkout" => $failover['disableCheckout']], $failover['statusCode']);
            } else {
                return $response->body();
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function failoverMetafieldCreate(Request $request) {
        $shop = $request->get('shop');
        $shop = User::where('id', $shop->id)->first();
        $failoverCheckout = FailoverCheckout::where('shop_id', $shop->id)->first();
        $messages = json_decode($failoverCheckout->failover_message);

        $metafieldObj = [];
        foreach ($messages as $key => $message) {
            switch ($key) {
                case 1:
                    $metafieldObj['place_order'] = $message;
                    break;
                case 2:
                    $metafieldObj['does_not_place_order'] = $message;
                    break;
                case 3:
                    $metafieldObj['unauthorized'] = $message;
                    break;
            }
        }
        $metafieldObj['selected_option'] = $failoverCheckout->action == 1 ? 'place_order' : 'does_not_place_order';

        $parameters['namespace'] = "ava_failover_setting";
        $parameters['key'] = "ava_failover_setting";
        $parameters['value'] = json_encode($metafieldObj);
        $parameters['value_type'] = 'json_string';
        $url = '/admin/metafields.json';
        $metafield['metafield'] = $parameters;
        $shop->api()->rest('POST', $url, $metafield);

        return response(json_encode($metafieldObj));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|string
     */
    public function createTest(Request $request)
    {
        $input = $request->all();
        $requestData = $request['data'];
        $requestDataAdjust = $requestData;
        $transactionLines = $requestData['TransactionLines'];

        $shop = User::where('name', $input['shopDomain'])->first();
        //$shop = $request->get('shop');

        list($companyId, $apiUsername, $apiUserPassword) = Helpers::avalaraCredentials($shop->id);

        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $companyId,
        ];

        list(
            $titleTransferCode, $transactionType, $transportationModeCode, $seller, $buyer, $unitOfMeasure, $currency, $origin,
            $orderCustomString1, $orderCustomString2, $orderCustomString3, $orderCustomNumeric1, $orderCustomNumeric2, $orderCustomNumeric3,
            $itemCustomString1, $itemCustomString2, $itemCustomString3, $itemCustomNumeric1, $itemCustomNumeric2, $itemCustomNumeric3
            ) = Helpers::staticSettings($shop->id);

        $productForExcise = Helpers::productForExcise($shop->id);
        $productIdentifierForExcise = Helpers::productIdentifierForExcise($shop->id);

        $requestDataAdjust['TransactionLines'] = [];
        foreach ($transactionLines as $item) {
            $isLicensedJurisdiction = LicensedJurisdiction::where('jurisdiction', $item['DestinationJurisdiction'])->exists();

            //If product's destinationJurisdiction can be found in our database, It will terminate script.
            if (!$isLicensedJurisdiction) {
                $failover = Helpers::failoverCheckout($shop->id, 1);
                return response(["error" => $failover['failoverMessage']], $failover['statusCode']);
                break;
            }

            if ($this->filterRequest($item, $productForExcise, $productIdentifierForExcise)) {
                unset($item['tags'], $item['itemSKU']);
                $item['UnitOfMeasure'] = $unitOfMeasure;
                $item['Currency'] = $currency;
                $item['Origin'] = $origin;
                $requestDataAdjust['TransactionLines'][] = $item;
            }

        }

        //return $requestDataAdjust;
        if (!empty($requestDataAdjust['TransactionLines'])) {
            $requestDataAdjust['TitleTransferCode'] = $titleTransferCode;
            $requestDataAdjust['TransactionType'] = $transactionType;
            $requestDataAdjust['TransportationModeCode'] = $transportationModeCode;
            $requestDataAdjust['Seller'] = $seller;
            $requestDataAdjust['Buyer'] = $buyer;

            $http = Http::timeout(60)->withHeaders($headers);
            $http->withBasicAuth($apiUsername, $apiUserPassword);
            $response = $http->post(env('AVALARA_API_ENDPOINT') . '/AvaTaxExcise/transactions/create', $requestDataAdjust);

            DB::table('avalara_transaction_log')->insert([
                "ip" => $request->ip(),
                "shop_id" => $shop->id,
                "request_data" => json_encode($requestData),
                "total_requested_products" => count($transactionLines),
                "response" => $response->status() != 200 ? json_encode($response->body()) : $response->body(),
                "filtered_request_data" => json_encode($requestDataAdjust),
                "status" =>$response->status(),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            if ($response->status() != 200) {
                $failover = Helpers::failoverCheckout($shop->id);
                return response(["error" => $failover['failoverMessage'], "disable_checkout" => $failover['disableCheckout']], $failover['statusCode']);
            } else {
                return $response->body();
            }
        } else {
            return response(["excise_tax" => 0], 200);
        }
    }

    /**
     * @param $item
     * @param $productForExcise
     * @param $productIdentifierForExcise
     * @return bool
     */
    public function filterRequest($item, $productForExcise, $productIdentifierForExcise)
    {
        if ($productForExcise->option == 2) {
            $isExist = ProductInfo::where('alternate_product_code', $item['ProductCode'])->exists();
            if (!$isExist) {
                return false;
            }
        }

        /*switch ($productForExcise->option) {
            case 1:
            case 2:
            case 3:
                if (!$this->checkProductForExcise($productForExcise->value, $item['tags'])) {
                    return false;
                }

                break;
            case 4:
                $data = json_decode($productForExcise->value);
                $selectedTagsForExcise = [];
                if (count($data) > 0) {
                    foreach ($data as $tag) {
                        $selectedTagsForExcise[] = $tag->value;
                    }
                }

                if (!in_array($item['product_type'], $selectedTagsForExcise)) {
                    return false;
                }
            case 5:
                $data = json_decode($productForExcise->value);
                $selectedTagsForExcise = [];
                if (count($data) > 0) {
                    foreach ($data as $tag) {
                        $selectedTagsForExcise[] = $tag->value;
                    }
                }

                if (!in_array($item['vendor'], $selectedTagsForExcise)) {
                    return false;
                }
        }*/

        /*switch ($productIdentifierForExcise->identifier) {
            case 1:
                return $this->checkTagPattern($productIdentifierForExcise->option, $item['tags'], $productIdentifierForExcise->value);
            case 2:
                return $this->checkString($productIdentifierForExcise->option, $item['itemSKU'], $productIdentifierForExcise->value);
        }*/
        return true;
    }

    /**
     * @param $type
     * @param $data
     * @param $ref
     * @return bool
     */
    public function checkString($type, $data, $ref) {
        switch ($type) {
            case 1:
                if (!str_starts_with($data, $ref)) {
                    return false;
                }
                break;
            case 2:
                if (!str_ends_with($data, $ref)) {
                    return false;
                }
                break;
            case 3:
                if (!str_contains($data, $ref)) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * @param $data
     * @param $itemTags
     * @return bool
     */
    public function checkProductForExcise($data, $itemTags)
    {
        $data = json_decode($data);
        $selectedTagsForExcise = [];
        if (count($data) > 0) {
            foreach ($data as $tag) {
                $selectedTagsForExcise[] = $tag->value;
            }
        }
        $tags = [];
        if (!empty($itemTags)) {
            $tags = explode(',', $itemTags);
        }
        foreach ($selectedTagsForExcise as $selectedTag) {
            if (!in_array($selectedTag, $tags)) {
                return false;
                break;
            }
        }

        return true;
    }

    /**
     * @param $type
     * @param $itemTags
     * @param $ref
     * @return bool
     */
    public function checkTagPattern($type, $itemTags, $ref)
    {
        $tags = [];
        if (!empty($itemTags)) {
            $tags = array_map('trim', explode(',', $itemTags));
        }
        foreach ($tags as $tag) {
            if (!$this->checkString($type, $tag, $ref)) {
                return false;
            }
        }

        return true;
    }
}
