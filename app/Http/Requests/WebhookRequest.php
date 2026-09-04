<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class WebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_type' => [
                'required',
                'string',
                'in:deal.status_changed,lead.created',
            ],
            'message' => [
                'required_if:event_type,lead.created',
                'string',
            ],

            'deal_status' => [
                'required_if:event_type,deal.status_changed',
                'string',
            ],

            'event_id' => [
                'required',
                'string',
                'uuid',
            ],

            'guest_phone' => [
                'string',
                'required_if:event_type,lead.created',
            ],

            'guest_email' => [
                'nullable',
                'email',
            ],

            'external_id' => [
                'required',
                'string',
            ],

            'guest_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'hotel_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'source' => [
                'required',
                'string',
            ],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('guest_phone')) {
            $rawPhone = (string) $this->input('guest_phone');

            $cleanPhone = preg_replace('/\D/', '', $rawPhone);

            if (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '8')) {
                $cleanPhone = '7'.substr($cleanPhone, 1);
            }

            if (strlen($cleanPhone) === 10) {
                $cleanPhone = '7'.$cleanPhone;
            }

            $this->merge([
                'guest_phone' => $cleanPhone,
            ]);
        }
    }
}
