<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInfo extends Model
{
    use HasFactory;
    protected $table="product_infos";

    protected $fillable = [
        'country_code',
        'jurisdiction',
        'product_code',
        'description',
        'alternate_product_code',
        'terminal_code',
        'tax_code',
        'alternate_effective_date',
        'alternate_obsolete_date',
        'product_effective_date',
        'product_obsolete_date',
    ];
}
