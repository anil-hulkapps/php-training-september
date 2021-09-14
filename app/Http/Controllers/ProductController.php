<?php

namespace App\Http\Controllers;

use App\Models\Product_info;
use App\Models\ProductInfo;
use App\Models\ScheduelTask;
use App\Traits\Helpers;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ProductController extends Controller
{
    /**
     * @param Request $request
     */
    public function importProducts(Request $request)
    {
        $shop = $request->user();
        $shopId = $shop->id;
        if ($request->isMethod('POST')) {
            //dd($request->file);
            if ($request->hasFile('file')) {
                $img["key"] = 'file';
                $img["file"] = $request->file('file');
                $img["dir"] = "import";
                $img["old_file"] = null;
                $filepath = Helpers::fileUpload($request, $img);

                $scheduleTask = new ScheduelTask();
                $scheduleTask->source_file = $filepath;
                $scheduleTask->status = 'to_do';
                $scheduleTask->shop_id = $shopId;
                $scheduleTask->save();

                return response('success', 200);
            }
        }
    }
}
