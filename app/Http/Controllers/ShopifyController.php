<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    public function validateDomainAccess(Request $request)
    {
        return response([], 201);
    }

    public function getApiKey(Request $request)
    {
        $request->validate([
            'shop' => 'required',
        ]);
        $shop = User::where('name', $request->get('shop'))->first();
        $response = [];
        if(isset($shop->shopify_api_key) && $shop->shopify_api_key) {
            $response['apiKey'] =  $shop->shopify_api_key;
        }else{
            $response['apiKey'] =  config('shopify-app.api_key');
        }
        return response($response, 200);
    }

    public function webhooks(Request $request){
        $shop = $request->user();
        $response = $shop->api()->rest('GET', '/admin/api/'.config('shopify-app.api_version').'/webhooks.json');
        dd($response);
    }
}
