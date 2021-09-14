<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\LicensedJurisdictionsJob;
use App\Models\Setting\AvalaraCredential;
use App\Models\Setting\FailoverCheckout;
use App\Models\Setting\ProductForExcise;
use App\Models\Setting\ProductIdentifierForExcise;
use App\Models\Setting\StaticSetting;
use App\Models\User;
use App\Traits\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Exception;

class SettingController extends Controller
{
    /**
     * @param User $user
     */
    public function ShopDetail(User $user, Request $request) {
        $shopId = $request->user()->id;
        return response(user::where('id', $shopId)->select('shop_name', 'is_app_setup', 'currency_format', 'name')->first());
    }

    /**
     * @param Request $request
     * @param User $user
     * @return ResponseFactory|\Illuminate\Http\Response
     * @throws ValidationException
     */
    public function Steps(Request $request, User $user) {
        $shop = $user->all();
        $shopId = $request->user()->id;
        if ($request->isMethod("GET")) {
            switch ($request->step) {
                case 1:
                    return response(AvalaraCredential::where('shop_id', $shopId)->first());
                    break;
                case 3:
                    return response(ProductForExcise::where('shop_id', $shopId)->first());
                    break;
                case 4:
                    return response(ProductIdentifierForExcise::where('shop_id', $shopId)->first());
                    break;
                case 5:
                    $staticSettings = StaticSetting::where([['shop_id', $shopId], ['type', 1]])->get();
                        if (!empty($staticSettings)) {
                            $data = [];
                            foreach ($staticSettings as $staticSetting) {
                                $data[$staticSetting->field] = $staticSetting->value;
                            }
                            return response($data);
                        }
                    break;
                case 6:
                    return response(user::where('id', $shopId)->pluck('installed_app'));
                    break;
                case 'settings':
                    $data['avalaraCredential'] = AvalaraCredential::where('shop_id', $shopId)->first();
                    $data['productForExcise'] = ProductForExcise::where('shop_id', $shopId)->first();
                    $data['productIdentifierForExcise'] = ProductIdentifierForExcise::where('shop_id', $shopId)->first();
                    $data['failoverCheckout'] = FailoverCheckout::where('shop_id', $shopId)->get();
                    $staticSettings = StaticSetting::where([['shop_id', $shopId]])->get();
                    if (!empty($staticSettings)) {
                        $staticData = [];
                        foreach ($staticSettings as $staticSetting) {
                            $staticData[$staticSetting->field] = $staticSetting->value;
                        }
                        $data['staticSettings'] = $staticData;
                    }

                    return response($data);
                    break;
            }
        } else {
            switch ($request->step) {
                case 1:
                    $this->validate($request,
                        [
                            'username' => 'required',
                            'password' => 'required',
                            'company_id' => 'required',
                        ], [
                            'username.required' => 'Required field',
                            'password.required' => 'Required field',
                            'company_id.required' => 'Required field',
                        ]
                    );
                    AvalaraCredential::updateOrCreate([
                        'shop_id' => $shopId,
                    ], [
                        'shop_id' => $shopId,
                        'username' => $request->username,
                        'password' => $request->password,
                        'company_id' => $request->company_id,
                    ]);
                    break;
                case 3:
                    ProductForExcise::updateOrCreate([
                        'shop_id' => $shopId,
                    ], [
                        'shop_id' => $shopId,
                        'option' => $request->option,
                        'value' => $request->value,
                    ]);
                    break;
                case 4:
                    $this->validate($request,
                        [
                            'value' => 'required',
                        ], [
                            'value.required' => 'Required field',
                        ]
                    );
                    ProductIdentifierForExcise::updateOrCreate([
                        'shop_id' => $shopId,
                    ], [
                        'shop_id' => $shopId,
                        'identifier' => $request->identifier,
                        'option' => $request->option,
                        'value' => $request->value,
                        'confirm' => $request->confirm,
                    ]);
                    break;
                case 5:
                    $this->validate($request,
                        [
                            'unit_of_measure' => 'required',
                            'transportation_mode_code' => 'required',
                            'transaction_type' => 'required',
                            'title_transfer_code' => 'required',
                            'seller' => 'required',
                            'origin' => 'required',
                            'currency' => 'required',
                            'buyer' => 'required',
                        ], [
                            'unit_of_measure.required' => 'Required field',
                            'transportation_mode_code.required' => 'Required field',
                            'transaction_type.required' => 'Required field',
                            'title_transfer_code.required' => 'Required field',
                            'seller.required' => 'Required field',
                            'origin.required' => 'Required field',
                            'currency.required' => 'Required field',
                            'buyer.required' => 'Required field',
                        ]
                    );
                    foreach ($request->all() as $param => $val) {

                        // Set static currency value.
                        if ($param == 'currency') {
                            $val = 'USD';
                        }

                        if ($param != 'step') {
                            StaticSetting::updateOrCreate([
                                'shop_id' => $shopId,
                                'field' => $param,
                                'type' => 1,
                            ], [
                                'shop_id' => $shopId,
                                'field' => $param,
                                'value' => $val,
                                'type' => 1,
                            ]);
                        }
                    }
                    break;
                case 6:
                    $installedApp = [];
                    foreach ($request->apps as $app) {
                        foreach ($app as $name)
                            if (!empty($name))
                                $installedApp[] = $name;
                    }

                    if (!empty($installedApp)) {
                        $params = [];
                        $params['apps'] = implode(", ", $installedApp);
                        $params['store_name'] = $request->user()->name;
                        Mail::to(Helpers::devContactEmails())->send(new \App\Mail\InstalledApp($params));
                    }
                    User::where('id', $shopId)->update(['installed_app' => collect($installedApp)->implode(",")]);
                    break;
                case 'get_started':
                    User::where('id', $shopId)->update(['is_app_setup' => $request->is_app_setup]);
                    /*FailoverCheckout::firstOrCreate([
                        'shop_id' => $shopId,
                    ],[
                        'shop_id' => $shopId,
                        'action' => 1,
                        'identifier' => 1,
                        'failover_message' => json_encode(["1" => "The excise tax calculation might not be possible at the moment due to some technical reason. We shall, later on, share  you the payment link with excise tax.", "2" => "You can't place an order without paying excise tax.", "3" => "We are not authorized to ship some Excise product in this area."]),
                        'value' => "",
                    ]);*/

                    FailoverCheckout::firstOrCreate([
                        'shop_id' => $shopId,
                        'action' => 1,
                    ],[
                        'shop_id' => $shopId,
                        'action' => 1,
                        'identifier' => 1,
                        'message' => 'The excise tax calculation might not be possible at the moment due to some technical reason. We shall, later on, share  you the payment link with excise tax.',
                    ]);

                    FailoverCheckout::firstOrCreate([
                        'shop_id' => $shopId,
                        'action' => 2,
                    ],[
                        'shop_id' => $shopId,
                        'action' => 2,
                        'identifier' => 1,
                        'message' => 'We are not authorized to ship some Excise product in this area.',
                    ]);

                    //LicensedJurisdictionsJob::dispatch($shopId)->onQueue('default');
                    $this->saveConditionOnStore($shopId);
                    return response(["data" => "Setup completed"], 200);
                    break;
                case 'settings':
                    $this->saveAvalaraCredential($request, $shop);
                    break;
            }
        }
    }

