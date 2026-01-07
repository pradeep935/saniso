<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;

class ProjectFormField extends BaseModel
{
    protected $table = 'project_form_fields';

    protected $fillable = [
        'name',
        'label',
        'type',
        'placeholder',
        'description',
        'required',
        'enabled',
        'validation_rules',
        'css_classes',
        'default_value',
        'help_text',
        'field_width',
        'sort_order',
        'options',
        'field_attributes'
    ];

    protected $casts = [
        'required' => 'boolean',
        'enabled' => 'boolean',
        'options' => 'array',
        'field_attributes' => 'array'
    ];

    public static function getFieldTypes()
    {
        return [
            'text' => 'Text Input',
            'email' => 'Email',
            'tel' => 'Phone',
            'number' => 'Number',
            'select' => 'Dropdown',
            'textarea' => 'Textarea',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
            'file' => 'File Upload',
            'date' => 'Date',
            'time' => 'Time',
            'url' => 'URL',
            'heading' => 'Heading',
            'divider' => 'Divider',
            'html' => 'HTML Content'
        ];
    }

    public static function getEnabledFields()
    {
        return self::where('enabled', true)
                  ->orderBy('sort_order')
                  ->get();
    }

    public function getValidationRules()
    {
        $rules = [];
        
        if ($this->required && !in_array($this->type, ['heading', 'divider', 'html'])) {
            $rules[] = 'required';
        }

        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'tel':
                $rules[] = 'string|max:20';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'file':
                $rules[] = 'file|max:10240'; // 10MB
                if ($this->field_attributes && isset($this->field_attributes['accept'])) {
                    $rules[] = 'mimes:' . $this->field_attributes['accept'];
                }
                break;
            case 'text':
            case 'textarea':
                $rules[] = 'string|max:1000';
                break;
            case 'select':
            case 'radio':
                if ($this->options) {
                    $validValues = collect($this->options)->pluck('value')->toArray();
                    $rules[] = 'in:' . implode(',', $validValues);
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

    public function renderField($value = null)
    {
        $attributes = $this->field_attributes ?? [];
        $cssClasses = $this->css_classes ? ' ' . $this->css_classes : '';
        
        switch ($this->type) {
            case 'text':
            case 'email':
            case 'tel':
            case 'number':
            case 'url':
            case 'date':
            case 'time':
                return $this->renderInput($value, $cssClasses, $attributes);
                
            case 'textarea':
                return $this->renderTextarea($value, $cssClasses, $attributes);
                
            case 'select':
                return $this->renderSelect($value, $cssClasses, $attributes);
                
            case 'checkbox':
                return $this->renderCheckbox($value, $cssClasses, $attributes);
                
            case 'radio':
                return $this->renderRadio($value, $cssClasses, $attributes);
                
            case 'file':
                return $this->renderFile($cssClasses, $attributes);
                
            case 'heading':
                return $this->renderHeading();
                
            case 'divider':
                return '<hr>';
                
            case 'html':
                return $this->description;
                
            default:
                return $this->renderInput($value, $cssClasses, $attributes);
        }
    }

    private function renderInput($value = null, $cssClasses = '', $attributes = [])
    {
        $required = $this->required ? 'required' : '';
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';
        $value = $value ? "value=\"{$value}\"" : '';
        
        $attributeString = '';
        foreach ($attributes as $key => $val) {
            $attributeString .= " {$key}=\"{$val}\"";
        }

        return "
        <div class=\"form-group mb-3\">
            <label for=\"{$this->name}\" class=\"form-label\">{$this->label}" . ($this->required ? ' <span class="text-danger">*</span>' : '') . "</label>
            <input type=\"{$this->type}\" class=\"form-control{$cssClasses}\" id=\"{$this->name}\" name=\"{$this->name}\" {$placeholder} {$value} {$required} {$attributeString}>
            " . ($this->help_text ? "<div class=\"form-text\">{$this->help_text}</div>" : '') . "
        </div>";
    }

    private function renderTextarea($value = null, $cssClasses = '', $attributes = [])
    {
        $required = $this->required ? 'required' : '';
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';
        $rows = isset($attributes['rows']) ? $attributes['rows'] : '4';
        
        return "
        <div class=\"form-group mb-3\">
            <label for=\"{$this->name}\" class=\"form-label\">{$this->label}" . ($this->required ? ' <span class="text-danger">*</span>' : '') . "</label>
            <textarea class=\"form-control{$cssClasses}\" id=\"{$this->name}\" name=\"{$this->name}\" rows=\"{$rows}\" {$placeholder} {$required}>{$value}</textarea>
            " . ($this->help_text ? "<div class=\"form-text\">{$this->help_text}</div>" : '') . "
        </div>";
    }

