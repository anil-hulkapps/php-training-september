<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use function Osiset\ShopifyApp\createHmac;

class AuthWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $hmac = $request->header('x-shopify-hmac-sha256') ?: '';
        $shop = $request->header('x-shopify-shop-domain');
        $data = $request->getContent();
        $shopEloquent = User::where('name', $shop)->whereNotNull('password')->first();
        if (!$shopEloquent) {
            return Response::make('Invalid webhook signature', 401);
        }
        if (isset($shopEloquent->shopify_api_secret) && $shopEloquent->shopify_api_secret) {
            Config::set('shopify-app.api_key', $shopEloquent->shopify_api_key);
            Config::set('shopify-app.api_secret', $shopEloquent->shopify_api_secret);
        }

        $hmacLocal = createHmac(['data' => $data, 'raw' => true, 'encode' => true], config('shopify-app.api_secret'));


        if (!hash_equals($hmac, $hmacLocal) || empty($shop)) {
            // Issue with HMAC or missing shop header
            return Response::make('Invalid webhook signature.', 401);
        }

        // All good, process webhook
        return $next($request);
    }
}
