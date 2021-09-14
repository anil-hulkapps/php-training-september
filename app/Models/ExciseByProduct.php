<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExciseByProduct extends Model
{
    use HasFactory;
    protected $table = 'excise_by_product';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function product() {
        return $this->hasOne(Product::class, 'shopify_product_id', 'product_id');
    }
}
