<?php

namespace Botble\AffiliatePro\Database\Seeders;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\AffiliatePro\Enums\WithdrawalStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateClick;
use Botble\AffiliatePro\Models\AffiliateCoupon;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\AffiliatePro\Models\Commission;
use Botble\AffiliatePro\Models\Transaction;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AffiliateProSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncateTables();
        $this->seedBasicSettings();
        $this->seedAffiliates();
        $this->seedCommissions();
        $this->seedWithdrawals();
        $this->seedTransactions();
        $this->seedShortLinks();
        $this->seedClicks();
        $this->seedCoupons();
    }

    protected function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Affiliate::query()->truncate();
        Commission::query()->truncate();
        Withdrawal::query()->truncate();
        Transaction::query()->truncate();
        AffiliateShortLink::query()->truncate();
        AffiliateClick::query()->truncate();
        AffiliateCoupon::query()->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function seedBasicSettings(): void
    {
        $this->command->info('Seeding affiliate pro basic settings...');

        // Basic affiliate settings
        Setting::set('affiliate_commission_percentage', 10);
        Setting::set('affiliate_cookie_lifetime', 30);
        Setting::set('affiliate_minimum_withdrawal_amount', 50);
        Setting::set('affiliate_enable_registration', true);
        Setting::set('affiliate_auto_approve_affiliates', false);
        Setting::set('affiliate_auto_approve_commissions', false);

        // Enable commission for each category
        Setting::set('affiliate_enable_commission_for_each_category', true);

        // Get some product categories to create commission settings
        $categories = ProductCategory::query()
            ->wherePublished()
            ->limit(6)
            ->get();

        if ($categories->isNotEmpty()) {
            $commissionByCategory = [];

            // Create different commission rates for different categories
            $commissionRates = [15, 12, 8, 20, 10, 18]; // Different percentages for variety

            foreach ($categories->take(6) as $index => $category) {
                $commissionByCategory[] = [
                    'commission_percentage' => $commissionRates[$index] ?? 10,
                    'categories' => json_encode([$category->id]),
                ];
            }

            // Group some categories together for demonstration
            if ($categories->count() >= 4) {
                // Create a group with multiple categories at 25% commission
                $groupCategories = $categories->skip(2)->take(2)->pluck('id')->toArray();
                $commissionByCategory[] = [
                    'commission_percentage' => 25,
                    'categories' => json_encode($groupCategories),
                ];
            }

            Setting::set('affiliate_commission_by_category', json_encode($commissionByCategory));

            $this->command->info('Created commission settings for ' . count($commissionByCategory) . ' category groups');
        } else {
            $this->command->warn('No product categories found. Skipping category commission settings.');
        }

        // Promotional banners settings
        Setting::set('affiliate_banner_1_name', 'Banner 1 (468x60)');
        Setting::set('affiliate_banner_2_name', 'Banner 2 (728x90)');
        Setting::set('affiliate_banner_3_name', 'Banner 3 (300x250)');

        Setting::save();

        $this->command->info('Basic affiliate settings seeded successfully.');
    }

    protected function seedAffiliates(): void
    {
        $customers = Customer::query()->limit(50)->get();

        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please seed customers first.');

            return;
        }

        $statuses = [
            AffiliateStatusEnum::APPROVED,
            AffiliateStatusEnum::PENDING,
            AffiliateStatusEnum::REJECTED,
        ];

        $statusWeights = [70, 20, 10]; // 70% published, 20% pending, 10% draft

        foreach ($customers as $index => $customer) {
            // Use weighted random status
            $status = $this->getRandomWeightedElement($statuses, $statusWeights);

            // Create more realistic balance data based on how long they've been an affiliate
            $createdAt = Carbon::now()->subDays(rand(1, 365));
            $daysSinceCreation = $createdAt->diffInDays(Carbon::now());

            // More established affiliates have higher earnings
            $baseCommission = rand(100, 1000);
            $totalCommission = $baseCommission * (1 + ($daysSinceCreation / 100));

            // Round to 2 decimal places
            $totalCommission = round($totalCommission, 2);

            // Active affiliates have withdrawn some money
            $totalWithdrawn = $status === AffiliateStatusEnum::APPROVED ?
                rand(0, (int) ($totalCommission * 0.7)) : 0;

            $balance = $totalCommission - $totalWithdrawn;

            // Create affiliate with realistic timestamps
            $updatedAt = (clone $createdAt)->addDays(rand(1, min(30, $daysSinceCreation)));

            $affiliate = Affiliate::query()->create([
                'customer_id' => $customer->id,
                'affiliate_code' => 'AFF' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'balance' => $balance,
                'total_commission' => $totalCommission,
                'total_withdrawn' => $totalWithdrawn,
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            $this->command->info('Created affiliate: ' . $affiliate->affiliate_code . ' with status: ' . $status);
        }
    }

    /**
     * Get a random element from an array with weighted probabilities
     */
    protected function getRandomWeightedElement(array $elements, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $randomWeight = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($elements as $index => $element) {
            $currentWeight += $weights[$index];
            if ($randomWeight <= $currentWeight) {
                return $element;
            }
        }

        return $elements[0]; // Fallback
    }

    protected function seedCommissions(): void
    {
        $affiliates = Affiliate::query()->where('status', AffiliateStatusEnum::APPROVED)->get();
        $orders = Order::query()->limit(200)->get();

        if ($orders->isEmpty()) {
            $this->command->info('No orders found. Creating commissions without order references.');
            // Create dummy orders for commission references
            $orders = $this->createDummyOrders(10);
        }

        if ($affiliates->isEmpty()) {
            $this->command->info('No published affiliates found. Skipping commission seeding.');

            return;
        }

        $statuses = [
            CommissionStatusEnum::PENDING,
            CommissionStatusEnum::APPROVED,
            CommissionStatusEnum::REJECTED,
        ];

        $statusWeights = [30, 60, 10]; // 30% pending, 60% approved, 10% rejected

        $descriptions = [
            'Commission for order ',
            'Affiliate commission for order ',
            'Referral commission for order ',
        ];

        // Create a date range for the last 12 months to distribute commissions
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();
        $dateRange = CarbonPeriod::create($startDate, '1 day', $endDate);
        $dates = [];

        foreach ($dateRange as $date) {
            $dates[] = $date;
        }

        // Weight more recent dates higher for a realistic distribution
        $dateCount = count($dates);

        foreach ($affiliates as $affiliate) {
            // Create between 20-100 commissions for each affiliate based on their creation date
            $daysSinceCreation = $affiliate->created_at->diffInDays(Carbon::now());
            $commissionCount = min(100, max(20, (int) ($daysSinceCreation / 3)));

            // Top affiliates get more commissions
            if ($affiliate->total_commission > 5000) {
                $commissionCount = (int) ($commissionCount * 1.5);
            }

            for ($i = 0; $i < $commissionCount; $i++) {
                $order = $orders->random();
                $status = $this->getRandomWeightedElement($statuses, $statusWeights);

                // More realistic commission calculation
                $commissionRate = rand(5, 20) / 100; // 5-20% commission
                $amount = round($order->amount * $commissionRate, 2);

                // Create commission with a realistic date distribution
                // Only use dates after the affiliate was created
                $validDates = array_filter($dates, function ($date) use ($affiliate) {
                    return $date > $affiliate->created_at;
                });

                if (empty($validDates)) {
                    continue;
                }

                // Select a date with weighted probability (more recent dates more likely)
                $dateIndex = array_rand($validDates);
                $createdAt = $validDates[$dateIndex];

                // Add some hours and minutes for more realism
                $createdAt->addHours(rand(0, 23))->addMinutes(rand(0, 59));

                // Updated a few hours or days later
                $updatedAt = (clone $createdAt)->addHours(rand(1, 48));

                $commission = Commission::query()->create([
                    'affiliate_id' => $affiliate->id,
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'description' => $descriptions[array_rand($descriptions)] . $order->code . ' (' . ($commissionRate * 100) . '%)',
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $this->command->info(
                    'Created commission: ' . $commission->id . ' for affiliate: ' . $affiliate->affiliate_code .
                    ' with status: ' . $status . ' and amount: ' . format_price($amount)
                );
            }
        }
    }

    protected function seedWithdrawals(): void
    {
        $affiliates = Affiliate::query()->where('status', AffiliateStatusEnum::APPROVED)->get();

        if ($affiliates->isEmpty()) {
            $this->command->info('No published affiliates found. Skipping withdrawal seeding.');

            return;
        }

        $paymentMethods = ['bank_transfer', 'paypal', 'stripe', 'razorpay'];
        $paymentMethodWeights = [40, 30, 20, 10]; // 40% bank, 30% paypal, 20% stripe, 10% razorpay

        $statuses = [
            WithdrawalStatusEnum::PENDING,
            WithdrawalStatusEnum::PROCESSING,
            WithdrawalStatusEnum::APPROVED,
            WithdrawalStatusEnum::REJECTED,
            WithdrawalStatusEnum::CANCELED,
        ];

        $statusWeights = [30, 15, 40, 10, 5]; // 30% pending, 15% processing, 40% approved, 10% rejected, 5% canceled

        $faker = $this->fake();

        // Create a date range for the last 6 months to distribute withdrawals
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();
        $dateRange = CarbonPeriod::create($startDate, '1 day', $endDate);
        $dates = [];

        foreach ($dateRange as $date) {
            $dates[] = $date;
        }

        foreach ($affiliates as $affiliate) {
            // Skip affiliates with very low balance
            if ($affiliate->balance < 50) {
                continue;
            }

            // Create withdrawals based on affiliate's activity level
            $withdrawalCount = 0;

            if ($affiliate->total_commission > 10000) {
                // Very active affiliates make more frequent withdrawals
                $withdrawalCount = rand(8, 15);
            } elseif ($affiliate->total_commission > 5000) {
                // Active affiliates
                $withdrawalCount = rand(5, 10);
            } elseif ($affiliate->total_commission > 1000) {
                // Moderately active affiliates
                $withdrawalCount = rand(3, 7);
            } else {
                // Less active affiliates
                $withdrawalCount = rand(1, 4);
            }

            // Ensure total withdrawals don't exceed balance
            $totalWithdrawalAmount = 0;
            $maxWithdrawalAmount = $affiliate->balance * 0.95; // Leave some balance

            // Preferred payment method for this affiliate
            $preferredMethod = $paymentMethods[array_rand($paymentMethods)];
            $preferredMethodWeight = 70; // 70% chance to use preferred method

            // Custom payment method weights for this affiliate
            $customPaymentMethodWeights = $paymentMethodWeights;
            foreach ($paymentMethods as $index => $method) {
                if ($method === $preferredMethod) {
                    $customPaymentMethodWeights[$index] = $preferredMethodWeight;
                } else {
                    $customPaymentMethodWeights[$index] = (100 - $preferredMethodWeight) / (count($paymentMethods) - 1);
                }
            }

            // Create withdrawal records
            for ($i = 0; $i < $withdrawalCount; $i++) {
                $status = $this->getRandomWeightedElement($statuses, $statusWeights);
                $paymentMethod = $this->getRandomWeightedElement($paymentMethods, $customPaymentMethodWeights);

                // Calculate a realistic withdrawal amount based on affiliate's earnings
                $minAmount = 50;
                $maxPossibleAmount = min(
                    $affiliate->total_commission * 0.3, // Max 30% of total earnings per withdrawal
                    $maxWithdrawalAmount - $totalWithdrawalAmount
                );

                // Ensure we have a valid range
                if ($maxPossibleAmount <= $minAmount) {
                    break; // Stop if we can't withdraw more
                }

                // More established affiliates tend to make larger withdrawals
                if ($affiliate->total_commission > 5000) {
                    $minAmount = 100;
                }

                $amount = rand($minAmount, (int) $maxPossibleAmount);
                $totalWithdrawalAmount += $amount;

                $paymentDetails = [];

                switch ($paymentMethod) {
                    case 'bank_transfer':
                        $paymentDetails = [
                            'bank_name' => $faker->company(),
                            'account_number' => $faker->bankAccountNumber(),
                            'account_name' => $faker->name(),
                            'swift_code' => $faker->swiftBicNumber(),
                            'routing_number' => $faker->numerify('########'),
                            'bank_address' => $faker->address(),
                        ];

                        break;

                    case 'paypal':
                        $paymentDetails = [
                            'paypal_email' => $faker->email(),
                            'paypal_account_name' => $faker->name(),
                        ];

                        break;

                    case 'stripe':
                        $paymentDetails = [
                            'card_last_four' => $faker->numerify('####'),
                            'card_brand' => $faker->randomElement(['visa', 'mastercard', 'amex', 'discover']),
                            'account_holder_name' => $faker->name(),
                            'expiry_date' => $faker->creditCardExpirationDateString(),
                        ];

                        break;

                    case 'razorpay':
                        $paymentDetails = [
                            'account_id' => 'acc_' . Str::random(14),
                            'contact_id' => 'cont_' . Str::random(14),
                            'fund_account_id' => 'fa_' . Str::random(14),
                            'account_holder_name' => $faker->name(),
                        ];

                        break;
                }

                // Create withdrawal with realistic dates
                // Only use dates after the affiliate was created
                $validDates = array_filter($dates, function ($date) use ($affiliate) {
                    return $date > $affiliate->created_at;
                });

                if (empty($validDates)) {
                    continue;
                }

                // Select a date with weighted probability (more recent dates more likely)
                $dateIndex = array_rand($validDates);
                $createdAt = $validDates[$dateIndex];

                // Add some hours and minutes for more realism
                $createdAt->addHours(rand(0, 23))->addMinutes(rand(0, 59));

                // Status-based update time
                $updatedAt = clone $createdAt;

                switch ($status) {
                    case WithdrawalStatusEnum::PENDING:
                        // Pending withdrawals are recent
                        $updatedAt->addHours(rand(1, 24));

                        break;
                    case WithdrawalStatusEnum::PROCESSING:
                        // Processing withdrawals started a bit ago
                        $updatedAt->addHours(rand(24, 72));

                        break;
                    case WithdrawalStatusEnum::APPROVED:
                    case WithdrawalStatusEnum::REJECTED:
                    case WithdrawalStatusEnum::CANCELED:
                        // Completed statuses took longer
                        $updatedAt->addDays(rand(1, 7));

                        break;
                }

                $withdrawal = Withdrawal::query()->create([
                    'affiliate_id' => $affiliate->id,
                    'amount' => $amount,
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_details' => json_encode($paymentDetails),
                    'notes' => $faker->paragraph(1),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $this->command->info(
                    'Created withdrawal: ' . $withdrawal->id .
                    ' for affiliate: ' . $affiliate->affiliate_code .
                    ' with status: ' . $status .
                    ' and amount: ' . format_price($amount)
                );
            }
        }
    }

    protected function seedTransactions(): void
    {
        $affiliates = Affiliate::query()->where('status', AffiliateStatusEnum::APPROVED)->get();

        if ($affiliates->isEmpty()) {
            $this->command->info('No published affiliates found. Skipping transaction seeding.');

            return;
        }

        $types = ['commission', 'withdrawal', 'adjustment'];
        $typeWeights = [60, 30, 10]; // 60% commission, 30% withdrawal, 10% adjustment

        foreach ($affiliates as $affiliate) {
            // Create between 15-40 transactions for each affiliate
            $transactionCount = rand(15, 40);

            for ($i = 0; $i < $transactionCount; $i++) {
                $type = $this->getRandomWeightedElement($types, $typeWeights);

                // For commission and withdrawal, reference existing records
                $referenceId = null;
                $referenceType = null;
                $amount = 0;
                $createdAt = null;

                if ($type === 'commission') {
                    $commission = Commission::query()
                        ->where('affiliate_id', $affiliate->id)
                        ->where('status', CommissionStatusEnum::APPROVED)
                        ->inRandomOrder()
                        ->first();

                    if ($commission) {
                        $referenceId = $commission->id;
                        $referenceType = Commission::class;
                        $amount = $commission->amount;
                        $createdAt = $commission->created_at;
                    } else {
                        // If no approved commission found, create a random one
                        $amount = rand(10, 200);
                        $createdAt = Carbon::now()->subDays(rand(1, 90));
                    }
                } elseif ($type === 'withdrawal') {
                    $withdrawal = Withdrawal::query()
                        ->where('affiliate_id', $affiliate->id)
                        ->whereIn('status', [WithdrawalStatusEnum::APPROVED, WithdrawalStatusEnum::PROCESSING])
                        ->inRandomOrder()
                        ->first();

                    if ($withdrawal) {
                        $referenceId = $withdrawal->id;
                        $referenceType = Withdrawal::class;
                        $amount = -$withdrawal->amount; // Negative for withdrawals
                        $createdAt = $withdrawal->created_at;
                    } else {
                        // If no approved withdrawal found, create a random one
                        $amount = -rand(50, 300);
                        $createdAt = Carbon::now()->subDays(rand(1, 60));
                    }
                } else {
                    // For adjustments, create random amounts (positive or negative)
                    $isPositive = rand(0, 1);
                    $amount = $isPositive ? rand(5, 100) : -rand(5, 100);
                    $createdAt = Carbon::now()->subDays(rand(1, 120));
                }

                // Set updated_at to be after created_at
                $updatedAt = (clone $createdAt)->addHours(rand(1, 48));

                $transaction = Transaction::query()->create([
                    'affiliate_id' => $affiliate->id,
                    'amount' => $amount,
                    'description' => $this->getTransactionDescription($type, $amount),
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'reference_type' => $referenceType,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $this->command->info(
                    'Created transaction: ' . $transaction->id .
                    ' for affiliate: ' . $affiliate->affiliate_code .
                    ' of type: ' . $type .
                    ' with amount: ' . format_price($amount)
                );
            }
        }
    }

    protected function getTransactionDescription(string $type, float $amount): string
    {
        switch ($type) {
            case 'commission':
                return 'Commission earned: ' . format_price($amount);
            case 'withdrawal':
                return 'Withdrawal processed: ' . format_price(abs($amount));
            case 'adjustment':
                if ($amount > 0) {
                    return 'Manual adjustment (credit): ' . format_price($amount);
                }

                return 'Manual adjustment (debit): ' . format_price(abs($amount));
            default:
                return 'Transaction: ' . format_price($amount);
        }
    }

    protected function seedShortLinks(): void
    {
        $affiliates = Affiliate::query()->where('status', AffiliateStatusEnum::APPROVED)->get();

        if ($affiliates->isEmpty()) {
            $this->command->info('No published affiliates found. Skipping short links seeding.');

            return;
        }

        $products = Product::query()->where('is_variation', 0)->limit(50)->get();

        if ($products->isEmpty()) {
            $this->command->info('No products found. Skipping product short links seeding.');

            return;
        }

        $faker = $this->fake();

        // Popular product categories for custom links
        $categories = [
            'electronics', 'fashion', 'home-decor', 'beauty', 'health',
            'sports', 'books', 'toys', 'jewelry', 'furniture',
        ];

        // Popular blog post topics for custom links
        $blogTopics = [
            'best-products', 'product-review', 'comparison', 'how-to', 'top-10',
            'buyers-guide', 'tips-and-tricks', 'sale-alert', 'new-arrivals', 'trending',
        ];

        foreach ($affiliates as $affiliate) {
            // More active affiliates create more short links
            $shortLinkCount = 0;

            if ($affiliate->total_commission > 10000) {
                // Very active affiliates
                $shortLinkCount = rand(15, 25);
            } elseif ($affiliate->total_commission > 5000) {
                // Active affiliates
                $shortLinkCount = rand(8, 15);
            } elseif ($affiliate->total_commission > 1000) {
                // Moderately active affiliates
                $shortLinkCount = rand(5, 10);
            } else {
                // Less active affiliates
                $shortLinkCount = rand(2, 6);
            }

            // Create short links with varied creation dates
            $startDate = Carbon::now()->subMonths(6);
            $endDate = Carbon::now();

            for ($i = 0; $i < $shortLinkCount; $i++) {
                // Determine link type with weighted distribution
                $linkType = $this->getRandomWeightedElement(
                    ['product', 'custom', 'homepage', 'category'],
                    [60, 25, 10, 5]
                );

                $shortCode = strtolower(Str::random(8));
                $title = null;
                $destinationUrl = null;
                $productId = null;

                switch ($linkType) {
                    case 'product':
                        $product = $products->random();
                        $productId = $product->id;

                        // Create more descriptive titles
                        $titleTypes = [
                            'Link to ' . $product->name,
                            'Buy ' . $product->name,
                            'Check out ' . $product->name,
                            $product->name . ' - Special Offer',
                            'Exclusive: ' . $product->name,
                            'Best Deal: ' . $product->name,
                        ];

                        $title = $titleTypes[array_rand($titleTypes)];
                        $destinationUrl = url('products/' . $product->slug);

                        break;

                    case 'custom':
                        // Create more realistic custom links
                        $category = $categories[array_rand($categories)];
                        $topic = $blogTopics[array_rand($blogTopics)];

                        $titleFormats = [
                            'My review of %s products',
                            'Top %s products for 2023',
                            'Best %s deals this month',
                            '%s buying guide',
                            'How to choose the best %s',
                            'Discount alert: %s products on sale',
                        ];

                        $titleFormat = $titleFormats[array_rand($titleFormats)];
                        $title = sprintf($titleFormat, $category);

                        // Create realistic blog URLs
                        $blogDomains = [
                            'myblog.com', 'reviewsite.net', 'bestproducts.org',
                            'topreviews.com', 'dealsandsteals.com', 'expertadvice.net',
                        ];

                        $domain = $blogDomains[array_rand($blogDomains)];
                        $destinationUrl = 'https://www.' . $domain . '/' . $category . '/' . $topic;

                        break;

                    case 'homepage':
                        $title = 'Homepage referral';
                        $destinationUrl = url('/');

                        break;

                    case 'category':
                        $category = $categories[array_rand($categories)];
                        $title = 'Shop ' . ucfirst($category);
                        $destinationUrl = url('product-category/' . $category);

                        break;
                }

                // Create more realistic performance metrics
                // Newer links have fewer clicks, older links have more
                $createdAt = Carbon::now()->subDays(rand(1, 180));
                $daysSinceCreation = $createdAt->diffInDays(Carbon::now());

                // Base clicks depend on how long the link has existed
                $baseClicks = max(10, $daysSinceCreation * rand(1, 5));

                // Add randomness
                $clicks = (int) ($baseClicks * (0.7 + (rand(0, 60) / 100)));

                // Conversion rate varies by link type
                $conversionRate = 0;
                switch ($linkType) {
                    case 'product':
                        $conversionRate = rand(5, 15) / 100; // 5-15% conversion

                        break;
                    case 'custom':
                        $conversionRate = rand(3, 8) / 100; // 3-8% conversion

                        break;
                    case 'homepage':
                        $conversionRate = rand(1, 5) / 100; // 1-5% conversion

                        break;
                    case 'category':
                        $conversionRate = rand(2, 7) / 100; // 2-7% conversion

                        break;
                }

                $conversions = (int) ($clicks * $conversionRate);

                // Create the short link
                $updatedAt = (clone $createdAt)->addDays(rand(1, min(30, $daysSinceCreation)));

                $shortLink = AffiliateShortLink::query()->create([
                    'affiliate_id' => $affiliate->id,
                    'short_code' => $shortCode,
                    'destination_url' => $destinationUrl,
                    'title' => $title,
                    'product_id' => $productId,
                    'clicks' => $clicks,
                    'conversions' => $conversions,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $this->command->info(
                    'Created short link: ' . $shortLink->short_code .
                    ' for affiliate: ' . $affiliate->affiliate_code .
                    ' with ' . $clicks . ' clicks and ' . $conversions . ' conversions'
                );
            }
        }
    }

    protected function seedClicks(): void
    {
        $shortLinks = AffiliateShortLink::query()->get();

        if ($shortLinks->isEmpty()) {
            $this->command->info('No short links found. Skipping clicks seeding.');

            return;
        }

        $faker = $this->fake();

        // More comprehensive country list with weighted distribution
        $countries = [
            'US' => 25, // 25% of traffic
            'UK' => 15,
            'CA' => 10,
            'AU' => 8,
            'DE' => 7,
            'FR' => 6,
            'IN' => 5,
            'JP' => 4,
            'BR' => 3,
            'MX' => 3,
            'ES' => 2,
            'IT' => 2,
            'NL' => 2,
            'SG' => 2,
            'AE' => 1,
            'ZA' => 1,
            'RU' => 1,
            'KR' => 1,
            'CN' => 1,
            'SE' => 1,
        ];

        // Cities by country for more realistic geographic data
        $citiesByCountry = [
            'US' => [
                'New York' => 20, 'Los Angeles' => 15, 'Chicago' => 12, 'Houston' => 10,
                'Phoenix' => 8, 'Philadelphia' => 7, 'San Antonio' => 6, 'San Diego' => 6,
                'Dallas' => 5, 'San Jose' => 5, 'Austin' => 4, 'Seattle' => 2,
            ],
            'UK' => [
                'London' => 40, 'Manchester' => 15, 'Birmingham' => 12, 'Glasgow' => 10,
                'Liverpool' => 8, 'Bristol' => 5, 'Edinburgh' => 5, 'Leeds' => 5,
            ],
            'CA' => [
                'Toronto' => 30, 'Montreal' => 20, 'Vancouver' => 15, 'Calgary' => 10,
                'Ottawa' => 10, 'Edmonton' => 8, 'Winnipeg' => 7,
            ],
            'AU' => [
                'Sydney' => 35, 'Melbourne' => 30, 'Brisbane' => 15, 'Perth' => 10,
                'Adelaide' => 10,
            ],
            'DE' => [
                'Berlin' => 30, 'Munich' => 20, 'Hamburg' => 15, 'Cologne' => 10,
                'Frankfurt' => 10, 'Stuttgart' => 8, 'Dusseldorf' => 7,
            ],
            'FR' => [
                'Paris' => 40, 'Marseille' => 15, 'Lyon' => 15, 'Toulouse' => 10,
                'Nice' => 10, 'Nantes' => 5, 'Strasbourg' => 5,
            ],
            'IN' => [
                'Mumbai' => 25, 'Delhi' => 20, 'Bangalore' => 15, 'Hyderabad' => 10,
                'Chennai' => 10, 'Kolkata' => 10, 'Pune' => 5, 'Ahmedabad' => 5,
            ],
            'JP' => [
                'Tokyo' => 40, 'Osaka' => 20, 'Yokohama' => 10, 'Nagoya' => 10,
                'Sapporo' => 10, 'Fukuoka' => 5, 'Kobe' => 5,
            ],
            'BR' => [
                'Sao Paulo' => 35, 'Rio de Janeiro' => 25, 'Brasilia' => 15,
                'Salvador' => 10, 'Fortaleza' => 8, 'Belo Horizonte' => 7,
            ],
            'MX' => [
                'Mexico City' => 40, 'Guadalajara' => 20, 'Monterrey' => 15,
                'Puebla' => 10, 'Tijuana' => 8, 'Leon' => 7,
            ],
        ];

        // Default cities for countries not in the detailed list
        $defaultCities = [
            'Madrid', 'Barcelona', 'Rome', 'Milan', 'Amsterdam', 'Rotterdam',
            'Singapore', 'Dubai', 'Johannesburg', 'Cape Town', 'Moscow', 'Seoul',
            'Beijing', 'Shanghai', 'Stockholm', 'Gothenburg',
        ];

        // More realistic referrer URLs with weighted distribution
        $referrers = [
            // Search engines (40%)
            'https://www.google.com/search?q=product+review' => 20,
            'https://www.google.com/search?q=best+deals' => 10,
            'https://www.bing.com/search?q=best+products' => 5,
            'https://duckduckgo.com/?q=buy+online' => 3,
            'https://www.yahoo.com/search?p=discount+products' => 2,

            // Social media (35%)
            'https://www.facebook.com/' => 12,
            'https://www.instagram.com/' => 8,
            'https://twitter.com/' => 5,
            'https://www.pinterest.com/' => 4,
            'https://www.youtube.com/' => 3,
            'https://www.linkedin.com/' => 2,
            'https://www.reddit.com/r/deals/' => 1,

            // Direct traffic (15%)
            null => 15,

            // Other sources (10%)
            'https://www.dealsites.com/' => 3,
            'https://www.couponwebsite.com/' => 2,
            'https://www.bloggerreview.com/' => 2,
            'https://www.emailcampaign.com/' => 2,
            'https://www.pricecomparison.com/' => 1,
        ];

        // More comprehensive user agents with device information
        $userAgents = [
            // Desktop browsers (60%)
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36' => 20,
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15' => 15,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0' => 10,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59' => 8,
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36' => 7,

            // Mobile browsers (40%)
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1' => 15,
            'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1' => 8,
            'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36' => 7,
            'Mozilla/5.0 (Linux; Android 10; SM-A505F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36' => 5,
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/91.0.4472.80 Mobile/15E148 Safari/604.1' => 5,
        ];

        // Process each short link
        foreach ($shortLinks as $shortLink) {
            // Create clicks based on the shortLink's clicks count
            // For links with many clicks, create a representative sample
            $clicksToCreate = min($shortLink->clicks, 200); // Increased limit for better data

            // Create a date distribution for clicks
            // Newer links have more recent clicks, older links have clicks spread out
            $createdAt = $shortLink->created_at;
            $daysSinceCreation = $createdAt->diffInDays(Carbon::now());

            // Create a weighted distribution of dates
            $clickDates = [];
            for ($i = 0; $i < $clicksToCreate; $i++) {
                // More recent dates have higher probability
                $daysAgo = rand(0, $daysSinceCreation);
                $weight = $daysSinceCreation - $daysAgo; // More recent = higher weight
                $clickDates[] = [
                    'date' => Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'weight' => $weight,
                ];
            }

            // Sort by date
            usort($clickDates, function ($a, $b) {
                return $a['date']->timestamp - $b['date']->timestamp;
            });

            // Create clicks with proper distribution
            for ($i = 0; $i < $clicksToCreate; $i++) {
                // Determine if this click converted
                $isConverted = $i < $shortLink->conversions;

                // Get a date for this click
                $createdAt = $clickDates[$i]['date'];

                // Conversion happens a bit later
                $conversionTime = $isConverted ? (clone $createdAt)->addMinutes(rand(1, 60)) : null;

                // Select country based on weighted distribution
                $country = $this->getRandomWeightedElement(array_keys($countries), array_values($countries));

                // Select city based on country
                $city = '';
                if (isset($citiesByCountry[$country])) {
                    $city = $this->getRandomWeightedElement(
                        array_keys($citiesByCountry[$country]),
                        array_values($citiesByCountry[$country])
                    );
                } else {
                    $city = $defaultCities[array_rand($defaultCities)];
                }

                // Select referrer based on weighted distribution
                $referrer = $this->getRandomWeightedElement(
                    array_keys($referrers),
                    array_values($referrers)
                );

                // Select user agent based on weighted distribution
                $userAgent = $this->getRandomWeightedElement(
                    array_keys($userAgents),
                    array_values($userAgents)
                );

                // Create the click record
                $click = AffiliateClick::query()->create([
                    'affiliate_id' => $shortLink->affiliate_id,
                    'short_link_id' => $shortLink->id,
                    'ip_address' => $faker->ipv4(),
                    'user_agent' => $userAgent,
                    'referrer_url' => $referrer,
                    'landing_url' => $shortLink->destination_url,
                    'converted' => $isConverted,
                    'conversion_time' => $conversionTime,
                    'country' => $country,
                    'city' => $city,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $this->command->info(
                'Created ' . $clicksToCreate . ' clicks for short link: ' . $shortLink->short_code
            );
        }
    }

    /**
     * Create dummy orders for commission references
     */
    protected function createDummyOrders(int $count = 10): Collection
    {
        $faker = $this->fake();
        $orders = collect();

        // Get some customers for the orders
        $customers = Customer::query()->limit(5)->get();

        if ($customers->isEmpty()) {
            // Create some dummy customers if none exist
            for ($i = 0; $i < 5; $i++) {
                $customers[] = Customer::query()->create([
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'password' => bcrypt('password'),
                ]);
            }
        }

        // Create dummy orders
        for ($i = 0; $i < $count; $i++) {
            $customer = $customers->random();
            $amount = rand(100, 1000);

            $order = Order::query()->create([
                'code' => 'ORD-' . strtoupper(Str::random(6)),
                'user_id' => $customer->id,
                'shipping_amount' => rand(10, 50),
                'discount_amount' => 0,
                'tax_amount' => $amount * 0.1,
                'amount' => $amount,
                'sub_total' => $amount - ($amount * 0.1),
                'coupon_code' => null,
                'status' => 'completed',
                'is_finished' => 1,
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            $orders->push($order);
        }

        return $orders;
    }

    protected function seedCoupons(): void
    {
        $affiliates = Affiliate::query()->where('status', AffiliateStatusEnum::APPROVED)->get();

        if ($affiliates->isEmpty()) {
            $this->command->info('No published affiliates found. Skipping coupons seeding.');

            return;
        }

        $faker = $this->fake();
        $discountTypes = ['percentage', 'fixed'];

        // Coupon themes for more realistic descriptions
        $couponThemes = [
            'welcome' => [
                'Welcome discount for new customers',
                'First-time buyer special offer',
                'New customer exclusive discount',
                'Welcome aboard special',
                'First purchase bonus',
            ],
            'seasonal' => [
                'Summer sale special',
                'Winter clearance discount',
                'Spring collection offer',
                'Fall favorites discount',
                'Holiday season special',
            ],
            'flash' => [
                'Flash sale discount',
                'Limited time offer',
                '24-hour special',
                'Weekend flash deal',
                'Quick deal discount',
            ],
            'loyalty' => [
                'Loyal customer appreciation',
                'Returning customer bonus',
                'Customer loyalty reward',
                'Thank you discount',
                'Valued customer special',
            ],
            'category' => [
                'Special discount on electronics',
                'Fashion items discount',
                'Home goods special offer',
                'Beauty products discount',
                'Sports equipment special',
            ],
        ];

        foreach ($affiliates as $affiliate) {
            // More active affiliates have more coupons
            $couponCount = 0;

            if ($affiliate->total_commission > 10000) {
                // Very active affiliates
                $couponCount = rand(4, 6);
            } elseif ($affiliate->total_commission > 5000) {
                // Active affiliates
                $couponCount = rand(2, 4);
            } else {
                // Less active affiliates
                $couponCount = rand(1, 2);
            }

            // Create coupons with varied creation dates
            $startDate = Carbon::now()->subMonths(6);
            $endDate = Carbon::now();

            for ($i = 0; $i < $couponCount; $i++) {
                // More realistic discount distribution
                // More established affiliates get better discounts
                if ($affiliate->total_commission > 8000) {
                    $discountType = $this->getRandomWeightedElement(
                        $discountTypes,
                        [60, 40] // 60% percentage, 40% fixed
                    );

                    $discountAmount = $discountType === 'percentage' ?
                        rand(10, 30) : // 10-30% discount
                        rand(15, 50);  // $15-$50 discount
                } else {
                    $discountType = $this->getRandomWeightedElement(
                        $discountTypes,
                        [70, 30] // 70% percentage, 30% fixed
                    );

                    $discountAmount = $discountType === 'percentage' ?
                        rand(5, 20) : // 5-20% discount
                        rand(5, 30);  // $5-$30 discount
                }

                // Create a discount in the ec_discounts table first
                // More descriptive coupon codes
                $codePrefix = strtoupper($affiliate->affiliate_code);
                $codeSuffix = '';

                // Add theme to code
                $themeKeys = array_keys($couponThemes);
                $theme = $themeKeys[array_rand($themeKeys)];

                switch ($theme) {
                    case 'welcome':
                        $codeSuffix = 'WELCOME' . rand(10, 99);

                        break;
                    case 'seasonal':
                        $seasons = ['SPRING', 'SUMMER', 'FALL', 'WINTER'];
                        $codeSuffix = $seasons[array_rand($seasons)] . rand(10, 99);

                        break;
                    case 'flash':
                        $codeSuffix = 'FLASH' . rand(10, 99);

                        break;
                    case 'loyalty':
                        $codeSuffix = 'LOYAL' . rand(10, 99);

                        break;
                    case 'category':
                        $categories = ['TECH', 'FASHION', 'HOME', 'BEAUTY', 'SPORT'];
                        $codeSuffix = $categories[array_rand($categories)] . rand(10, 99);

                        break;
                }

                $code = $codePrefix . '_' . $codeSuffix;

                // Create realistic dates
                $createdAt = Carbon::now()->subDays(rand(30, 180));
                $startDate = (clone $createdAt)->addDays(rand(1, 5));
                $expiresAt = (clone $startDate)->addDays(rand(30, 90));
                $updatedAt = (clone $createdAt)->addDays(rand(1, 10));

                // Usage limits based on discount value
                $quantity = $discountType === 'percentage' ?
                    ($discountAmount > 20 ? rand(10, 30) : rand(30, 100)) : // Higher % discounts have lower quantity
                    ($discountAmount > 30 ? rand(10, 30) : rand(30, 100));  // Higher $ discounts have lower quantity

                // Total used based on how long the coupon has existed
                $daysSinceCreation = $createdAt->diffInDays(Carbon::now());
                $maxPossibleUsed = min($quantity, (int) ($daysSinceCreation / 7)); // Roughly 1 use per week
                $totalUsed = rand(0, $maxPossibleUsed);

                // Create a discount record
                $discount = Discount::query()->create([
                    'title' => $couponThemes[$theme][array_rand($couponThemes[$theme])] . ' by ' . $affiliate->customer->name,
                    'code' => $code,
                    'value' => $discountAmount,
                    'type' => 'coupon',
                    'type_option' => $discountType === 'percentage' ? 'percentage' : 'amount',
                    'target' => 'all-orders',
                    'start_date' => $startDate,
                    'end_date' => $expiresAt,
                    'quantity' => $quantity,
                    'total_used' => $totalUsed,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                // Create more detailed descriptions
                $descriptions = [
                    'Exclusive ' . $discountAmount . ($discountType === 'percentage' ? '% off' : '$ off') . ' your purchase with this special affiliate coupon.',
                    'Save ' . $discountAmount . ($discountType === 'percentage' ? '% on' : '$ on') . ' your order with this limited-time affiliate offer.',
                    'Get ' . $discountAmount . ($discountType === 'percentage' ? '% discount' : '$ discount') . ' when you use this affiliate promotion code.',
                    'Special offer: ' . $discountAmount . ($discountType === 'percentage' ? '% off' : '$ off') . ' your purchase through our affiliate program.',
                    'Exclusive affiliate discount: ' . $discountAmount . ($discountType === 'percentage' ? '% off' : '$ off') . ' your next order.',
                ];

                // Now create the affiliate coupon
                $affiliateCoupon = AffiliateCoupon::query()->create([
                    'affiliate_id' => $affiliate->id,
                    'discount_id' => $discount->id,
                    'code' => $code,
                    'description' => $descriptions[array_rand($descriptions)],
                    'discount_amount' => $discountAmount,
                    'discount_type' => $discountType,
                    'expires_at' => $expiresAt,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $this->command->info(
                    'Created coupon: ' . $affiliateCoupon->code .
                    ' for affiliate: ' . $affiliate->affiliate_code .
                    ' with ' . $discountAmount . ($discountType === 'percentage' ? '%' : '$') . ' discount'
                );
            }
        }
    }
}
