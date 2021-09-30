<?php

use App\Models\ProductInfo;
/**
 * Create a metafields.
 */
function metafieldsCreate($shop, $requestParam = []) {
    $parameters['namespace'] = 'details';
    $parameters['key'] = $requestParam['key'];
    $parameters['value'] = $requestParam['value'];
    $parameters['value_type'] = 'string';
    $url = '/admin/' . $requestParam['what'] . '/' . $requestParam['resource_id'] . '/metafields.json';
    $metafield['metafield'] = $parameters;
    $shop->api()->rest('POST', $url, $metafield);
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
            if ($data->customer) {
                $customerName = $data->customer->first_name.' '.$data->customer->last_name;
            }

            return $customerName;
        case 2:
            return $data->order_number;
        case 3:
            $phone = '';
            if ($data->customer) {
                $phone = $data->customer->phone;
            }

            return $phone;
        case 4:
            $customerEmail = '';
            if ($data->customer) {
                $customerEmail = $data->customer->email;
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
            return $data->total_price;
        case 2:
            $totalQuantity = 0;
            foreach ($data->line_items as $item) {
                $totalQuantity += $item->quantity;
            }

            return $totalQuantity;
        case 1:
            return '';
    }
}

/**
 * @param $item
 * @param $productForExcise
 * @param $productIdentifierForExcise
 *
 * @return bool
 */
function filterRequest($item, $productForExcise, $productIdentifierForExcise)
{
    if ($productForExcise->option == 2) {
        $isExist = ProductInfo::where('alternate_product_code', $item['ProductCode'])->exists();
        if (!$isExist) {
            return false;
        }
    }
    return true;
}

