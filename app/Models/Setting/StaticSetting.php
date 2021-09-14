<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticSetting extends Model
{
    use HasFactory;
    protected $table = 'static_avalara_setting';
    protected $guarded = ['id'];
}
