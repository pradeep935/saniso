<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Ecommerce\Models\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PublicProjectController
{
    public function store(Request $request)
    {
        // Get enabled project form fields for validation
        $projectFields = \Botble\Ecommerce\Models\ProjectFormField::getEnabledFields();
        
        // Build validation rules dynamically
        $validationRules = [
            // Honeypot field for spam protection
            'website' => 'nullable|string|max:0', // Should be empty
        ];
        
        foreach ($projectFields as $field) {
            $rules = [];
            
            // Add required rule if field is required
            if ($field->required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }
            
            // Add type-specific rules
            switch ($field->type) {
                case 'email':
                    $rules[] = 'email';
                    $rules[] = 'max:255';
                    break;
                case 'text':
                case 'textarea':
                    $rules[] = 'string';
                    if ($field->name === 'project_description') {
                        $rules[] = 'min:10';
                    }
                    $rules[] = 'max:2000';
                    break;
                case 'tel':
                    $rules[] = 'string';
                    $rules[] = 'max:20';
                    break;
                case 'file':
                    if ($field->field_attributes) {
                        $attributes = is_string($field->field_attributes) 
                            ? json_decode($field->field_attributes, true) 
                            : $field->field_attributes;
                        
                        $fileRules = ['file'];
                        if (isset($attributes['accept'])) {
                            $extensions = str_replace('.', '', $attributes['accept']);
                            $fileRules[] = 'mimes:' . $extensions;
                        } else {
                            $fileRules[] = 'mimes:jpg,jpeg,png,pdf,doc,docx,zip';
                        }
                        $fileRules[] = 'max:10240'; // 10MB max per file
                        
                        if (isset($attributes['multiple']) && $attributes['multiple']) {
                            $validationRules[$field->name . '.*'] = $fileRules;
                        } else {
                            $validationRules[$field->name] = $fileRules;
                        }
                        break;
                    }
                    break;
                case 'checkbox':
                    $rules[] = 'boolean';
                    break;
                default:
                    $rules[] = 'string';
                    $rules[] = 'max:255';
            }
            
            // Add custom validation rules if specified
            if ($field->validation_rules) {
                $customRules = explode('|', $field->validation_rules);
                $rules = array_merge($rules, $customRules);
            }
            
            $validationRules[$field->name] = $rules;
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => __('Please check your form data.'),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check honeypot field for spam protection
        if ($request->filled('website')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => __('Spam detection triggered.')
                ], 422);
            }
            return redirect()->back()->with('error', __('Spam detection triggered.'));
        }

        try {
            // Collect dynamic field data
            $fieldData = [];
            $uploadedFiles = [];
            
            foreach ($projectFields as $field) {
                $value = $request->input($field->name);
                
                // Handle file uploads
                if ($field->type === 'file' && $request->hasFile($field->name)) {
                    $files = $request->file($field->name);
                    if (!is_array($files)) {
                        $files = [$files];
                    }
                    
                    foreach ($files as $file) {
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('storage/project-files'), $filename);
                        $uploadedFiles[] = 'storage/project-files/' . $filename;
                    }
                    $fieldData[$field->name] = $uploadedFiles;
                } else {
                    $fieldData[$field->name] = $value;
                }
            }

            // Create project request with dynamic data
            $projectRequest = ProjectRequest::create([
                'customer_name' => $fieldData['customer_name'] ?? '',
                'customer_email' => $fieldData['customer_email'] ?? '',
                'customer_phone' => $fieldData['customer_phone'] ?? '',
                'customer_company' => $fieldData['customer_company'] ?? '',
                'project_description' => $fieldData['project_description'] ?? '',
                'uploaded_files' => $uploadedFiles,
                'budget_range' => $fieldData['budget_range'] ?? '',
                'deadline' => $fieldData['deadline'] ?? '',
                'newsletter_subscribe' => isset($fieldData['newsletter_subscribe']) ? (bool)$fieldData['newsletter_subscribe'] : false,
                'form_data' => $fieldData, // Store all form data as JSON
                'status' => 'pending'
            ]);

            // Send notifications
            $this->sendNotifications($projectRequest);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => false,
                    'message' => __('Thank you! Your project request has been submitted successfully. We will contact you within 24 hours.')
                ]);
            }
            
            return redirect()->back()->with('success', __('Thank you! Your project request has been submitted successfully. We will contact you within 24 hours.'));

        } catch (\Exception $e) {
            \Log::error('Project request submission failed: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => __('Sorry, there was an error processing your request. Please try again or contact us directly.')
                ], 500);
            }
            
            return redirect()->back()->with('error', __('Sorry, there was an error processing your request. Please try again or contact us directly.'));
        }
    }

    private function sendNotifications(ProjectRequest $projectRequest)
    {
        try {
            // Send customer confirmation email
            Mail::send('emails.project-request-confirmation', ['projectRequest' => $projectRequest], function ($message) use ($projectRequest) {
                $message->to($projectRequest->customer_email, $projectRequest->customer_name)
                        ->subject('Project Request Confirmation');
            });

            // Send admin notification email
            $adminEmail = get_ecommerce_setting('admin_email') ?? config('mail.from.address');
            if ($adminEmail) {
                Mail::send('emails.project-request-admin', ['projectRequest' => $projectRequest], function ($message) use ($adminEmail, $projectRequest) {
                    $message->to($adminEmail)
                            ->subject('New Project Request - ' . $projectRequest->customer_name);
                });
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send project request notifications: ' . $e->getMessage());
        }
    }
}