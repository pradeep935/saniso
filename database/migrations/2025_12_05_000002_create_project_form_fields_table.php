<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label');
            $table->string('type');
            $table->string('placeholder')->nullable();
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('enabled')->default(true);
            $table->string('validation_rules')->nullable();
            $table->string('css_classes')->nullable();
            $table->string('default_value')->nullable();
            $table->string('help_text')->nullable();
            $table->string('field_width')->default('col-12');
            $table->integer('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->json('field_attributes')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'sort_order']);
        });

        // Insert default fields
        $defaultFields = [
            [
                'name' => 'customer_name',
                'label' => 'Full Name',
                'type' => 'text',
                'placeholder' => 'Enter your full name',
                'required' => true,
                'enabled' => true,
                'field_width' => 'col-md-6',
                'sort_order' => 10
            ],
            [
                'name' => 'customer_email',
                'label' => 'Email Address',
                'type' => 'email',
                'placeholder' => 'Enter your email address',
                'required' => true,
                'enabled' => true,
                'field_width' => 'col-md-6',
                'sort_order' => 20
            ],
            [
                'name' => 'customer_phone',
                'label' => 'Phone Number',
                'type' => 'tel',
                'placeholder' => 'Enter your phone number',
                'required' => false,
                'enabled' => true,
                'field_width' => 'col-md-6',
                'sort_order' => 30
            ],
            [
                'name' => 'customer_company',
                'label' => 'Company',
                'type' => 'text',
                'placeholder' => 'Enter your company name',
                'required' => false,
                'enabled' => true,
                'field_width' => 'col-md-6',
                'sort_order' => 40
            ],
            [
                'name' => 'budget_range',
                'label' => 'Budget Range',
                'type' => 'select',
                'placeholder' => 'Select budget range...',
                'required' => false,
                'enabled' => true,
                'field_width' => 'col-md-6',
                'sort_order' => 50,
                'options' => json_encode([
                    ['label' => 'Under $5,000', 'value' => 'under_5000'],
                    ['label' => '$5,000 - $10,000', 'value' => '5000_10000'],
                    ['label' => '$10,000 - $25,000', 'value' => '10000_25000'],
                    ['label' => '$25,000 - $50,000', 'value' => '25000_50000'],
                    ['label' => '$50,000 - $100,000', 'value' => '50000_100000'],
                    ['label' => 'Over $100,000', 'value' => 'over_100000']
                ])
            ],
            [
                'name' => 'deadline',
                'label' => 'Project Deadline',
                'type' => 'select',
                'placeholder' => 'Select deadline...',
                'required' => false,
                'enabled' => true,
                'field_width' => 'col-md-6',
                'sort_order' => 60,
                'options' => json_encode([
                    ['label' => 'ASAP (1-4 weeks)', 'value' => 'asap'],
                    ['label' => 'Within 1 month', 'value' => 'month'],
                    ['label' => 'Within 3 months', 'value' => 'quarter'],
                    ['label' => 'Within 6 months', 'value' => 'half_year'],
                    ['label' => 'Flexible timing', 'value' => 'flexible']
                ])
            ],
            [
                'name' => 'project_files',
                'label' => 'Upload Files',
                'type' => 'file',
                'help_text' => 'Accepted formats: JPG, PNG, PDF, DOC, DOCX, ZIP. Max 5 files, 10MB each.',
                'required' => false,
                'enabled' => true,
                'field_width' => 'col-12',
                'sort_order' => 70,
                'field_attributes' => json_encode([
                    'accept' => '.jpg,.jpeg,.png,.pdf,.doc,.docx,.zip',
                    'multiple' => true
                ])
            ],
            [
                'name' => 'project_description',
                'label' => 'Project Description',
                'type' => 'textarea',
                'placeholder' => 'Please describe your project in detail...',
                'required' => true,
                'enabled' => true,
                'field_width' => 'col-12',
                'sort_order' => 80,
                'field_attributes' => json_encode(['rows' => '5'])
            ],
            [
                'name' => 'newsletter_subscribe',
                'label' => 'Subscribe to our newsletter for updates and offers',
                'type' => 'checkbox',
                'required' => false,
                'enabled' => true,
                'field_width' => 'col-12',
                'sort_order' => 90
            ]
        ];

        foreach ($defaultFields as $field) {
            \Botble\Ecommerce\Models\ProjectFormField::create($field);
        }
    }

    public function down()
    {
        Schema::dropIfExists('project_form_fields');
    }
};