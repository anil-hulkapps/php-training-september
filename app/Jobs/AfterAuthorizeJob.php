<?php

namespace App\Jobs;

use App\Models\AvalaraExciseTaxProduct;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AfterAuthorizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $shop = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopInfo = $this->shop->api()->rest('GET', '/admin/api/shop.json');

        $this->shop->shop_name = $shopInfo['body']['shop']['shop_owner'];
        $this->shop->currency = $shopInfo['body']['shop']['currency'];
        $this->shop->currency_format = Str::replaceFirst(" {{amount}}", "", $shopInfo['body']['shop']['money_in_emails_format']);
        $this->shop->save();

        $this->addAvalaraExciseTaxProduct($this->shop);
    }

    public function addAvalaraExciseTaxProduct($shopInfo) {
        $shopifyProduct = $this->addExciseTaxProductInShopify($shopInfo);

        if($shopifyProduct) {
            AvalaraExciseTaxProduct::firstOrCreate(['shop_id' => $shopInfo->id], [
                'shopify_product_id' => $shopifyProduct['id'],
                'title' => $shopifyProduct['title'],
                'handle' => $shopifyProduct['handle']
            ]);
        }
    }

    public function addExciseTaxProductInShopify($shop) {
        $product = null;

        $variants = [];
        $variants['price'] = '10000.00';

        $params = [];
        $params['title'] = 'Avalara excise tax';
        $params['handle'] = 'avalara-excise';
        $params['variants'][] = $variants;

        $addProduct = [];
        $addProduct['product'] = $params;

        $response = $shop->api()->rest('POST', '/admin/products.json', $addProduct);

        if(isset($response['body']['product']) && !empty($response['body']['product'])) {
            $product = $response['body']['product'];
            $shopifyVariantId = $product['variants'][0]['id'];
            $this->manageThemeAssets($shop, $shopifyVariantId);
        }

        return $product;
    }

    public function manageThemeAssets($shop, $shopifyVariantId) {
        $themes = $shop->api()->rest(
            'GET',
            '/admin/themes.json',
            [ 'role' => 'main' ]
        );

        if(isset($themes['body']['themes'][0]) && !empty($themes['body']['themes'])) {
            $themeDetail = $themes['body']['themes'][0];
            $this->updateThemeAssets($shop, $themeDetail, $shopifyVariantId);
        }
    }

    public function updateThemeAssets($shop, $themeDetail, $shopifyVariantId) {

        $themeId = $themeDetail->id;

        $asset = [];
        $asset['key'] = 'snippets/avalara_excise.liquid';
        $asset['value'] = '<div data-avalara_ex_product_id="'.$shopifyVariantId.'" style="display:none">'.$shopifyVariantId.'</div>';

        $shop->api()->rest('PUT', '/admin/themes/'.$themeId.'/assets.json',
            ['asset' => $asset]
        );
    }
}
