<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('affiliates')
            ->where('status', 'published')
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        DB::table('affiliates')
            ->where('status', 'approved')
            ->update(['status' => 'published']);
    }
};
