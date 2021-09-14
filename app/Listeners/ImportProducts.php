<?php

namespace App\Listeners;

use App\Events\ScheduleSaved;
use App\Http\Controllers\ProductController;
use App\Jobs\ImportProductsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ImportProducts
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ScheduleSaved  $event
     * @return void
     */
    public function handle(ScheduleSaved $event)
    {
        \Log::info('Job Started in listeners');
        $scheduleTask = $event->scheduleTask;
        $statuses = [
            'to_do'
        ];
        if(in_array($scheduleTask->status, $statuses)) {
            if($scheduleTask->status == "to_do") {
                ImportProductsJob::dispatch($scheduleTask, $scheduleTask->id, $scheduleTask->shop_id);
            }
        }
    }
}
