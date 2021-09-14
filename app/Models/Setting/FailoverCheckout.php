<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailoverCheckout extends Model
{
    use HasFactory;
    protected $table = 'failover_checkout';
    protected $guarded = ['id'];
}
