<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $event_id
 * @property string $source
 * @property string $event_type
 * @property string|null $external_id
 * @property array<array-key, mixed> $payload
 * @property string $status
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntegrationLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class IntegrationLog extends Model
{
    protected $fillable = [
        'event_id',
        'source',
        'event_type',
        'external_id',
        'payload',
        'status',
        'error_message',
        'latency_ms',
    ];

    protected $casts = ['payload' => 'array'];
}
