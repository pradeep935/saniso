<?php

namespace Botble\AffiliatePro\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(AffiliateProSeeder::class);

        Model::reguard();
    }
}
