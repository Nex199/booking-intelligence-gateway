<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'event_id', 
        'source', 
        'event_type', 
        'external_id', 
        'payload', 
        'status', 
        'error_message'
    ];

    protected $casts = ['payload' => 'array'];
}
