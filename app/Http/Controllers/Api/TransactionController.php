<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ReAttemptExciseJob;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function orders(Request $request, User $user) {
        $sortBy = request('sortBy') ?? 'order_number';
        $sortOrder = request('sortOrder') ?? 'ascending';
        $sortOrder = ($sortOrder == 'descending') ? 'desc' : 'asc';
        $search = isset($request->search) && $request->search ? $request->search : '';
        $status = isset($request->status) && $request->status ? $request->status : '';
        $from_date = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $to_date = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $shop = $request->user();
        $shopId = $shop->id;

        $totalExciseErrors = Transaction::where('shop_id', $shopId)->whereNotNull('failed_reason')->where('is_ignore', 0)->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->count();
        $totalIgnoredOrders = Transaction::where('shop_id', $shopId)->whereNotNull('failed_reason')->where('is_ignore', 1)->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->count();
        $totalExciseCollection = Transaction::where('shop_id', $shopId)->whereNull('failed_reason')->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->sum('excise_tax');
        $orders = Transaction::where('shop_id', $shopId);
        if ($search) {
            $orders->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', '%'.$search.'%');
                $q->orWhere('customer', 'LIKE', '%'.$search.'%');
                $q->orWhere('state', 'LIKE', '%'.$search.'%');
            });
        }
        if ($from_date && $to_date) {
            $orders->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()]);
        }
        if ($status) {
            $orders->where('status', $status);
        }
        $orders->whereNull('failed_reason');
        $orders->orderBy($sortBy, $sortOrder);
        $orders = $orders->paginate(15);
        $orders->flatMap(function ($value) {
            $value->order_date = Carbon::parse($value->order_date)->format("d M, Y");
            $status = $this->status($value->status);
            $value->status = $status['status'];
            $value->badge = $status['badge'];
            $value->excise_tax = number_format($value->excise_tax, 2);
            $value->progress = $status['progress'];
        });
        return response(["data" => $orders, "total_excise_errors" => $totalExciseErrors, "total_ignored_orders" => $totalIgnoredOrders, 'total_excise_collection' => number_format($totalExciseCollection, 2)]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function exciseErrors(Request $request, User $user) {
        $sortBy = request('sortBy') ?? 'order_number';
        $sortOrder = request('sortOrder') ?? 'ascending';
        $sortOrder = ($sortOrder == 'descending') ? 'desc' : 'asc';
        $search = isset($request->search) && $request->search ? $request->search : '';
        $status = isset($request->status) && $request->status ? $request->status : '';
        $from_date = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $to_date = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $shop = $request->user();
        $shopId = $shop->id;

        $totalOrders = Transaction::where('shop_id', $shopId)->whereNull('failed_reason')->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->count();
        $totalIgnoredOrders = Transaction::where('shop_id', $shopId)->whereNotNull('failed_reason')->where('is_ignore', 1)->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->count();
        $totalExciseCollection = Transaction::where('shop_id', $shopId)->whereNull('failed_reason')->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->sum('excise_tax');
        $orders = Transaction::where('shop_id', $shopId);
        if ($search) {
            $orders->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', '%'.$search.'%');
                $q->orWhere('customer', 'LIKE', '%'.$search.'%');
                $q->orWhere('state', 'LIKE', '%'.$search.'%');
            });
        }
        if ($from_date && $to_date) {
            $orders->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()]);
        }
        if ($status) {
            $orders->where('status', $status);
        }
        $orders->whereNotNull('failed_reason');
        $orders->where('is_ignore', 0);
        $orders->orderBy($sortBy, $sortOrder);
        $orders = $orders->paginate(15);
        $orders->flatMap(function ($value) {
            $value->order_date = Carbon::parse($value->order_date)->format("d M, Y");
            $status = $this->status($value->status);
            $value->status = $status['status'];
            $value->badge = $status['badge'];
            $value->progress = $status['progress'];
        });
        return response(["data" => $orders, "total_orders" => $totalOrders, "total_ignored_orders" => $totalIgnoredOrders, 'total_excise_collection' => number_format($totalExciseCollection, 2)]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function ignoredOrders(Request $request, User $user) {
        $sortBy = request('sortBy') ?? 'order_number';
        $sortOrder = request('sortOrder') ?? 'ascending';
        $sortOrder = ($sortOrder == 'descending') ? 'desc' : 'asc';
        $search = isset($request->search) && $request->search ? $request->search : '';
        $from_date = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $to_date = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $shop = $request->user();
        $shopId = $shop->id;

        $totalOrders = Transaction::where('shop_id', $shopId)->whereNull('failed_reason')->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->count();
        $totalExciseErrors = Transaction::where('shop_id', $shopId)->whereNotNull('failed_reason')->where('is_ignore', 0)->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->count();
        $totalExciseCollection = Transaction::where('shop_id', $shopId)->whereNull('failed_reason')->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()])->sum('excise_tax');
        $orders = Transaction::where('shop_id', $shopId);
        if ($search) {
            $orders->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', '%'.$search.'%');
                $q->orWhere('customer', 'LIKE', '%'.$search.'%');
                $q->orWhere('state', 'LIKE', '%'.$search.'%');
            });
        }
        if ($from_date && $to_date) {
            $orders->whereBetween('order_date', [$from_date.Helpers::startTime(), $to_date.Helpers::endTime()]);
        }
        $orders->whereNotNull('failed_reason');
        $orders->where('is_ignore', 1);
        $orders->orderBy($sortBy, $sortOrder);
        $orders = $orders->paginate(15);
        $orders->flatMap(function ($value) {
            $value->order_date = Carbon::parse($value->order_date)->format("d M, Y");
            $status = $this->status($value->status);
            $value->status = $status['status'];
            $value->badge = $status['badge'];
            $value->progress = $status['progress'];
        });
        return response(["data" => $orders, "total_orders" => $totalOrders, "total_excise_errors" => $totalExciseErrors, 'total_excise_collection' => number_format($totalExciseCollection, 2)]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function ignoreExcise(Request $request) {
        $Responce = Transaction::where('id', $request->id)->update(['is_ignore' => 1]);
        if ($Responce) {
            return response(['data' => 'Order excise ignored successfully'], 200);
        } else {
            return response(['data' => 'Something went wrong please try later!'], 400);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function reattemptExcise(Request $request) {
        $shop = $request->user();
        ReAttemptExciseJob::dispatch($shop, $request->order_id);
        return response(['data' => "Re-attempt running in background!"], 200);
    }

    /**
     * @param $status
     * @return mixed
     */
    public function status($status) {
        switch ($status) {
            case 1:
                $response['status'] = 'Fulfilled';
                $response['badge'] = 'new';
                $response['progress'] = 'complete';
                break;
            case 2:
                $response['status'] = 'Unfulfilled';
                $response['badge'] = 'attention';
                $response['progress'] = 'incomplete';
                break;
            case 3:
                $response['status'] = 'Partially fulfilled';
                $response['badge'] = 'warning';
                $response['progress'] = 'partiallyComplete';
                break;
            default:
                $response['status'] = '-';
                $response['badge'] = '';
                $response['progress'] = '';
                break;
        }
        return $response;
    }
}
