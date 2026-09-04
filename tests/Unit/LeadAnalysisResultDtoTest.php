<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\LeadAnalysisResultDto;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeadAnalysisResultDtoTest extends TestCase
{
    public function test_create_dto_valid_data(): void
    {
        $data = [
            'intent' => 'booking',
            'checkInDate' => '2026-03-12',
            'nights' => 3,
            'numberOfGuests' => 1,
            'budget' => 6000,
            'guestPreferences' => 'Тихий номер',
            'confidenceScore' => 7,
            'reason' => 'Клиент планирует заезд 12.03.2026 на 3 ночи на одного человека с бюджетом 6000 и попросил тихий номер.',
        ];
        $service = LeadAnalysisResultDto::fromArray($data, 250);

        $expected = [
            'intent' => 'booking',
            'check_in_date' => '2026-03-12',
            'nights' => 3,
            'number_of_guests' => 1,
            'budget' => 6000,
            'guest_preferences' => 'Тихий номер',
            'confidence_score' => 7,
            'reason' => 'Клиент планирует заезд 12.03.2026 на 3 ночи на одного человека с бюджетом 6000 и попросил тихий номер.',
        ];
        $this->assertSame($expected, $service->toArray());
    }

    public function test_throws_validation_exception_on_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        LeadAnalysisResultDto::fromArray([], 250);
    }

    public function test_strips_html_tags_from_string_fields(): void
    {
        $data = [
            'intent' => 'booking',
            'checkInDate' => '2026-03-12',
            'nights' => 3,
            'numberOfGuests' => 1,
            'budget' => 6000,
            'guestPreferences' => '<b>я хочу пиццы</b> <script>alert("xss")</script>',
            'confidenceScore' => 7,
            'reason' => '<a>Просто</a>',
        ];

        $dto = LeadAnalysisResultDto::fromArray($data, 250);

        $expected = [
            'intent' => 'booking',
            'check_in_date' => '2026-03-12',
            'nights' => 3,
            'number_of_guests' => 1,
            'budget' => 6000,
            'guest_preferences' => 'я хочу пиццы alert("xss")',
            'confidence_score' => 7,
            'reason' => 'Просто',
        ];

        $this->assertSame($expected, $dto->toArray());
    }
}
