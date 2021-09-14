<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\ExciseByProduct;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function listProduct(Request $request, User $user) {
        $sortBy = request('sortBy') ?? 'title';
        $sortOrder = request('sortOrder') ?? 'ascending';
        $sortOrder = ($sortOrder == 'descending') ? 'desc' : 'asc';
        $search = isset($request->search) && $request->search ? $request->search : '';
        $from_date = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->format('Y-m-d');
        $to_date = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->format('Y-m-d');

        $shop = $request->user();
        $shopId = $shop->id;
        //DB::enableQueryLog();

        //fetching data from product table
        /*$products = Product::with('exciseByProducts')->whereHas('exciseByProducts')->where('shop_id', $shopId);
        if ($search) {
            $products->where('title', 'LIKE', '%'.$search.'%');
        }*/


        $products = Product::with(['exciseByProducts' => function($query) use ($from_date, $to_date) {
            $query->select(['product_id', DB::raw('ROUND(SUM(excise_tax), 2) as total_excise_tax')]);
            $query->whereBetween('date', [$from_date, $to_date]);
            $query->groupBy('product_id');
        }])->whereHas('exciseByProducts')->where('shop_id', $shopId);
        if ($search) {
            $products->where('title', 'LIKE', '%'.$search.'%');
        }

        $products->orderBy($sortBy, $sortOrder);
        $products = $products->paginate(15);
        //Log::info(DB::getQueryLog());
        return response($products);
    }
}
