<?php

namespace App\Console\Commands;

use App\Jobs\LicensedJurisdictionsJob;
use App\Jobs\OrdersCreateJob;
use App\Jobs\OrdersUpdateJob;
use App\Jobs\SyncProducts;
use App\Mail\ExciseProductDelete;
use App\Models\ProductInfo;
use App\Models\ScheduelTask;
use App\Models\Setting\AvalaraCredential;
use App\Models\Setting\FailoverCheckout;
use App\Models\Setting\StaticSetting;
use App\Models\User;
use App\Traits\Helpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:function';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $shop = User::find(1);
        /*Log::info("testing");
        OrdersUpdateJob::dispatch("avalara-excise.myshopify.com", "3886373077179");*/

        //$this->orderEdit($shop);
        //$this->createOrder();
        //$this->testFun();
        $this->checkAuthState();

        /*$headers = [
            'Accept' => 'application/json',
            'x-company-id' => 1425,
        ];
        $http = Http::timeout(20)->withHeaders($headers);
        $http->withBasicAuth("satish", "Wzzk6m6am8zt");
        $response = $http->get('https://excisesbx.avalara.com/api/v1/ImportHeaders/GetByLoadDate');
        dd(json_decode($response->body()));*/
    }

    public function checkAuthState() {
        LicensedJurisdictionsJob::dispatch(1);
    }

    public function testFun() {
        Log::info(Helpers::additionalField(1));
        $additionalStaticField = Helpers::additionalField(1);
        $requestDataAdjust = [
            'PreviousSeller' => isset($additionalStaticField['previous_seller']) ? $additionalStaticField['previous_seller'] : '',
            'NextBuyer' => isset($additionalStaticField['next_buyer']) ? $additionalStaticField['next_buyer'] : '',
            'Middleman' => isset($additionalStaticField['middleman']) ? $additionalStaticField['middleman'] : '',
            'FuelUseCode' => isset($additionalStaticField['fuel_use_code']) ? $additionalStaticField['fuel_use_code'] : '',
        ];
        dd($requestDataAdjust);
        /*$staticSettings = StaticSetting::where('shop_id', 1)->get();

        $return = [];
        for ($i=1; $i <= 20; $i++) {
            $hasTitleTransferCode = $staticSettings->where('field', 'additional_custom_option'.$i)->first();
            if ($hasTitleTransferCode) {
                $hasTitleTransferValue = $staticSettings->where('field', 'additional_custom_value'.$i)->first();
                if ($hasTitleTransferValue)
                    $return[Helpers::additionalStaticField($hasTitleTransferCode->value)] = $hasTitleTransferValue->value;
            }

            Log::info($return);
        }
        dd($hasTitleTransferCode);*/
        //$titleTransferCode = $hasTitleTransferCode ? $hasTitleTransferCode->value : 'DEST';
    }

    /**
     * @param $shop
     */
    public function orderEdit($shop) {
        $beginEditQuery = 'mutation beginEdit{
            orderEditBegin(id: "gid://shopify/Order/3901537615975"){
                calculatedOrder{
                    id
                }
            }
        }';
        $beginEditRequest = $shop->api()->graph($beginEditQuery);
        $calculateOrder = $beginEditRequest['body']['data']['orderEditBegin']['calculatedOrder'];
        $calculateOrderId = $calculateOrder['id'];
        $this->addCustomItemToOrder($shop, $calculateOrderId);
    }

    /**
     * @param $shop
     * @param $calculateOrderId
     */
    public function addCustomItemToOrder($shop, $calculateOrderId) {
        $addCustomItemToOrderQuery = 'mutation addCustomItemToOrder {
            orderEditAddCustomItem(id: "'.$calculateOrderId.'", title: "Excise Tax", quantity: 1, price: {amount: 40.00, currencyCode: INR}) {
                calculatedOrder {
                    id
                    addedLineItems(first: 5) {
                        edges {
                            node {
                                id
                            }
                        }
                    }
                }
                userErrors {
                    field
                    message
                }
            }
        }';
        $addCustomItemToOrderRequest = $shop->api()->graph($addCustomItemToOrderQuery);
        $customLineItem = $addCustomItemToOrderRequest['body']['data']['orderEditAddCustomItem']['calculatedOrder'];
        $addedLineItems = $customLineItem['addedLineItems']['edges'];
        $isAddItem = false;
        foreach ($addedLineItems as $addedLineItem) {
            if($addedLineItem['node']['id']) {
                $isAddItem = true;
            }
        }
        if($isAddItem) {
            $this->orderEditCommit($shop, $calculateOrderId);
        }
    }

    /**
     * @param $shop
     * @param $calculateOrderId
     */
    public function orderEditCommit($shop, $calculateOrderId) {
        $orderEditCommitQuery = 'mutation commitEdit {
          orderEditCommit(id: "'.$calculateOrderId.'", notifyCustomer: true, staffNote: "Due excise tax") {
            order {
              id
            }
            userErrors {
              field
              message
            }
          }
        }';

        $orderEditCommitRequest = $shop->api()->graph($orderEditCommitQuery);
        $orderEditCommit = $orderEditCommitRequest['body']['data']['orderEditCommit']['order'];
        $orderId = $orderEditCommit['id'];
    }

    public function createOrder() {
        $shop = User::find(1);
        $order['order'] = [
            'email' => 'satish@praella.com',
            "line_items" => [
                [
                    "variant_id"=> 39391228723303,
                    "quantity"=> 1
                ],
                [
                    "variant_id"=> 20920339923047,
                    "quantity"=> 1
                ],
                [
                    "variant_id"=> 20920272093287,
                    "quantity"=> 1
                ]],
        ];
        $shop->api()->rest("POST", '/admin/orders.json', $order);
    }
}
