<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        Log::info("==========");
        Log::info(json_encode($this));
        Log::info("==========");
        $user = $this->user;
        $response['name'] = $user->title;
        //$response['last_paid_date'] = $user->last_paid_date != null ? Carbon::parse($user->last_paid_date)->format('m-d-Y') : '---';
        //$response['_paid'] = $currency.number_format($this->paid, 2);
        //$response['_unpaid'] = $currency.number_format($this->unpaid,2);
        return $response;
    }
}
