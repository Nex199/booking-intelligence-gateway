<?php

declare(strict_types=1);

namespace App\DTO;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LeadAnalysisResultDto
{
    private function __construct(
        public readonly bool $isSuccess,
        public readonly int $latencyMs,
        public readonly string $reason = '',
        public readonly string $intent = 'other',
        public readonly ?string $checkInDate = null,
        public readonly ?int $nights = null,
        public readonly ?int $numberOfGuests = null,
        public readonly ?int $budget = null,
        public readonly ?string $guestPreferences = null,
        public readonly ?int $confidenceScore = null,
    ) {}

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'check_in_date' => $this->checkInDate,
            'nights' => $this->nights,
            'number_of_guests' => $this->numberOfGuests,
            'budget' => $this->budget,
            'guest_preferences' => $this->guestPreferences,
            'confidence_score' => $this->confidenceScore,
            'reason' => $this->reason,
        ];
    }

    public static function fromArray(array $rawData, int $latencyMs): self
    {
        $validator = Validator::make($rawData, [
            'intent' => ['required', 'string', 'in:booking,other'],
            'checkInDate' => ['nullable', 'string', 'date_format:Y-m-d'],
            'nights' => ['nullable', 'min:1', 'integer'],
            'numberOfGuests' => ['nullable', 'min:1', 'integer'],
            'budget' => ['nullable', 'integer'],
            'guestPreferences' => ['nullable', 'string'],
            'confidenceScore' => ['required', 'min:1', 'max:10', 'integer'],
            'reason' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $safeData = $validator->validated();

        return new self(
            isSuccess: true,
            latencyMs: $latencyMs,
            reason: strip_tags($safeData['reason'] ?? 'ИИ не указал причину'),
            intent: strip_tags($safeData['intent']),
            checkInDate: $safeData['checkInDate'] ?? null,
            nights: isset($safeData['nights']) ? (int) $safeData['nights'] : null,
            numberOfGuests: isset($safeData['numberOfGuests']) ? (int) $safeData['numberOfGuests'] : null,
            budget: isset($safeData['budget']) ? (int) $safeData['budget'] : null,
            guestPreferences: isset($safeData['guestPreferences']) ? strip_tags($safeData['guestPreferences']) : null,
            confidenceScore: (int) $safeData['confidenceScore'],
        );
    }
}
