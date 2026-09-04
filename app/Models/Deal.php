<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $external_id
 * @property string $status
 * @property string|null $external_status
 * @property string|null $guest_name
 * @property string|null $guest_phone
 * @property string|null $guest_email
 * @property string|null $hotel_name
 * @property string|null $check_in_date
 * @property int|null $number_of_guests
 * @property numeric|null $budget
 * @property string|null $hotel_preferences
 * @property string|null $ai_analysis
 * @property string $source
 * @property string|null $chat_history
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereAiAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereChatHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCheckInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereExternalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereGuestEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereGuestName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereGuestPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereHotelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereHotelPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereNumberOfGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Deal extends Model
{
    protected $fillable = [
        'title',
        'external_id',
        'status',
        'external_status',
        'amocrm_lead_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'hotel_name',
        'check_in_date',
        'number_of_guests',
        'budget',
        'guest_preferences',
        'ai_analysis',
        'source',
        'chat_history',
    ];

    protected $casts = [
        'ai_analysis' => 'array',
    ];
}
