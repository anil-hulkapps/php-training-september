<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppUninstalledJob extends \Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob
{
    public function __construct($shopDomain, $data)
    {
        $this->data = $data;
        $this->shopDomain = $shopDomain;
        Log::info("in uninstall job");
    }
}
