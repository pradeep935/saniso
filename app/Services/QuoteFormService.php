<?php

namespace App\Services;

use App\Models\QuoteFormField;
use App\Models\QuoteFormStyle;

class QuoteFormService
{
    /**
     * Get the rendered quote form HTML
     */
    public function renderForm($productId = null, $existingData = [])
    {
        $fields = QuoteFormField::getEnabledFields();
        $styles = QuoteFormStyle::getAllSettings();
        $css = QuoteFormStyle::generateCSS();

        return view('plugins/ecommerce::quote-requests.dynamic-form', compact('fields', 'styles', 'css', 'productId', 'existingData'))->render();
    }

    /**
     * Get validation rules for the form
     */
    public function getValidationRules()
    {
        $fields = QuoteFormField::getEnabledFields();
        $rules = [];

        foreach ($fields as $field) {
            if ($field->type === 'file') {
                $rules[$field->name] = $field->getValidationRules();
            } elseif (in_array($field->type, ['checkbox']) && $field->options) {
                $rules[$field->name . '.*'] = 'string';
            } else {
                $validationRule = $field->getValidationRules();
                if ($validationRule) {
                    $rules[$field->name] = $validationRule;
                }
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     */
    public function getValidationMessages()
    {
        $fields = QuoteFormField::getEnabledFields();
        $messages = [];

        foreach ($fields as $field) {
            if ($field->required && !in_array($field->type, ['heading', 'divider', 'html'])) {
                $messages[$field->name . '.required'] = "The {$field->label} field is required.";
            }

            // Add type-specific messages
            switch ($field->type) {
                case 'email':
                    $messages[$field->name . '.email'] = "Please enter a valid email address for {$field->label}.";
                    break;
                case 'number':
                    $messages[$field->name . '.numeric'] = "{$field->label} must be a number.";
                    break;
                case 'url':
                    $messages[$field->name . '.url'] = "Please enter a valid URL for {$field->label}.";
                    break;
                case 'file':
                    $messages[$field->name . '.file'] = "Please upload a valid file for {$field->label}.";
                    break;
            }
        }

        return $messages;
    }

    /**
     * Process form submission data
     */
    public function processFormData($requestData)
    {
        $fields = QuoteFormField::getEnabledFields();
        $processedData = [];

        foreach ($fields as $field) {
            if (isset($requestData[$field->name])) {
                $value = $requestData[$field->name];

                // Handle different field types
                switch ($field->type) {
                    case 'checkbox':
                        if ($field->options) {
                            // Multiple checkboxes
                            $processedData[$field->name] = is_array($value) ? $value : [$value];
                        } else {
                            // Single checkbox
                            $processedData[$field->name] = $value ? 1 : 0;
                        }
                        break;
                    
                    case 'file':
                        // Handle file uploads
                        if ($value && is_object($value)) {
                            $processedData[$field->name] = $this->handleFileUpload($value, $field);
                        }
                        break;
                    
                    case 'number':
                        $processedData[$field->name] = is_numeric($value) ? (float) $value : $value;
                        break;
                    
                    default:
                        $processedData[$field->name] = $value;
                        break;
                }
            }
        }

        return $processedData;
    }

    /**
     * Handle file upload for form fields
     */
    private function handleFileUpload($file, $field)
    {
        $allowedMimes = [];
        if ($field->field_attributes && isset($field->field_attributes['accept'])) {
            $allowedMimes = explode(',', str_replace('.', '', $field->field_attributes['accept']));
        }

        $path = $file->store('quote-attachments', 'public');
        
        return [
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ];
    }

    /**
     * Get form data for editing
     */
    public function getFormDataForEdit($quoteRequest)
    {
        $formData = [];
        $customData = json_decode($quoteRequest->custom_fields ?? '{}', true);

        // Map existing quote request data to form fields
        $fieldMappings = [
            'product_name' => 'product_name',
            'customer_name' => 'customer_name', 
            'customer_email' => 'customer_email',
            'customer_phone' => 'customer_phone',
            'quantity' => 'quantity',
            'requirements' => 'message'
        ];

        foreach ($fieldMappings as $fieldName => $quoteField) {
            if (isset($quoteRequest->$quoteField)) {
                $formData[$fieldName] = $quoteRequest->$quoteField;
            }
        }

        // Add custom fields
        if ($customData) {
            $formData = array_merge($formData, $customData);
        }

        return $formData;
    }

    /**
     * Get CSS for the form
     */
    public function getFormCSS()
    {
        return QuoteFormStyle::generateCSS();
    }

    /**
     * Check if form builder is enabled
     */
    public function isFormBuilderEnabled()
    {
        return QuoteFormField::where('enabled', true)->exists();
    }
}