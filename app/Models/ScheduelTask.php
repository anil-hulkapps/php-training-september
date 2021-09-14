<?php

namespace App\Models;

use App\Events\ScheduleSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduelTask extends Model
{
    use HasFactory;
    protected $dispatchesEvents = [
        'saved' => ScheduleSaved::class,
    ];

    protected $table = "schedule_task";
    protected $fillable = ["source_file", "status", "last_row"];
}
