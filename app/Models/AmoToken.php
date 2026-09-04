<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmoToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'base_domain',
        'expires_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'string',
    ];
}
