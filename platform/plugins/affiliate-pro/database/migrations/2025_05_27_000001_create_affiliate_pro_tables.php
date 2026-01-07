<?php

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->index();
                $table->string('affiliate_code', 100)->unique();
                $table->decimal('balance', 15)->default(0);
                $table->decimal('total_commission', 15)->default(0);
                $table->decimal('total_withdrawn', 15)->default(0);
                $table->string('status', 60)->default(AffiliateStatusEnum::PENDING);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_commissions')) {
            Schema::create('affiliate_commissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_id')->index();
                $table->foreignId('order_id')->index();
                $table->decimal('amount', 15);
                $table->string('description')->nullable();
                $table->string('status', 60)->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_withdrawals')) {
            Schema::create('affiliate_withdrawals', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_id')->index();
                $table->decimal('amount', 15, 2);
                $table->string('status', 60)->default('pending');
                $table->string('payment_method');
                $table->text('payment_details')->nullable();
                $table->text('notes')->nullable();
                $table->string('payment_channel', 120)->nullable();
                $table->string('transaction_id', 120)->nullable();
                $table->text('bank_info')->nullable();
                $table->integer('customer_id')->unsigned()->nullable();
                $table->string('currency', 120)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_transactions')) {
            Schema::create('affiliate_transactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_id')->index();
                $table->decimal('amount', 15);
                $table->string('description');
                $table->string('type', 60); // commission, withdrawal, etc.
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_clicks')) {
            Schema::create('affiliate_clicks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_id')->index();
                $table->foreignId('short_link_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('referrer_url')->nullable();
                $table->string('landing_url')->nullable();
                $table->boolean('converted')->default(false);
                $table->timestamp('conversion_time')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_coupons')) {
            Schema::create('affiliate_coupons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_id')->index();
                $table->foreignId('discount_id')->index();
                $table->string('code', 20)->unique();
                $table->string('description')->nullable();
                $table->decimal('discount_amount', 15);
                $table->string('discount_type', 20)->default('percentage');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_short_links')) {
            Schema::create('affiliate_short_links', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_id')->index();
                $table->string('short_code', 20)->unique();
                $table->string('destination_url');
                $table->string('title')->nullable();
                $table->foreignId('product_id')->nullable()->index();
                $table->integer('clicks')->default(0);
                $table->integer('conversions')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('ec_products')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                if (! Schema::hasColumn('ec_products', 'is_affiliate_enabled')) {
                    $table->boolean('is_affiliate_enabled')->default(true)->after('tax_id');
                }

                if (! Schema::hasColumn('ec_products', 'affiliate_commission_percentage')) {
                    $table->float('affiliate_commission_percentage')->nullable()->after('is_affiliate_enabled');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_short_links');
        Schema::dropIfExists('affiliate_coupons');
        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliate_transactions');
        Schema::dropIfExists('affiliate_withdrawals');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliates');

        if (Schema::hasTable('ec_products')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                if (Schema::hasColumn('ec_products', 'affiliate_commission_percentage')) {
                    $table->dropColumn('affiliate_commission_percentage');
                }

                if (Schema::hasColumn('ec_products', 'is_affiliate_enabled')) {
                    $table->dropColumn('is_affiliate_enabled');
                }
            });
        }
    }
};
