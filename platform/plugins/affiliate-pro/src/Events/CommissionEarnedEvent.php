<?php

namespace Botble\AffiliatePro\Events;

use Botble\AffiliatePro\Models\Commission;
use Botble\Base\Events\Event;
use Illuminate\Queue\SerializesModels;

class CommissionEarnedEvent extends Event
{
    use SerializesModels;

    public function __construct(public Commission $commission)
    {
    }
}
