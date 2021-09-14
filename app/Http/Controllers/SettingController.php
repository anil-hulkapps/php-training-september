<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function Steps(Request $request) {
        Log::info(json_encode($request->all()));
        //return "aaaaaaaaa";
        $shop = $request->shop;
        dd($shop);
        if ($request->isMethod("GET")) {
            $shop = $request->shop;
        } else {

        }
    }
}
