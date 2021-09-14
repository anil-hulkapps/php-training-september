<?php

namespace App\Http\Controllers;

use App\Exports\StateZipCodeExport;
use App\Jobs\FulfillmentsCreateJob;
use App\Jobs\OrdersCancelledJob;
use App\Jobs\RefundsCreateJob;
use App\Models\State;
use App\Models\StateZipcode;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Excel;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Storage;
use App\Exports\DownloadSampleFile;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {

    }

    /**
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function testOrderCancelled(Request $request)
    {
        $orderData = '
       {
"id": 3837231890601,
"admin_graphql_api_id": "gid://shopify/Order/3837231890601",
"app_id": 580111,
"browser_ip": "172.58.229.255",
"buyer_accepts_marketing": true,
"cancel_reason": "inventory",
"cancelled_at": "2021-05-25T17:53:28-07:00",
"cart_token": "b2dbd22453cbee5a2d8e7fbfc5dd5f39",
"checkout_id": 20264869396649,
"checkout_token": "d50427a881fe52010e24f899ee6c2ee6",
"client_details": {
"accept_language": "en-US,en;q=0.9,ru;q=0.8,uk;q=0.7",
"browser_height": 718,
"browser_ip": "172.58.229.255",
"browser_width": 412,
"session_hash": null,
"user_agent": "Mozilla/5.0 (Linux; Android 9; SM-G955U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.210 Mobile Safari/537.36"
},
"closed_at": null,
"confirmed": true,
"contact_email": "hudzovatyy@gmail.com",
"created_at": "2021-05-25T05:35:35-07:00",
"currency": "USD",
"current_subtotal_price": "0.00",
"current_subtotal_price_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"current_total_discounts": "0.00",
"current_total_discounts_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"current_total_duties_set": null,
"current_total_price": "0.00",
"current_total_price_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"current_total_tax": "0.00",
"current_total_tax_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"customer_locale": "en",
"device_id": null,
"discount_codes": [],
"email": "hudzovatyy@gmail.com",
"financial_status": "refunded",
"fulfillment_status": null,
"gateway": "authorize_net",
"landing_site": "/products/brain-freeze-naked-100-e-juice-60-ml",
"landing_site_ref": null,
"location_id": null,
"name": "#622535",
"note": null,
"note_attributes": [
{
"name": "transaction_id",
"value": "34034260"
},
{
"name": "transaction_code",
"value": "1621946025704"
},
{
"name": "birthMonth",
"value": "10"
},
{
"name": "birthDay",
"value": "26"
},
{
"name": "birthYear",
"value": "1970"
},
{
"name": "birthdate",
"value": "Mon Oct 26 1970"
},
{
"name": "age",
"value": "50"
},
{
"name": "javascriptEnabled",
"value": "true"
},
{
"name": "userAgent",
"value": "Mozilla/5.0 (Linux; Android 9; SM-G955U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.210 Mobile Safari/537.36"
}
],
"number": 621535,
"order_number": 622535,
"order_status_url": "https://www.vaporauthority.com/1879482/orders/b5082fbf28595e75528d6b3a48d009dd/authenticate?key=4e8a719f8ee1e2908d7f1a075acb1c56",
"original_total_duties_set": null,
"payment_gateway_names": [
"authorize_net"
],
"phone": null,
"presentment_currency": "USD",
"processed_at": "2021-05-25T05:35:33-07:00",
"processing_method": "direct",
"reference": null,
"referring_site": "https://www.google.com/",
"source_identifier": null,
"source_name": "web",
"source_url": null,
"subtotal_price": "40.96",
"subtotal_price_set": {
"shop_money": {
"amount": "40.96",
"currency_code": "USD"
},
"presentment_money": {
"amount": "40.96",
"currency_code": "USD"
}
},
"tags": "Riskified::approved, Riskified::submitted",
"tax_lines": [],
"taxes_included": false,
"test": false,
"token": "b5082fbf28595e75528d6b3a48d009dd",
"total_discounts": "10000.00",
"total_discounts_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"total_line_items_price": "10040.96",
"total_line_items_price_set": {
"shop_money": {
"amount": "10040.96",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10040.96",
"currency_code": "USD"
}
},
"total_outstanding": "0.00",
"total_price": "50.94",
"total_price_set": {
"shop_money": {
"amount": "50.94",
"currency_code": "USD"
},
"presentment_money": {
"amount": "50.94",
"currency_code": "USD"
}
},
"total_price_usd": "50.94",
"total_shipping_price_set": {
"shop_money": {
"amount": "9.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "9.98",
"currency_code": "USD"
}
},
"total_tax": "0.00",
"total_tax_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"total_tip_received": "0.00",
"total_weight": 283,
"updated_at": "2021-05-25T17:53:28-07:00",
"user_id": null,
"billing_address": {
"first_name": "Iryna",
"address1": "1812 East 18street, apt.B2",
"phone": "6462515785",
"city": "Brooklyn",
"zip": "11229",
"province": "New York",
"country": "United States",
"last_name": "Polyak",
"address2": "",
"company": "",
"latitude": 40.6054414,
"longitude": -73.95499389999999,
"name": "Iryna Polyak",
"country_code": "US",
"province_code": "NY"
},
"customer": {
"id": 4723230703785,
"email": "hudzovatyy@gmail.com",
"accepts_marketing": true,
"created_at": "2021-02-10T16:12:43-08:00",
"updated_at": "2021-05-25T17:53:32-07:00",
"first_name": "Yaroslav",
"last_name": "Hudzovatyy",
"orders_count": 2,
"state": "disabled",
"total_spent": "0.00",
"last_order_id": 3837231890601,
"note": null,
"verified_email": true,
"multipass_identifier": null,
"tax_exempt": false,
"phone": null,
"tags": "",
"last_order_name": "#622535",
"currency": "USD",
"accepts_marketing_updated_at": "2021-02-10T16:12:43-08:00",
"marketing_opt_in_level": "single_opt_in",
"tax_exemptions": [],
"admin_graphql_api_id": "gid://shopify/Customer/4723230703785",
"default_address": {
"id": 6450160173225,
"customer_id": 4723230703785,
"first_name": "Iryna",
"last_name": "Polyak",
"company": "",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"province": "New York",
"country": "United States",
"zip": "11229",
"phone": "6462515785",
"name": "Iryna Polyak",
"province_code": "NY",
"country_code": "US",
"country_name": "United States",
"default": true
}
},
"discount_applications": [
{
"target_type": "line_item",
"type": "script",
"value": "10000.0",
"value_type": "fixed_amount",
"allocation_method": "one",
"target_selection": "explicit",
"title": "",
"description": ""
}
],
"fulfillments": [],
"line_items": [
{
"id": 9989757173929,
"admin_graphql_api_id": "gid://shopify/LineItem/9989757173929",
"destination_location": {
"id": 2945840414889,
"country_code": "US",
"province_code": "NY",
"name": "Iryna Polyak",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"zip": "11229"
},
"fulfillable_quantity": 0,
"fulfillment_service": "manual",
"fulfillment_status": null,
"gift_card": false,
"grams": 0,
"name": "Avalara excise tax",
"origin_location": {
"id": 1588470317127,
"country_code": "US",
"province_code": "CA",
"name": "Vapor Authority",
"address1": "4897 Mercury St",
"address2": "",
"city": "San Diego",
"zip": "92123"
},
"pre_tax_price": "0.00",
"pre_tax_price_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"price": "10000.00",
"price_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"product_exists": true,
"product_id": 6630282133673,
"properties": [
{
"name": "excise_tax",
"value": "0"
}
],
"quantity": 1,
"requires_shipping": false,
"sku": "",
"taxable": false,
"title": "Avalara excise tax",
"total_discount": "10000.00",
"total_discount_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"variant_id": 39522449719465,
"variant_inventory_management": null,
"variant_title": "",
"vendor": "Vapor Authority",
"tax_lines": [],
"duties": [],
"discount_allocations": [
{
"amount": "10000.00",
"amount_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"discount_application_index": 0
}
]
},
{
"id": 9989757206697,
"admin_graphql_api_id": "gid://shopify/LineItem/9989757206697",
"destination_location": {
"id": 2945840414889,
"country_code": "US",
"province_code": "NY",
"name": "Iryna Polyak",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"zip": "11229"
},
"fulfillable_quantity": 0,
"fulfillment_service": "manual",
"fulfillment_status": null,
"gift_card": false,
"grams": 142,
"name": "Strawberry Pom (Brain Freeze) - Naked 100 E-Juice (60 ml) - 60 ml / 3 mg",
"origin_location": {
"id": 1588470317127,
"country_code": "US",
"province_code": "CA",
"name": "Vapor Authority",
"address1": "4897 Mercury St",
"address2": "",
"city": "San Diego",
"zip": "92123"
},
"pre_tax_price": "39.98",
"pre_tax_price_set": {
"shop_money": {
"amount": "39.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "39.98",
"currency_code": "USD"
}
},
"price": "19.99",
"price_set": {
"shop_money": {
"amount": "19.99",
"currency_code": "USD"
},
"presentment_money": {
"amount": "19.99",
"currency_code": "USD"
}
},
"product_exists": true,
"product_id": 7504891461,
"properties": [],
"quantity": 2,
"requires_shipping": true,
"sku": "LQDS-NKD-0765-60ML-3MG",
"taxable": true,
"title": "Strawberry Pom (Brain Freeze) - Naked 100 E-Juice (60 ml)",
"total_discount": "0.00",
"total_discount_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"variant_id": 27763931909,
"variant_inventory_management": "shopify",
"variant_title": "60 ml / 3 mg",
"vendor": "Naked 100",
"tax_lines": [],
"duties": [],
"discount_allocations": []
},
{
"id": 9989757239465,
"admin_graphql_api_id": "gid://shopify/LineItem/9989757239465",
"destination_location": {
"id": 2945840414889,
"country_code": "US",
"province_code": "NY",
"name": "Iryna Polyak",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"zip": "11229"
},
"fulfillable_quantity": 0,
"fulfillment_service": "manual",
"fulfillment_status": null,
"gift_card": false,
"grams": 0,
"name": "Route Package Protection - $0.98",
"origin_location": {
"id": 1588470317127,
"country_code": "US",
"province_code": "CA",
"name": "Vapor Authority",
"address1": "4897 Mercury St",
"address2": "",
"city": "San Diego",
"zip": "92123"
},
"pre_tax_price": "0.98",
"pre_tax_price_set": {
"shop_money": {
"amount": "0.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.98",
"currency_code": "USD"
}
},
"price": "0.98",
"price_set": {
"shop_money": {
"amount": "0.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.98",
"currency_code": "USD"
}
},
"product_exists": true,
"product_id": 4551408582727,
"properties": [],
"quantity": 1,
"requires_shipping": false,
"sku": "NSRN-RT-2507-$0.98",
"taxable": false,
"title": "Route Package Protection",
"total_discount": "0.00",
"total_discount_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"variant_id": 31970996977735,
"variant_inventory_management": null,
"variant_title": "$0.98",
"vendor": "Route",
"tax_lines": [],
"duties": [],
"discount_allocations": []
}
],
"payment_details": {
"credit_card_bin": "414720",
"avs_result_code": "Y",
"cvv_result_code": "M",
"credit_card_number": "•••• •••• •••• 5534",
"credit_card_company": "Visa"
},
"refunds": [
{
"id": 806945423529,
"admin_graphql_api_id": "gid://shopify/Refund/806945423529",
"created_at": "2021-05-25T17:53:27-07:00",
"note": null,
"order_id": 3837231890601,
"processed_at": "2021-05-25T17:53:27-07:00",
"restock": true,
"user_id": 5689073,
"order_adjustments": [
{
"id": 176906535081,
"amount": "-9.98",
"amount_set": {
"shop_money": {
"amount": "-9.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "-9.98",
"currency_code": "USD"
}
},
"kind": "shipping_refund",
"order_id": 3837231890601,
"reason": "Shipping refund",
"refund_id": 806945423529,
"tax_amount": "0.00",
"tax_amount_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
}
}
],
"transactions": [
{
"id": 4816048128169,
"admin_graphql_api_id": "gid://shopify/OrderTransaction/4816048128169",
"amount": "50.94",
"authorization": "63056585545#5534#void",
"created_at": "2021-05-25T17:53:25-07:00",
"currency": "USD",
"device_id": null,
"error_code": null,
"gateway": "authorize_net",
"kind": "refund",
"location_id": null,
"message": "This transaction has been approved",
"order_id": 3837231890601,
"parent_id": 4814490042537,
"processed_at": "2021-05-25T17:53:25-07:00",
"receipt": {
"action": "void",
"response_code": 1,
"response_reason_code": "1",
"response_reason_text": "This transaction has been approved",
"avs_result_code": "P",
"transaction_id": "63056585545",
"card_code": null,
"authorization_code": "06004C",
"cardholder_authentication_code": null,
"account_number": "5534",
"test_request": "0",
"full_response_code": "I00001"
},
"source_name": "1830279",
"status": "success",
"test": false,
"user_id": null
}
],
"refund_line_items": [
{
"id": 297859678377,
"line_item_id": 9989757173929,
"location_id": 11367493,
"quantity": 1,
"restock_type": "cancel",
"subtotal": 0,
"subtotal_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"total_tax": 0,
"total_tax_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"line_item": {
"id": 9989757173929,
"admin_graphql_api_id": "gid://shopify/LineItem/9989757173929",
"destination_location": {
"id": 2945840414889,
"country_code": "US",
"province_code": "NY",
"name": "Iryna Polyak",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"zip": "11229"
},
"fulfillable_quantity": 0,
"fulfillment_service": "manual",
"fulfillment_status": null,
"gift_card": false,
"grams": 0,
"name": "Avalara excise tax",
"origin_location": {
"id": 1588470317127,
"country_code": "US",
"province_code": "CA",
"name": "Vapor Authority",
"address1": "4897 Mercury St",
"address2": "",
"city": "San Diego",
"zip": "92123"
},
"pre_tax_price": "0.00",
"pre_tax_price_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"price": "10000.00",
"price_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"product_exists": true,
"product_id": 6630282133673,
"properties": [
{
"name": "excise_tax",
"value": "0"
}
],
"quantity": 1,
"requires_shipping": false,
"sku": "",
"taxable": false,
"title": "Avalara excise tax",
"total_discount": "10000.00",
"total_discount_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"variant_id": 39522449719465,
"variant_inventory_management": null,
"variant_title": "",
"vendor": "Vapor Authority",
"tax_lines": [],
"duties": [],
"discount_allocations": [
{
"amount": "10000.00",
"amount_set": {
"shop_money": {
"amount": "10000.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "10000.00",
"currency_code": "USD"
}
},
"discount_application_index": 0
}
]
}
},
{
"id": 297859711145,
"line_item_id": 9989757206697,
"location_id": 11367493,
"quantity": 2,
"restock_type": "cancel",
"subtotal": 39.98,
"subtotal_set": {
"shop_money": {
"amount": "39.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "39.98",
"currency_code": "USD"
}
},
"total_tax": 0,
"total_tax_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"line_item": {
"id": 9989757206697,
"admin_graphql_api_id": "gid://shopify/LineItem/9989757206697",
"destination_location": {
"id": 2945840414889,
"country_code": "US",
"province_code": "NY",
"name": "Iryna Polyak",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"zip": "11229"
},
"fulfillable_quantity": 0,
"fulfillment_service": "manual",
"fulfillment_status": null,
"gift_card": false,
"grams": 142,
"name": "Strawberry Pom (Brain Freeze) - Naked 100 E-Juice (60 ml) - 60 ml / 3 mg",
"origin_location": {
"id": 1588470317127,
"country_code": "US",
"province_code": "CA",
"name": "Vapor Authority",
"address1": "4897 Mercury St",
"address2": "",
"city": "San Diego",
"zip": "92123"
},
"pre_tax_price": "39.98",
"pre_tax_price_set": {
"shop_money": {
"amount": "39.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "39.98",
"currency_code": "USD"
}
},
"price": "19.99",
"price_set": {
"shop_money": {
"amount": "19.99",
"currency_code": "USD"
},
"presentment_money": {
"amount": "19.99",
"currency_code": "USD"
}
},
"product_exists": true,
"product_id": 7504891461,
"properties": [],
"quantity": 2,
"requires_shipping": true,
"sku": "LQDS-NKD-0765-60ML-3MG",
"taxable": true,
"title": "Strawberry Pom (Brain Freeze) - Naked 100 E-Juice (60 ml)",
"total_discount": "0.00",
"total_discount_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"variant_id": 27763931909,
"variant_inventory_management": "shopify",
"variant_title": "60 ml / 3 mg",
"vendor": "Naked 100",
"tax_lines": [],
"duties": [],
"discount_allocations": []
}
},
{
"id": 297859743913,
"line_item_id": 9989757239465,
"location_id": 11367493,
"quantity": 1,
"restock_type": "cancel",
"subtotal": 0.98,
"subtotal_set": {
"shop_money": {
"amount": "0.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.98",
"currency_code": "USD"
}
},
"total_tax": 0,
"total_tax_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"line_item": {
"id": 9989757239465,
"admin_graphql_api_id": "gid://shopify/LineItem/9989757239465",
"destination_location": {
"id": 2945840414889,
"country_code": "US",
"province_code": "NY",
"name": "Iryna Polyak",
"address1": "1812 East 18street, apt.B2",
"address2": "",
"city": "Brooklyn",
"zip": "11229"
},
"fulfillable_quantity": 0,
"fulfillment_service": "manual",
"fulfillment_status": null,
"gift_card": false,
"grams": 0,
"name": "Route Package Protection - $0.98",
"origin_location": {
"id": 1588470317127,
"country_code": "US",
"province_code": "CA",
"name": "Vapor Authority",
"address1": "4897 Mercury St",
"address2": "",
"city": "San Diego",
"zip": "92123"
},
"pre_tax_price": "0.98",
"pre_tax_price_set": {
"shop_money": {
"amount": "0.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.98",
"currency_code": "USD"
}
},
"price": "0.98",
"price_set": {
"shop_money": {
"amount": "0.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.98",
"currency_code": "USD"
}
},
"product_exists": true,
"product_id": 4551408582727,
"properties": [],
"quantity": 1,
"requires_shipping": false,
"sku": "NSRN-RT-2507-$0.98",
"taxable": false,
"title": "Route Package Protection",
"total_discount": "0.00",
"total_discount_set": {
"shop_money": {
"amount": "0.00",
"currency_code": "USD"
},
"presentment_money": {
"amount": "0.00",
"currency_code": "USD"
}
},
"variant_id": 31970996977735,
"variant_inventory_management": null,
"variant_title": "$0.98",
"vendor": "Route",
"tax_lines": [],
"duties": [],
"discount_allocations": []
}
}
],
"duties": []
}
],
"shipping_address": {
"first_name": "Iryna",
"address1": "1812 East 18street, apt.B2",
"phone": "6462515785",
"city": "Brooklyn",
"zip": "11229",
"province": "New York",
"country": "United States",
"last_name": "Polyak",
"address2": "",
"company": "",
"latitude": 40.6054414,
"longitude": -73.95499389999999,
"name": "Iryna Polyak",
"country_code": "US",
"province_code": "NY"
},
"shipping_lines": [
{
"id": 3264441614505,
"carrier_identifier": null,
"code": "Expedited Priority Shipping Adult Signature Cost-21",
"delivery_category": null,
"discounted_price": "9.98",
"discounted_price_set": {
"shop_money": {
"amount": "9.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "9.98",
"currency_code": "USD"
}
},
"phone": null,
"price": "9.98",
"price_set": {
"shop_money": {
"amount": "9.98",
"currency_code": "USD"
},
"presentment_money": {
"amount": "9.98",
"currency_code": "USD"
}
},
"requested_fulfillment_service_id": null,
"source": "shopify",
"title": "Expedited Priority Shipping Adult Signature Cost-21",
"tax_lines": [],
"discount_allocations": []
}
]
}';

        OrdersCancelledJob::dispatch('satish-practice.myshopify.com', $orderData);
    }

    public function testRefundCreate()
    {
        $refundCreate = '
        {
          "id": 811293212775,
          "order_id": 3859935821927,
          "created_at": "2021-06-09T06:39:29-04:00",
          "note": "",
          "user_id": 37385306215,
          "processed_at": "2021-06-09T06:39:29-04:00",
          "restock": false,
          "duties": [],
          "total_duties_set": {
            "shop_money": {
              "amount": "0.00",
              "currency_code": "INR"
            },
            "presentment_money": {
              "amount": "0.00",
              "currency_code": "INR"
            }
          },
          "admin_graphql_api_id": "gid://shopify/Refund/811293212775",
          "refund_line_items": [],
          "transactions": [
            {
              "id": 4857155190887,
              "order_id": 3859935821927,
              "kind": "refund",
              "gateway": "manual",
              "status": "success",
              "message": "Refunded 1.00 from manual gateway",
              "created_at": "2021-06-09T06:39:29-04:00",
              "test": false,
              "authorization": null,
              "location_id": null,
              "user_id": 37385306215,
              "parent_id": 4857155027047,
              "processed_at": "2021-06-09T06:39:29-04:00",
              "device_id": null,
              "error_code": null,
              "source_name": "1830279",
              "receipt": {},
              "amount": "1.00",
              "currency": "INR",
              "admin_graphql_api_id": "gid://shopify/OrderTransaction/4857155190887"
            }
          ],
          "order_adjustments": [
            {
              "id": 177085972583,
              "order_id": 3859935821927,
              "refund_id": 811293212775,
              "amount": "-1.00",
              "tax_amount": "0.00",
              "kind": "refund_discrepancy",
              "reason": "Refund discrepancy",
              "amount_set": {
                "shop_money": {
                  "amount": "-1.00",
                  "currency_code": "INR"
                },
                "presentment_money": {
                  "amount": "-1.00",
                  "currency_code": "INR"
                }
              },
              "tax_amount_set": {
                "shop_money": {
                  "amount": "0.00",
                  "currency_code": "INR"
                },
                "presentment_money": {
                  "amount": "0.00",
                  "currency_code": "INR"
                }
              }
            }
          ]
        }';

        RefundsCreateJob::dispatch('satish-practice.myshopify.com', $refundCreate);
    }

    public function testFulfillmentCreate()
    {
        $fulfillmentCreate = '
        {
          "id": 3394607448167,
          "order_id": 3822143373415,
          "status": "success",
          "created_at": "2021-05-20T07:55:27-04:00",
          "service": "manual",
          "updated_at": "2021-05-20T07:55:27-04:00",
          "tracking_company": null,
          "shipment_status": null,
          "location_id": 17358585959,
          "email": "",
          "destination": null,
          "line_items": [
            {
              "id": 9958375522407,
              "variant_id": 20920339923047,
              "title": "#Future is Feminine Tank",
              "quantity": 1,
              "sku": "TNS-LGY-IWDS",
              "variant_title": "S / cloud",
              "vendor": "satish Practice",
              "fulfillment_service": "manual",
              "product_id": 2268338389095,
              "requires_shipping": true,
              "taxable": true,
              "gift_card": false,
              "name": "#Future is Feminine Tank - S / cloud",
              "variant_inventory_management": "shopify",
              "properties": [],
              "product_exists": true,
              "fulfillable_quantity": 0,
              "grams": 0,
              "price": "58.00",
              "total_discount": "0.00",
              "fulfillment_status": "fulfilled",
              "price_set": {
                "shop_money": {
                  "amount": "58.00",
                  "currency_code": "INR"
                },
                "presentment_money": {
                  "amount": "58.00",
                  "currency_code": "INR"
                }
              },
              "total_discount_set": {
                "shop_money": {
                  "amount": "0.00",
                  "currency_code": "INR"
                },
                "presentment_money": {
                  "amount": "0.00",
                  "currency_code": "INR"
                }
              },
              "discount_allocations": [],
              "duties": [],
              "admin_graphql_api_id": "gid://shopify/LineItem/9958375522407",
              "tax_lines": [
                {
                  "title": "IGST",
                  "price": "10.44",
                  "rate": 0.18,
                  "channel_liable": null,
                  "price_set": {
                    "shop_money": {
                      "amount": "10.44",
                      "currency_code": "INR"
                    },
                    "presentment_money": {
                      "amount": "10.44",
                      "currency_code": "INR"
                    }
                  }
                }
              ]
            }
          ],
          "tracking_number": null,
          "tracking_numbers": [],
          "tracking_url": null,
          "tracking_urls": [],
          "receipt": {},
          "name": "#3989.2",
          "admin_graphql_api_id": "gid://shopify/Fulfillment/3394607448167"
        }';

        FulfillmentsCreateJob::dispatch('satish-practice.myshopify.com', $fulfillmentCreate);
    }
}
