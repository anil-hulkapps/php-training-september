<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 0;
    public $shop;

    public function __construct($shop)
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
        Log::info("inside product sync");
        $count = $this->shop->api()->rest('GET', '/admin/products/count.json');
        if (isset($count['body']) && isset($count['body']['count'])) {
            $totalPage = ceil($count['body']['count'] / 50);
            Log::info($totalPage);
            Log::info($count['body']['count']);

            $limit = 50;
            $data = ['limit' => $limit];
            for ($i = 1; $i <= $totalPage; $i++) {
                Log::info($i);
                $data250 = $this->shop->api()->rest('GET', '/admin/products.json', $data);
                if (isset($data250['body']['products'])) {
                    foreach ($data250['body']['products'] as $key => $product) {
                        $productObj = new Product();
                        $productObj->shop_id = $this->shop->id;
                        $productObj->shopify_product_id = $product['id'];
                        $productObj->title = $product['title'];
                        $productObj->handle = $product['handle'];
                        $productObj->image_url = !empty($product['image']) ? $product['image']['src'] : null;
                        $productObj->save();

                        foreach ($product['variants'] as $variant) {
                            $variantObj = new ProductVariant();
                            $variantObj->shop_id = $this->shop->id;
                            $variantObj->product_id = $product['id'];
                            $variantObj->variant_id = $variant['id'];
                            $variantObj->option_1_name = isset($product['options'][0]) ? $product['options'][0]['name'] : null;
                            $variantObj->option_1_value = $variant['option1'];
                            $variantObj->option_2_name = isset($product['options'][1]) ? $product['options'][1]['name'] : null;
                            $variantObj->option_2_value = $variant['option2'];
                            $variantObj->option_3_name = isset($product['options'][2]) ? $product['options'][2]['name'] : null;
                            $variantObj->option_3_value = $variant['option3'];
                            $variantObj->sku = $variant['sku'];
                            $variantObj->barcode = $variant['barcode'];
                            $variantObj->price = $variant['price'];
                            $variantObj->compare_at_price = $variant['compare_at_price'];
                            $variantObj->quantity = $variant['inventory_quantity'];
                            $variantObj->save();
                        }
                    }
                    $data = ['page_info' => ($data250['link'] != null ? $data250['link']['next'] : ''), 'limit' => $limit];
                } else {
                    echo 'Shopify error' . PHP_EOL;
                    Log::info(json_encode($data250)) . PHP_EOL;
                }

            }
            Log::info("over");
        }
    }
}
