<?php

namespace Botble\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteSettings extends Model
{
    use HasFactory;

    protected $table = 'quote_settings';

    protected $fillable = [
        'enable_quote_system',
        'quote_categories',
        'quote_products',
        'quote_for_no_price_products',
        'form_fields',
        'budget_ranges',
        'timeline_options',
        'room_types',
        'admin_email',
        'send_customer_confirmation',
        'send_admin_notification',
        'customer_email_template',
        'admin_email_template',
        'response_time',
        'quote_page_content',
        'require_login',
        'max_file_uploads',
        'allowed_file_types',
        'enable_tax_on_quotes',
        'quote_prices_include_tax',
        'quote_tax_calculation'
    ];

    protected $casts = [
        'enable_quote_system' => 'boolean',
        'quote_categories' => 'array',
        'quote_products' => 'array',
        'quote_for_no_price_products' => 'boolean',
        'form_fields' => 'array',
        'budget_ranges' => 'array',
        'timeline_options' => 'array',
        'room_types' => 'array',
        'send_customer_confirmation' => 'boolean',
        'send_admin_notification' => 'boolean',
        'require_login' => 'boolean',
        'max_file_uploads' => 'integer',
        'enable_tax_on_quotes' => 'boolean',
        'quote_prices_include_tax' => 'boolean'
    ];

    /**
     * Get the singleton instance of quote settings.
     */
    public static function getInstance()
    {
        return self::first() ?? self::create([]);
    }

    /**
     * Check if quote system is enabled.
     */
    public static function isEnabled(): bool
    {
        return self::getInstance()->enable_quote_system ?? true;
    }

    /**
     * Check if a product should show quote form.
     */
    public static function shouldShowQuoteForm($product): bool
    {
        $settings = self::getInstance();
        
        if (!$settings->enable_quote_system) {
            return false;
        }

        // Check if product has no price and setting is enabled
        if ($settings->quote_for_no_price_products && 
            (!$product->front_sale_price && !$product->price)) {
            return true;
        }

        // Check if product is in quote products list
        if ($settings->quote_products && 
            in_array($product->id, $settings->quote_products)) {
            return true;
        }

        // Check if product's category is in quote categories
        if ($settings->quote_categories) {
            foreach ($product->categories as $category) {
                if (in_array($category->id, $settings->quote_categories)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get enabled form fields.
     */
    public function getEnabledFormFields(): array
    {
        if (!$this->form_fields) {
            return [];
        }

        return array_filter($this->form_fields, function ($field) {
            return $field['enabled'] ?? false;
        });
    }

    /**
     * Get required form fields.
     */
    public function getRequiredFormFields(): array
    {
        if (!$this->form_fields) {
            return [];
        }

        return array_filter($this->form_fields, function ($field) {
            return ($field['enabled'] ?? false) && ($field['required'] ?? false);
        });
    }

    /**
     * Get form fields for tiles products.
     */
    public function getTilesFormFields(): array
    {
        if (!$this->form_fields) {
            return [];
        }

        return array_filter($this->form_fields, function ($field) {
            return ($field['enabled'] ?? false) && ($field['for_tiles'] ?? false);
        });
    }

    /**
     * Get admin notification email.
     */
    public function getAdminEmailAttribute(): ?string
    {
        return $this->attributes['admin_email'] ?? config('mail.from.address');
    }

    /**
     * Get default form fields structure.
     */
    public static function getDefaultFormFields(): array
    {
        return [
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
        ];
    }
}