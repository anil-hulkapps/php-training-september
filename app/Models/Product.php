<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['shop_id', 'shopify_product_id', 'title', 'handle', 'vendor', 'tags', 'image_url'];
    protected $casts = [
        'tags' => 'array',
    ];

    public function exciseByProducts() {
        return $this->hasMany(ExciseByProduct::class, 'product_id' , 'shopify_product_id');
    }
}
