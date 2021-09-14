<?php

namespace App\Jobs;

use App\Models\AvalaraExciseTaxProduct;
use App\Models\Product;
use App\Models\User;
use App\ShopTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProductsDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop = User::where(['name' => $this->shopDomain->toNative()])->first();
        $product = $this->data;

        $dbProduct = AvalaraExciseTaxProduct::where(['shop_id' => $shop->id, 'shopify_product_id' => $product->id])->first();

        if($dbProduct) {
            $shopifyProductId = $this->addExciseTaxProductInShopify($shop);

            $dbProduct->shopify_product_id = $shopifyProductId;
            $dbProduct->save();

            Mail::to($shop->email)->send(new \App\Mail\ExciseProductDelete());
        }
    }

    public function addExciseTaxProductInShopify($shop) {
        $shopifyProductId = null;

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
            $shopifyProductId = $product['id'];
            $shopifyVariantId = $product['variants'][0]['id'];
            $this->manageThemeAssets($shop, $shopifyVariantId);
        }

        return $shopifyProductId;
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
