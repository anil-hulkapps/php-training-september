<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIdentifierForExcise extends Model
{
    use HasFactory;
    protected $table = 'product_identifier_for_excise';
    protected $guarded = ['id'];
}
