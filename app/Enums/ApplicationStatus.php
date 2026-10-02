<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case AwaitingResponse = 'awaiting_response';
    case ResponseReceived = 'response_received';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';
    case NeedsReview = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingResponse => 'Awaiting response',
            self::ResponseReceived => 'Response received',
            self::Interview => 'Interview',
            self::Offer => 'Offer',
            self::Rejected => 'Rejected',
            self::NeedsReview => 'Needs review',
        };
    }
}
