<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvalaraExciseTaxProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'shop_id',
        'shopify_product_id',
        'title',
        'handle',
    ];
}
