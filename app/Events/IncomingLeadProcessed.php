<?php

declare(strict_types=1);

namespace App\Events;

use App\DTO\LeadAnalysisResultDto;
use App\Models\Deal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingLeadProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Deal $deal,
        public string $correlationId,
        public ?LeadAnalysisResultDto $result = null,
    ) {}

}
