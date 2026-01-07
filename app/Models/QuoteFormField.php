<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteFormField extends Model
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
     * Field types available for quote forms
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
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons',
            'file' => 'File Upload',
            'date' => 'Date Picker',
            'time' => 'Time Picker',
            'datetime-local' => 'Date & Time',
            'url' => 'URL Input',
            'password' => 'Password Input',
            'range' => 'Range Slider',
            'color' => 'Color Picker',
            'hidden' => 'Hidden Field',
            'heading' => 'Section Heading',
            'divider' => 'Divider/Separator',
            'html' => 'Custom HTML'
        ];
    }

    /**
     * Get enabled fields ordered by sort_order
     */
    public static function getEnabledFields()
    {
        return self::where('enabled', true)
                   ->orderBy('sort_order')
                   ->get();
    }

    /**
     * Generate HTML for this field
     */
    public function renderField()
    {
        $html = '';
        $attributes = $this->field_attributes ?: [];
        $style = $this->style_config ?: [];

        // Build field attributes string
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= " {$key}=\"{$value}\"";
        }

        // Add CSS classes
        $classes = 'form-control ' . ($this->css_classes ?: '');
        if ($this->required) {
            $classes .= ' required';
        }

        switch ($this->type) {
            case 'text':
            case 'email':
            case 'number':
            case 'tel':
            case 'url':
            case 'password':
            case 'date':
            case 'time':
            case 'datetime-local':
            case 'color':
                $html = "<input type=\"{$this->type}\" name=\"{$this->name}\" id=\"{$this->name}\" class=\"{$classes}\" placeholder=\"{$this->placeholder}\" value=\"{$this->default_value}\"{$attrString}" . ($this->required ? ' required' : '') . ">";
                break;

            case 'textarea':
                $html = "<textarea name=\"{$this->name}\" id=\"{$this->name}\" class=\"{$classes}\" placeholder=\"{$this->placeholder}\"{$attrString}" . ($this->required ? ' required' : '') . ">{$this->default_value}</textarea>";
                break;

            case 'select':
                $html = "<select name=\"{$this->name}\" id=\"{$this->name}\" class=\"{$classes}\"{$attrString}" . ($this->required ? ' required' : '') . ">";
                if ($this->placeholder) {
                    $html .= "<option value=\"\">{$this->placeholder}</option>";
                }
                if ($this->options) {
                    foreach ($this->options as $option) {
                        $selected = ($option['value'] == $this->default_value) ? ' selected' : '';
                        $html .= "<option value=\"{$option['value']}\"{$selected}>{$option['label']}</option>";
                    }
                }
                $html .= "</select>";
                break;

            case 'checkbox':
                if ($this->options) {
                    $html = '';
                    foreach ($this->options as $option) {
                        $checked = (in_array($option['value'], (array)$this->default_value)) ? ' checked' : '';
                        $html .= "<div class=\"form-check\">";
                        $html .= "<input type=\"checkbox\" name=\"{$this->name}[]\" value=\"{$option['value']}\" id=\"{$this->name}_{$option['value']}\" class=\"form-check-input\"{$checked}>";
                        $html .= "<label class=\"form-check-label\" for=\"{$this->name}_{$option['value']}\">{$option['label']}</label>";
                        $html .= "</div>";
                    }
                } else {
                    $checked = $this->default_value ? ' checked' : '';
                    $html = "<input type=\"checkbox\" name=\"{$this->name}\" id=\"{$this->name}\" class=\"form-check-input\" value=\"1\"{$checked}>";
                }
                break;

            case 'radio':
                $html = '';
                if ($this->options) {
                    foreach ($this->options as $option) {
                        $checked = ($option['value'] == $this->default_value) ? ' checked' : '';
                        $html .= "<div class=\"form-check\">";
                        $html .= "<input type=\"radio\" name=\"{$this->name}\" value=\"{$option['value']}\" id=\"{$this->name}_{$option['value']}\" class=\"form-check-input\"{$checked}>";
                        $html .= "<label class=\"form-check-label\" for=\"{$this->name}_{$option['value']}\">{$option['label']}</label>";
                        $html .= "</div>";
                    }
                }
                break;

            case 'file':
                $html = "<input type=\"file\" name=\"{$this->name}\" id=\"{$this->name}\" class=\"{$classes}\"{$attrString}" . ($this->required ? ' required' : '') . ">";
                break;

            case 'range':
                $html = "<input type=\"range\" name=\"{$this->name}\" id=\"{$this->name}\" class=\"form-range\" value=\"{$this->default_value}\"{$attrString}>";
                break;

            case 'hidden':
                $html = "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$this->default_value}\">";
                break;

            case 'heading':
                $level = $attributes['level'] ?? 'h3';
                $html = "<{$level} class=\"form-heading\">{$this->label}</{$level}>";
                break;

            case 'divider':
                $html = "<hr class=\"form-divider\">";
                break;

            case 'html':
                $html = $this->default_value; // Custom HTML content
                break;
        }

        return $html;
    }

    /**
     * Get validation rules for this field
     */
    public function getValidationRules()
    {
        $rules = [];
        
        if ($this->required && !in_array($this->type, ['heading', 'divider', 'html'])) {
            $rules[] = 'required';
        }

        // Add type-specific validation
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'file':
                $rules[] = 'file';
                if ($this->field_attributes) {
                    if (isset($this->field_attributes['accept'])) {
                        $rules[] = 'mimes:' . str_replace('.', '', $this->field_attributes['accept']);
                    }
                    if (isset($this->field_attributes['max_size'])) {
                        $rules[] = 'max:' . $this->field_attributes['max_size'];
                    }
                }
                break;
        }

        // Add custom validation rules
        if ($this->validation_rules) {
            $customRules = explode('|', $this->validation_rules);
            $rules = array_merge($rules, $customRules);
        }

        return implode('|', $rules);
    }
}