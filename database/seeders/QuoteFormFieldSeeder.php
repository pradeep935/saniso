<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuoteFormField;

class QuoteFormFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFields = [
            [
                'name' => 'product_name',
                'label' => 'Product Name',
                'type' => 'text',
                'placeholder' => 'Enter product name',
                'required' => true,
                'enabled' => true,
                'sort_order' => 1,
                'field_width' => 'col-12',
                'help_text' => 'Please specify the product you are interested in'
            ],
            [
                'name' => 'customer_name',
                'label' => 'Your Name',
                'type' => 'text',
                'placeholder' => 'Enter your full name',
                'required' => true,
                'enabled' => true,
                'sort_order' => 2,
                'field_width' => 'col-6',
                'validation_rules' => 'min:2'
            ],
            [
                'name' => 'customer_email',
                'label' => 'Email Address',
                'type' => 'email',
                'placeholder' => 'Enter your email address',
                'required' => true,
                'enabled' => true,
                'sort_order' => 3,
                'field_width' => 'col-6',
                'help_text' => 'We will send the quote to this email address'
            ],
            [
                'name' => 'customer_phone',
                'label' => 'Phone Number',
                'type' => 'tel',
                'placeholder' => 'Enter your phone number',
                'required' => false,
                'enabled' => true,
                'sort_order' => 4,
                'field_width' => 'col-6'
            ],
            [
                'name' => 'company_name',
                'label' => 'Company Name',
                'type' => 'text',
                'placeholder' => 'Enter your company name',
                'required' => false,
                'enabled' => true,
                'sort_order' => 5,
                'field_width' => 'col-6'
            ],
            [
                'name' => 'quantity',
                'label' => 'Quantity Required',
                'type' => 'number',
                'placeholder' => 'Enter quantity',
                'required' => true,
                'enabled' => true,
                'sort_order' => 6,
                'field_width' => 'col-4',
                'field_attributes' => ['min' => 1, 'step' => 1],
                'default_value' => '1'
            ],
            [
                'name' => 'budget_range',
                'label' => 'Budget Range',
                'type' => 'select',
                'required' => false,
                'enabled' => true,
                'sort_order' => 7,
                'field_width' => 'col-4',
                'placeholder' => 'Select budget range',
                'options' => [
                    ['label' => 'Under €100', 'value' => 'under_100'],
                    ['label' => '€100 - €500', 'value' => '100_500'],
                    ['label' => '€500 - €1000', 'value' => '500_1000'],
                    ['label' => '€1000 - €5000', 'value' => '1000_5000'],
                    ['label' => 'Over €5000', 'value' => 'over_5000']
                ]
            ],
            [
                'name' => 'urgency',
                'label' => 'How urgent is this request?',
                'type' => 'radio',
                'required' => false,
                'enabled' => true,
                'sort_order' => 8,
                'field_width' => 'col-4',
                'options' => [
                    ['label' => 'Not urgent', 'value' => 'not_urgent'],
                    ['label' => 'Within a week', 'value' => 'week'],
                    ['label' => 'ASAP', 'value' => 'asap']
                ]
            ],
            [
                'name' => 'requirements',
                'label' => 'Additional Requirements',
                'type' => 'textarea',
                'placeholder' => 'Please describe any specific requirements or questions',
                'required' => false,
                'enabled' => true,
                'sort_order' => 9,
                'field_width' => 'col-12',
                'field_attributes' => ['rows' => 4]
            ],
            [
                'name' => 'preferred_contact',
                'label' => 'Preferred Contact Method',
                'type' => 'checkbox',
                'required' => false,
                'enabled' => true,
                'sort_order' => 10,
                'field_width' => 'col-6',
                'options' => [
                    ['label' => 'Email', 'value' => 'email'],
                    ['label' => 'Phone', 'value' => 'phone'],
                    ['label' => 'WhatsApp', 'value' => 'whatsapp']
                ]
            ],
            [
                'name' => 'newsletter_subscribe',
                'label' => 'Subscribe to our newsletter for updates and special offers',
                'type' => 'checkbox',
                'required' => false,
                'enabled' => true,
                'sort_order' => 11,
                'field_width' => 'col-6',
                'default_value' => '0'
            ]
        ];

        foreach ($defaultFields as $field) {
            QuoteFormField::create($field);
        }
    }
}
