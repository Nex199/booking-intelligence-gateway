<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\DataAnonymizationService;
use PHPUnit\Framework\TestCase;

class DataAnonymizationServiceTest extends TestCase
{
    public function test_anonymizes_empty_string(): void
    {
        $service = new DataAnonymizationService;

        $result = $service->anonymize('');
        
        $this->assertEquals('', $result);
    }

    public function test_anonymizes_email_address(): void
    {
        $service = new DataAnonymizationService;
        $text = 'Напишите на почту chelick@gmail.com по вопросам брони';
        $expected = 'Напишите на почту [EMAIL_REDACTED] по вопросам брони';

        $result = $service->anonymize($text);
        
        $this->assertEquals($expected, $result);
    }

    public function test_anonymizes_phone_number(): void
    {
        $service = new DataAnonymizationService;
        $text = 'Хочу забронировать номер, позвоните по этому номеру 89991234567 на 2026-07-30';
        $expected = 'Хочу забронировать номер, позвоните по этому номеру [PHONE_REDACTED] на 2026-07-30';

        $result = $service->anonymize($text);
        
        $this->assertEquals($expected, $result);
    }

    public function test_does_not_change_clean_text(): void
    {
        $service = new DataAnonymizationService;
        $text = 'Здравствуйте, хочу забронировать номер 17 числа на 3 дня';

        $result = $service->anonymize($text);
        
        $this->assertEquals($text, $result);
    }
}
