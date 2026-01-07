<?php

namespace Botble\AffiliatePro\Events;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Events\Event;
use Illuminate\Queue\SerializesModels;

class AffiliateApplicationRejectedEvent extends Event
{
    use SerializesModels;

    public function __construct(public Affiliate $affiliate)
    {
    }
}
