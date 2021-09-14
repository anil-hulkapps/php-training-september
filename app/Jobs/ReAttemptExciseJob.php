<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Traits\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReAttemptExciseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var $shop
     */
    public $shop;

    /**
     * @var $orderId
     */
    public $orderId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop, $orderId)
    {
        $this->shop = $shop;
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $totalExcise = Helpers::calculateExcise($this->shop, $this->orderId);
        Helpers::orderEdit($this->shop, $this->orderId, $totalExcise);
        Transaction::where('order_id', $this->orderId)->update(['is_recalcuted' => 1]);
    }
}
