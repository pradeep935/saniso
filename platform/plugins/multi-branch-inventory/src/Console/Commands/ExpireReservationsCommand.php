<?php

namespace Botble\MultiBranchInventory\Console\Commands;

use Botble\MultiBranchInventory\Models\PickupReservation;
use Illuminate\Console\Command;

class ExpireReservationsCommand extends Command
{
    protected $signature = 'multi-branch-inventory:expire-reservations';

    protected $description = 'Expire old pickup reservations and release reserved stock';

    public function handle(): int
    {
        $this->info('Starting to expire old reservations...');

        $expiredCount = PickupReservation::cancelExpiredReservations();

        $this->info("Expired {$expiredCount} reservations.");

        return 0;
    }
}