<?php

declare(strict_types=1);

namespace App\Services;

class DataAnonymizationService
{
    public function anonymize(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $patterns = [
            'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            'phone' => '/(?:\+?7|8)[\s\(-]*\d{3}[\s\)-]*\d{3}[\s\-]*\d{2}[\s\-]*\d{2}\b/',
        ];

        $replacements = [
            'email' => '[EMAIL_REDACTED]',
            'phone' => '[PHONE_REDACTED]',
        ];

        return preg_replace($patterns, $replacements, $text);
    }
}
