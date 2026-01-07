<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectQuoteFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'type',
        'options',
        'placeholder',
        'description',
        'required',
        'enabled',
        'sort_order',
        'validation_rules',
        'css_classes',
        'field_attributes',
        'style_config',
        'default_value',
        'help_text',
        'field_width'
    ];

    protected $casts = [
        'options' => 'array',
        'field_attributes' => 'array',
        'style_config' => 'array',
        'required' => 'boolean',
        'enabled' => 'boolean'
    ];

    /**
     * Field types available for project quote forms
     */
    public static function getFieldTypes()
    {
        return [
            'text' => 'Text Input',
            'email' => 'Email Input',
            'number' => 'Number Input',
            'tel' => 'Phone Number',
            'textarea' => 'Text Area',
            'select' => 'Select Dropdown',
            'radio' => 'Radio Buttons',
            'checkbox' => 'Checkboxes',
            'file' => 'File Upload',
            'date' => 'Date Picker',
            'range' => 'Range Slider',
            'url' => 'URL Input',
            'hidden' => 'Hidden Field'
        ];
    }

    /**
     * Get enabled fields for display
     */
    public static function getEnabledFields()
    {
        return self::where('enabled', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get validation rules array
     */
    public function getValidationRulesArray()
    {
        if (!$this->validation_rules) {
            return [];
        }

        return explode('|', $this->validation_rules);
    }

    /**
     * Get CSS classes as string
     */
    public function getCssClassesString()
    {
        return $this->css_classes ?: '';
    }

    /**
     * Get field attributes for HTML
     */
    public function getFieldAttributesString()
    {
        if (!$this->field_attributes || !is_array($this->field_attributes)) {
            return '';
        }

        $attributes = [];
        foreach ($this->field_attributes as $key => $value) {
            $attributes[] = $key . '="' . htmlspecialchars($value) . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Get default options for common field types
     */
    public static function getDefaultOptions($type)
    {
        switch ($type) {
            case 'select':
            case 'radio':
            case 'checkbox':
                return [
                    ['value' => 'option1', 'label' => 'Option 1'],
                    ['value' => 'option2', 'label' => 'Option 2']
                ];
            case 'range':
                return [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1
                ];
            default:
                return [];
        }
    }

    /**
     * Get budget range options
     */
    public static function getBudgetRanges()
    {
        return [
            '0-5000' => 'Under €5,000',
            '5000-10000' => '€5,000 - €10,000',
            '10000-25000' => '€10,000 - €25,000',
            '25000-50000' => '€25,000 - €50,000',
            '50000-100000' => '€50,000 - €100,000',
            '100000+' => 'Over €100,000',
            'discuss' => 'Prefer to discuss'
        ];
    }

    /**
     * Get timeline options
     */
    public static function getTimelineOptions()
    {
        return [
            'asap' => 'As soon as possible',
            '1-2weeks' => '1-2 weeks',
            '1month' => 'Within 1 month',
            '2-3months' => '2-3 months',
            '3-6months' => '3-6 months',
            '6months+' => 'More than 6 months',
            'flexible' => 'Timeline is flexible'
        ];
    }

    /**
     * Get project type options
     */
    public static function getProjectTypeOptions()
    {
        return [
            'residential' => 'Residential',
            'commercial' => 'Commercial',
            'industrial' => 'Industrial',
            'hospitality' => 'Hospitality',
            'retail' => 'Retail',
            'office' => 'Office',
            'healthcare' => 'Healthcare',
            'education' => 'Education',
            'other' => 'Other'
        ];
    }
}