    private function renderSelect($value = null, $cssClasses = '', $attributes = [])
    {
        $required = $this->required ? 'required' : '';
        $options = '';
        
        if ($this->options) {
            foreach ($this->options as $option) {
                $selected = ($value == $option['value']) ? 'selected' : '';
                $options .= "<option value=\"{$option['value']}\" {$selected}>{$option['label']}</option>";
            }
        }

        return "
        <div class=\"form-group mb-3\">
            <label for=\"{$this->name}\" class=\"form-label\">{$this->label}" . ($this->required ? ' <span class="text-danger">*</span>' : '') . "</label>
            <select class=\"form-select{$cssClasses}\" id=\"{$this->name}\" name=\"{$this->name}\" {$required}>
                <option value=\"\">" . ($this->placeholder ?: __('Select an option...')) . "</option>
                {$options}
            </select>
            " . ($this->help_text ? "<div class=\"form-text\">{$this->help_text}</div>" : '') . "
        </div>";
    }

    private function renderCheckbox($value = null, $cssClasses = '', $attributes = [])
    {
        $checked = $value ? 'checked' : '';
        
        return "
        <div class=\"form-check mb-3\">
            <input type=\"checkbox\" class=\"form-check-input{$cssClasses}\" id=\"{$this->name}\" name=\"{$this->name}\" value=\"1\" {$checked}>
            <label class=\"form-check-label\" for=\"{$this->name}\">{$this->label}</label>
            " . ($this->help_text ? "<div class=\"form-text\">{$this->help_text}</div>" : '') . "
        </div>";
    }

    private function renderRadio($value = null, $cssClasses = '', $attributes = [])
    {
        $radioButtons = '';
        
        if ($this->options) {
            foreach ($this->options as $option) {
                $checked = ($value == $option['value']) ? 'checked' : '';
                $radioButtons .= "
                <div class=\"form-check\">
                    <input type=\"radio\" class=\"form-check-input{$cssClasses}\" id=\"{$this->name}_{$option['value']}\" name=\"{$this->name}\" value=\"{$option['value']}\" {$checked}>
                    <label class=\"form-check-label\" for=\"{$this->name}_{$option['value']}\">{$option['label']}</label>
                </div>";
            }
        }

        return "
        <div class=\"form-group mb-3\">
            <label class=\"form-label\">{$this->label}" . ($this->required ? ' <span class="text-danger">*</span>' : '') . "</label>
            {$radioButtons}
            " . ($this->help_text ? "<div class=\"form-text\">{$this->help_text}</div>" : '') . "
        </div>";
    }

    private function renderFile($cssClasses = '', $attributes = [])
    {
        $accept = isset($attributes['accept']) ? "accept=\"{$attributes['accept']}\"" : '';
        $multiple = isset($attributes['multiple']) && $attributes['multiple'] ? 'multiple' : '';
        
        return "
        <div class=\"form-group mb-3\">
            <label for=\"{$this->name}\" class=\"form-label\">{$this->label}" . ($this->required ? ' <span class="text-danger">*</span>' : '') . "</label>
            <input type=\"file\" class=\"form-control{$cssClasses}\" id=\"{$this->name}\" name=\"{$this->name}" . ($multiple ? '[]' : '') . "\" {$accept} {$multiple}>
            " . ($this->help_text ? "<div class=\"form-text\">{$this->help_text}</div>" : '') . "
        </div>";
    }

    private function renderHeading()
    {
        $level = $this->field_attributes['level'] ?? 'h4';
        return "<{$level} class=\"form-heading\">{$this->label}</{$level}>";
    }
}