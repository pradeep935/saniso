<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Models\ProjectFormField;
use Botble\Ecommerce\Tables\ProjectFormBuilderTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectFormBuilderController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/ecommerce::ecommerce.project_form_builder'), route('project-form-builder.index'));
    }

    public function index(ProjectFormBuilderTable $table)
    {
        $this->pageTitle(trans('plugins/ecommerce::ecommerce.project_form_builder'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/ecommerce::ecommerce.add_new_field'));
        
        $fieldTypes = ProjectFormField::getFieldTypes();
        
        return view('plugins/ecommerce::project-form-builder.create', compact('fieldTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(ProjectFormField::getFieldTypes())),
            'field_width' => 'nullable|string|in:col-12,col-6,col-4,col-3',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'help_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'label',
            'type',
            'field_width',
            'required',
            'placeholder',
            'options',
            'validation_rules',
            'help_text'
        ]);

        $data['required'] = $request->boolean('required');
        $data['field_width'] = $request->input('field_width', 'col-12');
        $data['enabled'] = true;

        // Set sort order
        $maxOrder = ProjectFormField::max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;

        ProjectFormField::create($data);

        return redirect()->route('project-form-builder.index')
            ->with('success_msg', trans('core/base::notices.create_success_message'));
    }

    public function edit($id)
    {
        $field = ProjectFormField::findOrFail($id);
        $fieldTypes = ProjectFormField::getFieldTypes();
        
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $field->label]));

        return view('plugins/ecommerce::project-form-builder.edit', compact('field', 'fieldTypes'));
    }

    public function update(Request $request, $id)
    {
        $field = ProjectFormField::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(ProjectFormField::getFieldTypes())),
            'field_width' => 'nullable|string|in:col-12,col-6,col-4,col-3',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'help_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'label',
            'type',
            'field_width',
            'required',
            'placeholder',
            'options',
            'validation_rules',
            'help_text'
        ]);

        $data['required'] = $request->boolean('required');
        $data['field_width'] = $request->input('field_width', 'col-12');

        $field->update($data);

        return redirect()->route('project-form-builder.index')
            ->with('success_msg', trans('core/base::notices.update_success_message'));
    }

    public function destroy($id)
    {
        $field = ProjectFormField::findOrFail($id);
        
        return DeleteResourceAction::make($field);
    }

    public function reorder(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            ProjectFormField::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => trans('core/base::notices.update_success_message')]);
    }

    public function toggle($id)
    {
        $field = ProjectFormField::findOrFail($id);
        $field->update(['enabled' => !$field->enabled]);

        return response()->json([
            'status' => true,
            'message' => trans('core/base::notices.update_success_message')
        ]);
    }

    public function duplicate($id)
    {
        $field = ProjectFormField::findOrFail($id);
        
        $newField = $field->replicate();
        $newField->label = $field->label . ' (Copy)';
        $newField->sort_order = (ProjectFormField::max('sort_order') ?? 0) + 1;
        $newField->save();

        return redirect()->route('project-form-builder.index')
            ->with('success_msg', trans('core/base::notices.create_success_message'));
    }

    public function preview()
    {
        $fields = ProjectFormField::where('enabled', true)
            ->orderBy('sort_order')
            ->get();

        return view('plugins/ecommerce::project-form-builder.preview', compact('fields'));
    }
}