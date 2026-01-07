<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable_quote_system')->default(true);
            $table->json('quote_categories')->nullable(); // Category IDs that should show quote form
            $table->json('quote_products')->nullable(); // Specific product IDs that should show quote form
            $table->boolean('quote_for_no_price_products')->default(true);
            $table->json('form_fields')->nullable(); // Configure which form fields to show
            $table->json('budget_ranges')->nullable(); // Custom budget ranges
            $table->json('timeline_options')->nullable(); // Custom timeline options
            $table->json('room_types')->nullable(); // Custom room types for tiles
            $table->string('admin_email')->nullable(); // Email to receive quote notifications
            $table->boolean('send_customer_confirmation')->default(true);
            $table->boolean('send_admin_notification')->default(true);
            $table->text('customer_email_template')->nullable();
            $table->text('admin_email_template')->nullable();
            $table->string('response_time')->default('24 hours');
            $table->text('quote_page_content')->nullable(); // Custom content for quote page
            $table->boolean('require_login')->default(false);
            $table->integer('max_file_uploads')->default(5);
            $table->string('allowed_file_types')->default('jpg,jpeg,png,pdf,doc,docx');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('quote_settings')->insert([
            'enable_quote_system' => true,
            'quote_for_no_price_products' => true,
            'form_fields' => json_encode([
                'customer_name' => ['enabled' => true, 'required' => true, 'label' => 'Full Name'],
                'customer_email' => ['enabled' => true, 'required' => true, 'label' => 'Email Address'],
                'customer_phone' => ['enabled' => true, 'required' => false, 'label' => 'Phone Number'],
                'customer_company' => ['enabled' => true, 'required' => false, 'label' => 'Company Name'],
                'quantity' => ['enabled' => true, 'required' => true, 'label' => 'Quantity Needed'],
                'area_size' => ['enabled' => true, 'required' => true, 'label' => 'Area Size', 'for_tiles' => true],
                'room_type' => ['enabled' => true, 'required' => false, 'label' => 'Room Type', 'for_tiles' => true],
                'installation_needed' => ['enabled' => true, 'required' => false, 'label' => 'Installation Required?', 'for_tiles' => true],
                'budget_range' => ['enabled' => true, 'required' => false, 'label' => 'Budget Range'],
                'timeline' => ['enabled' => true, 'required' => false, 'label' => 'Project Timeline'],
                'project_description' => ['enabled' => true, 'required' => false, 'label' => 'Project Description'],
                'special_requirements' => ['enabled' => true, 'required' => false, 'label' => 'Special Requirements', 'for_tiles' => true],
                'newsletter_subscribe' => ['enabled' => true, 'required' => false, 'label' => 'Subscribe to Newsletter']
            ]),
            'budget_ranges' => json_encode([
                'under_1000' => 'Under $1,000',
                '1000_5000' => '$1,000 - $5,000',
                '5000_10000' => '$5,000 - $10,000',
                '10000_25000' => '$10,000 - $25,000',
                'over_25000' => 'Over $25,000'
            ]),
            'timeline_options' => json_encode([
                'urgent' => 'ASAP (1-2 weeks)',
                'month' => 'Within a month',
                'quarter' => 'Within 3 months',
                'flexible' => 'Flexible timing'
            ]),
            'room_types' => json_encode([
                'bathroom' => 'Bathroom',
                'kitchen' => 'Kitchen',
                'living_room' => 'Living Room',
                'bedroom' => 'Bedroom',
                'commercial' => 'Commercial Space',
                'outdoor' => 'Outdoor/Patio',
                'other' => 'Other'
            ]),
            'response_time' => '24 hours',
            'send_customer_confirmation' => true,
            'send_admin_notification' => true,
            'require_login' => false,
            'max_file_uploads' => 5,
            'allowed_file_types' => 'jpg,jpeg,png,pdf,doc,docx',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_settings');
    }
};