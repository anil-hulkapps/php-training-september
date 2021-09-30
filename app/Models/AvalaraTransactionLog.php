<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvalaraTransactionLog extends Model
{
    use HasFactory;

    protected $table = 'avalara_transaction_log';

    protected $guarded = [];

    protected $casts = [
        'filtered_request_data' => 'array',
    ];

    public function getRequestDataAttribute($value)
    {
        if(!is_null($value)) {
            return json_decode($value);
        }
        return null;
    }

    public function getResponseAttribute($value)
    {
        if(isJSON($value)) {
            return json_decode($value);
        }
        return $value;
    }
}