    /**
     * @param $request
     * @param $shop
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function saveAvalaraCredential($request, $shop)
    {

        $shopId = $request->user()->id;
        $this->validate($request, [
            'avalaraCredential.username' => 'required',
            'avalaraCredential.password' => 'required',
            'avalaraCredential.company_id' => 'required',
            'staticSettings.unit_of_measure' => 'required',
            'staticSettings.transportation_mode_code' => 'required',
            'staticSettings.transaction_type' => 'required',
            'staticSettings.title_transfer_code' => 'required',
            'staticSettings.seller' => 'required',
            'staticSettings.origin' => 'required',
            'staticSettings.currency' => 'required',
            'staticSettings.buyer' => 'required',
            //'productIdentifierForExcise.value' => 'required',
        ], [
            'avalaraCredential.username.required' => 'Required field',
            'avalaraCredential.password.required' => 'Required field',
            'avalaraCredential.company_id.required' => 'Required field',
            'staticSettings.unit_of_measure.required' => 'Required field',
            'staticSettings.transportation_mode_code.required' => 'Required field',
            'staticSettings.transaction_type.required' => 'Required field',
            'staticSettings.title_transfer_code.required' => 'Required field',
            'staticSettings.seller.required' => 'Required field',
            'staticSettings.origin.required' => 'Required field',
            'staticSettings.currency.required' => 'Required field',
            'staticSettings.buyer.required' => 'Required field',
            //'productIdentifierForExcise.value.required' => 'Required field',
        ]);

//        if ($validator->fails()) {
//            throw new ValidationException($validator->errors()->first());
//        }

        $input = $request->all();

        AvalaraCredential::updateOrCreate([
            'shop_id' => $shopId,
        ], [
            'shop_id' => $shopId,
            'username' => $input['avalaraCredential']['username'],
            'password' => $input['avalaraCredential']['password'],
            'company_id' => $input['avalaraCredential']['company_id'],
        ]);

        foreach ($input['staticSettings'] as $param => $val) {
            $type = 1;

            // Set static currency value.
            if ($param == 'currency') {
                $val = 'USD';
            }

            if ($param != 'step') {
                if (stripos($param, 'order_custom_string') !== false || stripos($param, 'order_custom_numeric') !== false)
                    $type = 2;
                elseif (stripos($param, 'lineitem_custom_string') !== false || stripos($param, 'lineitem_custom_numeric') !== false)
                    $type = 3;

                if ($param == 'additionalStaticField') {
                    if (!empty($val)) {
                        StaticSetting::where('shop_id', $shopId)->where(function ($query) {
                            $query->where('field', 'LIKE', 'additional_custom_option%');
                            $query->orWhere('field', 'LIKE', 'additional_custom_value%');
                        })->delete();
                        foreach ($val as $k => $item) {
                            StaticSetting::create([
                                'shop_id' => $shopId,
                                'field' => "additional_custom_option".++$k,
                                'value' => $item['option'],
                                'type' => $type,
                            ]);
                            StaticSetting::create([
                                'shop_id' => $shopId,
                                'field' => "additional_custom_value".$k,
                                'value' => $item['value'],
                                'type' => $type,
                            ]);
                        }
                    } else {
                        StaticSetting::where('shop_id', $shopId)->where(function ($query) {
                            $query->where('field', 'LIKE', 'additional_custom_option%');
                            $query->orWhere('field', 'LIKE', 'additional_custom_value%');
                        })->delete();
                    }
                    continue;
                }

                StaticSetting::updateOrCreate([
                    'shop_id' => $shopId,
                    'field' => $param,
                    'type' => $type,
                ], [
                    'shop_id' => $shopId,
                    'field' => $param,
                    'value' => $val,
                    'type' => $type,
                ]);
            }
        }

        ProductForExcise::updateOrCreate([
            'shop_id' => $shopId,
        ], [
            'shop_id' => $shopId,
            'option' => $input['productForExcise']['option'],
            'value' => $input['productForExcise']['value'],
        ]);

        /*ProductIdentifierForExcise::updateOrCreate([
            'shop_id' => $shopId,
        ], [
            'shop_id' => $shopId,
            'identifier' => $input['productIdentifierForExcise']['identifier'],
            'option' => $input['productIdentifierForExcise']['option'],
            'value' => $input['productIdentifierForExcise']['value'],
        ]);*/

