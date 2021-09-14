<?php

use App\Models\ProductInfo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

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
 * Create a metafields.
 */
function metafieldsUpdate($shop, $requestParam = []) {
    $parameters['namespace'] = 'details';
    $parameters['key'] = $requestParam['key'];
    $parameters['value'] = $requestParam['value'];
    $parameters['value_type'] = 'string';
    $url = '/admin/metafields/'.$requestParam['resource_id'].'.json';
    $metafield['metafield'] = $parameters;
    $shop->api()->rest('PUT', $url, $metafield);
}

/**
 * @param string $shopDomain
 * @param integer $variantId
 *
 * @return float|null
 */
function getVariant($shopDomain, $variantId) {
    $shop = \App\Models\User::where('name', $shopDomain)->first();

    $condition = '{
          productVariant(id: "gid://shopify/ProductVariant/'.$variantId.'") {
            inventoryItem {
              unitCost {
                amount
              }
            }
          }
        }';
    $variant = $shop->api()->graph($condition);
    if (!$variant['errors']) {
        return @count($variant['body']['productVariant']) > 0 ? ($variant['body']['productVariant']['inventoryItem']['unitCost'] ? (float) $variant['body']['productVariant']['inventoryItem']['unitCost']['amount'] : null) : null;
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

function getOrderFulfillmentStatus($status) {
    switch ($status) {
        case 'fulfilled':
            return 1;
        case null:
            return 2;
        case 'partial':
            return 3;
        case 'restocked':
            return 4;
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

    /*switch ($productIdentifierForExcise->identifier) {
        case 1:
            return checkTagPattern($productIdentifierForExcise->option, $item['tags'], $productIdentifierForExcise->value);
        case 2:
            return checkString($productIdentifierForExcise->option, $item['itemSKU'], $productIdentifierForExcise->value);
    }*/
    return true;
}

/**
 * @param $type
 * @param $data
 * @param $ref
 *
 * @return bool
 */
function checkString($type, $data, $ref) {
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
 * @param array $data
 * @param array $itemTags
 *
 * @return false
 */
function checkProductForExcise($data, $itemTags)
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
 *
 * @return bool
 */
function checkTagPattern($type, $itemTags, $ref)
{
    $tags = [];
    if (!empty($itemTags)) {
        $tags = array_map('trim', explode(',', $itemTags));
    }
    foreach ($tags as $tag) {
        if (!checkString($type, $tag, $ref)) {
            return false;
        }
    }

    return true;
}
