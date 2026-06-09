<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'event_date',
        'title',
        'type',
        'description',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
    ];
}
