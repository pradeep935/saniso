<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use App\Models\ProjectQuoteFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectQuoteFormBuilderController extends BaseController
{
    public function index()
    {
        $fields = ProjectQuoteFormField::orderBy('sort_order')->get();
        $fieldTypes = ProjectQuoteFormField::getFieldTypes();
        
        return view('plugins/ecommerce::project-quote-form-builder.index', compact('fields', 'fieldTypes'));
    }

    public function create()
    {
        $fieldTypes = ProjectQuoteFormField::getFieldTypes();
        return view('plugins/ecommerce::project-quote-form-builder.create', compact('fieldTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:project_quote_form_fields,name',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(ProjectQuoteFormField::getFieldTypes())),
            'placeholder' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'required' => 'boolean',
            'enabled' => 'boolean',
            'validation_rules' => 'nullable|string',
            'css_classes' => 'nullable|string',
            'default_value' => 'nullable|string',
            'help_text' => 'nullable|string',
            'field_width' => 'required|string|in:col-12,col-6,col-4,col-3',
            'options' => 'nullable|array',
            'field_attributes' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Handle options array
        if ($request->has('options') && is_array($request->options)) {
            $options = [];
            foreach ($request->options as $option) {
                if (!empty($option['value']) && !empty($option['label'])) {
                    $options[] = [
                        'value' => $option['value'],
                        'label' => $option['label']
                    ];
                }
            }
            $data['options'] = $options;
        }

        // Handle field attributes
        if ($request->has('field_attributes') && is_array($request->field_attributes)) {
            $attributes = [];
            foreach ($request->field_attributes as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $attributes[$key] = $value;
                }
            }
            $data['field_attributes'] = $attributes;
        }

        // Set sort order
        $maxOrder = ProjectQuoteFormField::max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;

        ProjectQuoteFormField::create($data);

        return redirect()->route('project-quote-form-builder.index')
            ->with('success', 'Form field created successfully!');
    }

    public function edit($id)
    {
        $field = ProjectQuoteFormField::findOrFail($id);
        $fieldTypes = ProjectQuoteFormField::getFieldTypes();
        
        return view('plugins/ecommerce::project-quote-form-builder.edit', compact('field', 'fieldTypes'));
    }

    public function update(Request $request, $id)
    {
        $field = ProjectQuoteFormField::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:project_quote_form_fields,name,' . $id,
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(ProjectQuoteFormField::getFieldTypes())),
            'placeholder' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'required' => 'boolean',
            'enabled' => 'boolean',
            'validation_rules' => 'nullable|string',
            'css_classes' => 'nullable|string',
            'default_value' => 'nullable|string',
            'help_text' => 'nullable|string',
            'field_width' => 'required|string|in:col-12,col-6,col-4,col-3',
            'options' => 'nullable|array',
            'field_attributes' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Handle options array
        if ($request->has('options') && is_array($request->options)) {
            $options = [];
            foreach ($request->options as $option) {
                if (!empty($option['value']) && !empty($option['label'])) {
                    $options[] = [
                        'value' => $option['value'],
                        'label' => $option['label']
                    ];
                }
            }
            $data['options'] = $options;
        }

        // Handle field attributes
        if ($request->has('field_attributes') && is_array($request->field_attributes)) {
            $attributes = [];
            foreach ($request->field_attributes as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $attributes[$key] = $value;
                }
            }
            $data['field_attributes'] = $attributes;
        }

        $field->update($data);

        return redirect()->route('project-quote-form-builder.index')
            ->with('success', 'Form field updated successfully!');
    }

    public function destroy($id)
    {
        $field = ProjectQuoteFormField::findOrFail($id);
        $field->delete();

        return response()->json(['message' => 'Field deleted successfully']);
    }

    public function updateOrder(Request $request)
    {
        $fieldIds = $request->input('field_ids', []);
        
        foreach ($fieldIds as $index => $fieldId) {
            ProjectQuoteFormField::where('id', $fieldId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }

    public function toggleStatus($id)
    {
        $field = ProjectQuoteFormField::findOrFail($id);
        $field->enabled = !$field->enabled;
        $field->save();

        return response()->json([
            'message' => 'Field status updated successfully',
            'enabled' => $field->enabled
        ]);
    }

    public function preview()
    {
        $fields = ProjectQuoteFormField::getEnabledFields();
        
        return view('plugins/ecommerce::project-quote-form-builder.preview', compact('fields'));
    }

    public function duplicate($id)
    {
        $field = ProjectQuoteFormField::findOrFail($id);
        
        $newField = $field->replicate();
        $newField->name = $field->name . '_copy_' . time();
        $newField->label = $field->label . ' (Copy)';
        $newField->sort_order = ProjectQuoteFormField::max('sort_order') + 1;
        $newField->save();

        return redirect()->route('project-quote-form-builder.index')
            ->with('success', 'Field duplicated successfully!');
    }
}