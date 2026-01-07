<?php

namespace Botble\AffiliatePro\Events;

use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Queue\SerializesModels;

class WithdrawalRequestedEvent extends Event
{
    use SerializesModels;

    public function __construct(public Customer $customer, public Withdrawal $withdrawal)
    {
    }
}
