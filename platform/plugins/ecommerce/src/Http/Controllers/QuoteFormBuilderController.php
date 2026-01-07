<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use App\Models\QuoteFormField;
use App\Models\QuoteFormStyle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteFormBuilderController extends BaseController
{
    public function index()
    {
        $fields = QuoteFormField::orderBy('sort_order')->get();
        $fieldTypes = QuoteFormField::getFieldTypes();
        
        return view('plugins/ecommerce::quote-form-builder.index', compact('fields', 'fieldTypes'));
    }

    public function create()
    {
        $fieldTypes = QuoteFormField::getFieldTypes();
        return view('plugins/ecommerce::quote-form-builder.create', compact('fieldTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:quote_form_fields,name',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(QuoteFormField::getFieldTypes())),
            'field_width' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'name', 'label', 'type', 'placeholder', 'description',
            'required', 'enabled', 'validation_rules', 'css_classes',
            'default_value', 'help_text', 'field_width'
        ]);

        // Handle options for select, radio, checkbox fields
        if (in_array($request->type, ['select', 'radio', 'checkbox']) && $request->has('options')) {
            $options = [];
            foreach ($request->options['labels'] as $index => $label) {
                if (!empty($label) && !empty($request->options['values'][$index])) {
                    $options[] = [
                        'label' => $label,
                        'value' => $request->options['values'][$index]
                    ];
                }
            }
            $data['options'] = $options;
        }

        // Handle field attributes
        if ($request->has('field_attributes')) {
            $attributes = [];
            foreach ($request->field_attributes as $key => $value) {
                if (!empty($value)) {
                    $attributes[$key] = $value;
                }
            }
            $data['field_attributes'] = $attributes;
        }

        // Set sort order
        $maxOrder = QuoteFormField::max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;

        QuoteFormField::create($data);

        return redirect()->route('admin.ecommerce.quote-form-builder.index')
            ->with('success', 'Field created successfully!');
    }

    public function edit($id)
    {
        $field = QuoteFormField::findOrFail($id);
        $fieldTypes = QuoteFormField::getFieldTypes();
        
        return view('plugins/ecommerce::quote-form-builder.edit', compact('field', 'fieldTypes'));
    }

    public function update(Request $request, $id)
    {
        $field = QuoteFormField::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:quote_form_fields,name,' . $id,
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(QuoteFormField::getFieldTypes())),
            'field_width' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'name', 'label', 'type', 'placeholder', 'description',
            'required', 'enabled', 'validation_rules', 'css_classes',
            'default_value', 'help_text', 'field_width'
        ]);

        // Handle options for select, radio, checkbox fields
        if (in_array($request->type, ['select', 'radio', 'checkbox']) && $request->has('options')) {
            $options = [];
            foreach ($request->options['labels'] as $index => $label) {
                if (!empty($label) && !empty($request->options['values'][$index])) {
                    $options[] = [
                        'label' => $label,
                        'value' => $request->options['values'][$index]
                    ];
                }
            }
            $data['options'] = $options;
        }

        // Handle field attributes
        if ($request->has('field_attributes')) {
            $attributes = [];
            foreach ($request->field_attributes as $key => $value) {
                if (!empty($value)) {
                    $attributes[$key] = $value;
                }
            }
            $data['field_attributes'] = $attributes;
        }

        $field->update($data);

        return redirect()->route('admin.ecommerce.quote-form-builder.index')
            ->with('success', 'Field updated successfully!');
    }

    public function destroy($id)
    {
        $field = QuoteFormField::findOrFail($id);
        $field->delete();

        return redirect()->route('admin.ecommerce.quote-form-builder.index')
            ->with('success', 'Field deleted successfully!');
    }

    public function updateOrder(Request $request)
    {
        $fields = $request->input('fields', []);
        
        foreach ($fields as $index => $fieldId) {
            QuoteFormField::where('id', $fieldId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $field = QuoteFormField::findOrFail($id);
        $field->update(['enabled' => !$field->enabled]);

        return redirect()->route('admin.ecommerce.quote-form-builder.index')
            ->with('success', 'Field status updated successfully!');
    }

    public function styles()
    {
        $styles = QuoteFormStyle::getAllSettings();
        return view('plugins/ecommerce::quote-form-builder.styles', compact('styles'));
    }

    public function updateStyles(Request $request)
    {
        $styleCategories = ['form_container', 'form_fields', 'form_buttons', 'responsive_breakpoints'];

        foreach ($styleCategories as $category) {
            if ($request->has($category)) {
                QuoteFormStyle::setSetting($category, $request->input($category));
            }
        }

        return redirect()->route('admin.ecommerce.quote-form-builder.styles')
            ->with('success', 'Styles updated successfully!');
    }

    public function preview()
    {
        $fields = QuoteFormField::getEnabledFields();
        $styles = QuoteFormStyle::getAllSettings();
        $css = QuoteFormStyle::generateCSS();
        
        return view('plugins/ecommerce::quote-form-builder.preview', compact('fields', 'styles', 'css'));
    }

    public function duplicate($id)
    {
        $field = QuoteFormField::findOrFail($id);
        $newField = $field->replicate();
        $newField->name = $field->name . '_copy';
        $newField->label = $field->label . ' (Copy)';
        $newField->sort_order = QuoteFormField::max('sort_order') + 1;
        $newField->save();

        return redirect()->route('admin.ecommerce.quote-form-builder.index')
            ->with('success', 'Field duplicated successfully!');
    }
}