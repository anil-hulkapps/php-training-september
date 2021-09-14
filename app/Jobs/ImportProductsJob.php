<?php

namespace App\Jobs;

use App\Models\ProductInfo;
use App\Models\ScheduelTask;
use App\Models\Setting\StaticSetting;
use App\Models\User;
use App\Traits\Helpers;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $scheduleTask;
    public $scheduleID;
    public $shopID;

    public function __construct($scheduleTask, $scheduleID, $shopID) {
        $this->scheduleTask = $scheduleTask;
        $this->scheduleID = $scheduleID;
        $this->shopID = $shopID;
    }

    /**
     * @return void
     */
    public function handle()
    {
        Log::info("importToDatabase");
        ScheduelTask::find($this->scheduleTask->id)->update(['status' => "in_progress"]);

        $scheduleRecords = ScheduelTask::where('shop_id', $this->shopID)->get();
        foreach ($scheduleRecords as $scheduleRecord) {
            ProductInfo::where('schedule_id', $scheduleRecord->id)->delete();
        }

        $filepath = storage_path('app/public/' . $this->scheduleTask->source_file);
        $reader = new Xlsx();
        $spreadsheet = $reader->load($filepath);

        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        foreach ($sheetData[0] as $key => $field) {
            $field = trim($field);
            $headers[] = $field;
        }
        unset($sheetData[0]);
        $success = $fail = 0;
        $columnMismatched = false;
        $failedData = [];

        foreach ($headers as $header) {
            if (!in_array($header, Helpers::FixedCSVHeader())) {
                $columnMismatched = true;
            }
        }

        if (!$columnMismatched) {
            foreach ($sheetData as $row) {
                try {
                    $data = [];
                    $data = array_combine($headers, $row);
                    $product = new ProductInfo();
                    $product->country_code = $data['country_code'] ?? '';
                    $product->jurisdiction = $data["jurisdiction"] ?? '';
                    $product->product_code = $data["product_code"] ?? '';
                    $product->description = $data["description"] ?? '';
                    $product->alternate_product_code = $data["alternate_product_code"] ?? '';
                    $product->terminal_code = $data["terminal_code"] ?? '';
                    $product->tax_code = $data["tax_code"] ?? '';
                    $product->alternate_effective_date = $data["alternate_effective_date"] ?? '';
                    $product->alternate_obsolete_date = $data["alternate_obsolete_date"] ?? '';
                    $product->product_effective_date = $data["product_effective_date"] ?? '';
                    $product->product_obsolete_date = $data["product_obsolete_date"] ?? '';
                    $product->schedule_id = $this->scheduleID;
                    $is_saved = $product->save();

                    if (($is_saved)) {
                        $success++;
                    } else {
                        $fail++;
                    }

                } catch (Exception $e) {
                    $failedData[] = [
                        'title' => @$data["title"],
                        'reason' => $e->getMessage()
                    ];
                    Log::info(json_encode($failedData));
                }
            }
        }

        $schedule = ScheduelTask::find($this->scheduleTask->id);
        $schedule->last_row = $success;
        $schedule->status = 'completed';
        $schedule->save();

        $shop = User::where('id', $this->shopID)->first();
        $params = [];
        $params['success'] = $success;
        $params['fail'] = $fail;
        Mail::to($shop->email)->send(new \App\Mail\AvalaraProductImport($params));
    }
}