        if ($input['failoverCheckout']['actionSelect'] == 3 ) {
            $input['failoverCheckout']['actionSelect'] = 1;
        }

        FailoverCheckout::updateOrCreate([
            'shop_id' => $shopId,
            'action' => 1,
        ],[
            'shop_id' => $shopId,
            'action' => 1,//$input['failoverCheckout']['actionSelect'],
            'identifier' => $input['failoverCheckout']['actionIdentifier'],
            'message' => $input['failoverCheckout']['dueExciseNotification'],
            'tags' => json_encode($input['failoverCheckout']['dueExciseTags']),
            //'failover_message' => json_encode($input['failoverCheckout']['checkoutMessage']),
            //'value' => $input['failoverCheckout']['tagValue'],
        ]);

        FailoverCheckout::updateOrCreate([
            'shop_id' => $shopId,
            'action' => 2,
        ],[
            'shop_id' => $shopId,
            'action' => 2,
            'identifier' => $input['failoverCheckout']['actionIdentifier'],
            'message' => $input['failoverCheckout']['unauthorizeNotification'],
            'tags' => json_encode($input['failoverCheckout']['unauthorizeTags']),
        ]);

        $this->saveConditionOnStore($shopId);

        return true;
    }

    /**
     * @param Request $request
     *
     * @return Application|ResponseFactory|\Illuminate\Http\Response
     *
     * @throws GuzzleException
     */
    public function testConnection(Request $request)
    {
        $input = $request->all();
        $client = new Client();

        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $input['company_id'],
        ];
        $apiUsername = $input['username'];
        $apiUserPassword = $input['password'];

        try {
            $response = $client->get(env('AVALARA_API_ENDPOINT').'/ImportHeaders/GetByLoadDate', [
                'auth' => [
                    $apiUsername, $apiUserPassword
                ],
                'headers' => $headers,
            ]);

            return response('Credentials Tested', $response->getStatusCode());
        } catch (Exception $e) {
            return response('Credentials Tested', $e->getCode());
        }
    }

    /**
     * @param User $user
     *
     * @return Application|ResponseFactory|\Illuminate\Http\Response
     *
     * @throws GuzzleException
     */
    public function CheckConnection(Request $request)
    {
        $shopId = $request->user()->id;
        $avaCredential = AvalaraCredential::where('shop_id', $shopId)->first();

        $client = new Client();

        $headers = [
            'Accept' => 'application/json',
            'x-company-id' => $avaCredential->company_id,
        ];
        $apiUsername = $avaCredential->username;
        $apiUserPassword = $avaCredential->password;

        try {
            $response = $client->get('https://excisesbx.avalara.com/api/v1/ImportHeaders/GetByLoadDate', [
                'auth' => [
                    $apiUsername, $apiUserPassword
                ],
                'headers' => $headers,
            ]);

            return response('Credentials Tested', $response->getStatusCode());
        } catch (Exception $e) {
            return response('Credentials Tested', $e->getCode());
        }
    }

    /**
     * @param $shopId
     */
    public function saveConditionOnStore($shopId) {
        $shop = User::where('id', $shopId)->first();
        $failovers = FailoverCheckout::where('shop_id', $shopId)->get();
        $metafieldObj = [];
        foreach ($failovers as $key => $failover) {
            switch ($failover->action) {
                case 1:
                    $metafieldObj['place_order'] = $failover->message;
                    break;
                case 2:
                    $metafieldObj['unauthorized'] = $failover->message;
                    break;
            }
        }
        $metafieldObj['selected_option'] = 'place_order';

        $parameters['namespace'] = "ava_failover_setting";
        $parameters['key'] = "ava_failover_setting";
        $parameters['value'] = json_encode($metafieldObj);
        $parameters['value_type'] = 'json_string';
        $url = '/admin/metafields.json';
        $metafield['metafield'] = $parameters;
        $shop->api()->rest('POST', $url, $metafield);
    }
}
