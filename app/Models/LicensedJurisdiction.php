<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicensedJurisdiction extends Model
{
    protected $table = 'licensed_jurisdictions';
    protected $guarded = ['id'];

    use HasFactory;
}
