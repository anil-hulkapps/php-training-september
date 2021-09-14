<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class ShopAPIRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->get('shop')) {
            abort(404, 'Shopify store details not found!');
        }
        $shop = User::where('name', $request->get('shop'))->whereNotNull('password')->first();
        if(!$shop) {
            abort(404, 'Shopify store details not found!');
        }

        $request->shop = $shop;
        return $next($request);
    }
}
