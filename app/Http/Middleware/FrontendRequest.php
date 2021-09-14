<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class FrontendRequest
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
        $requestParams = $request->query();

        $hmac = $request->get('signature');
        if(!$hmac) {
            \Log::error('[Dev Info] Unauthorized Shop Request detected: '.json_encode($request->all()));
            abort(401, 'Unauthorized Shop Request');
        }
        unset($requestParams['signature']);
        ksort($requestParams);
        $requestParamsStr = '';
        foreach($requestParams as $key => $value) {
            $requestParamsStr .= $key.'='.$value;
        }
        $generatedHmac = hash_hmac('sha256', $requestParamsStr, config('shopify-app.api_secret'));
        if(!hash_equals($generatedHmac, $hmac)) {
            \Log::error('[Dev Info] Unable to verify requested hmac: '.json_encode($request->all()).' Generated hmac: ['.$generatedHmac.'] Request Hmac: ['.$hmac.']');
            abort(401, 'Unable to verify requested hmac');
        }

        $request->attributes->add(['shop' => \App\Models\User::where('name', $request->get('shop'))->first()]);

        return $next($request);
    }
}
