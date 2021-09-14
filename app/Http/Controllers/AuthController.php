<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Osiset\ShopifyApp\Actions\AuthenticateShop;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Traits\AuthController as AuthControllerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Osiset\ShopifyApp\Actions\AuthorizeShop;
use Osiset\ShopifyApp\Exceptions\SignatureVerificationException;



class AuthController extends Controller
{
    use AuthControllerTrait;

    /**
     * Authenticating a shop.
     *
     * @param AuthenticateShop $authenticateShop The action for authorizing and authenticating a shop.
     *
     * @throws SignatureVerificationException
     *
     * @return ViewView|RedirectResponse
     */
    public function authenticate(Request $request, AuthenticateShop $authenticateShop)
    {
        // Get the shop domain
        $shopDomain = ShopDomain::fromNative($request->get('shop'));

        $shop = User::where('name',$shopDomain->toNative())->first();
        if ($request->api_key && $request->api_secret) {
            Config::set('shopify-app.api_key', $request->api_key);
            Config::set('shopify-app.api_secret', $request->api_secret);
        }else if($shop && $shop->shopify_api_secret && $shop->shopify_api_key) {
            Config::set('shopify-app.api_key', $shop->shopify_api_key);
            Config::set('shopify-app.api_secret', $shop->shopify_api_secret);
        }

        // Run the action, returns [result object, result status]
        [$result, $status] = $authenticateShop($request);

        //save data
        if($shop && $request->api_key && $request->api_secret){
            $shop->shopify_api_key = $request->api_key;
            $shop->shopify_api_secret = $request->api_secret;
            $shop->save();
        }

        if ($status === null) {
            // Show exception, something is wrong
            throw new SignatureVerificationException('Invalid HMAC verification');
        } elseif ($status === false) {
            // No code, redirect to auth URL
            return $this->oauthFailure($result->url, $shopDomain);
        } else {
            // Everything's good... determine if we need to redirect back somewhere
            $return_to = Session::get('return_to');
            if ($return_to) {
                Session::forget('return_to');

                return Redirect::to($return_to);
            }

            // No return_to, go to home route
            return Redirect::route('home');
        }
    }
}
