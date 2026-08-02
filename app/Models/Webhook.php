<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'name',
    'url',
    'events',
    'secret',
    'is_active',
])]
class Webhook extends Model
{
    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
