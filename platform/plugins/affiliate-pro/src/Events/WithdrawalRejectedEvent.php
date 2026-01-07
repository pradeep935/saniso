<?php

namespace Botble\AffiliatePro\Events;

use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Events\Event;
use Illuminate\Queue\SerializesModels;

class WithdrawalRejectedEvent extends Event
{
    use SerializesModels;

    public function __construct(public Withdrawal $withdrawal, public string $reason = '')
    {
    }
}
