<?php

namespace Botble\Ecommerce\Tables;

use Botble\Ecommerce\Models\ProjectFormField;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProjectFormBuilderTable extends TableAbstract
{
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('label', function (ProjectFormField $item) {
                return anchor_link(route('project-form-builder.edit', $item->id), $item->label);
            })
            ->editColumn('type', function (ProjectFormField $item) {
                $types = ProjectFormField::getFieldTypes();
                return $types[$item->type] ?? $item->type;
            })
            ->editColumn('field_width', function (ProjectFormField $item) {
                $widths = [
                    'col-12' => 'Full Width (12/12)',
                    'col-6' => 'Half Width (6/12)',
                    'col-4' => 'One Third (4/12)',
                    'col-3' => 'Quarter Width (3/12)',
                ];
                return $widths[$item->field_width] ?? $item->field_width;
            })
            ->editColumn('required', function (ProjectFormField $item) {
                return $item->required ? 
                    '<span class="badge bg-success">Required</span>' : 
                    '<span class="badge bg-secondary">Optional</span>';
            })
            ->editColumn('enabled', function (ProjectFormField $item) {
                return $item->enabled ?
                    '<span class="badge bg-success">Enabled</span>' :
                    '<span class="badge bg-warning">Disabled</span>';
            })
            ->editColumn('sort_order', function (ProjectFormField $item) {
                return $item->sort_order;
            })
            ->addColumn('operations', function (ProjectFormField $item) {
                return $this->getOperations(
                    'project-form-builder.edit',
                    'project-form-builder.destroy',
                    $item,
                    view('plugins/ecommerce::project-form-builder.actions', compact('item'))->render()
                );
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = ProjectFormField::query()
            ->select([
                'id',
                'label',
                'type',
                'field_width',
                'required',
                'enabled',
                'sort_order',
                'created_at',
            ])
            ->orderBy('sort_order');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'sort_order' => [
                'title' => 'Order',
                'width' => '80px',
                'class' => 'text-center',
            ],
            'label' => [
                'title' => trans('plugins/ecommerce::ecommerce.field_label'),
                'class' => 'text-start',
            ],
            'type' => [
                'title' => trans('plugins/ecommerce::ecommerce.field_type'),
                'width' => '150px',
            ],
            'field_width' => [
                'title' => trans('plugins/ecommerce::ecommerce.field_width'),
                'width' => '150px',
            ],
            'required' => [
                'title' => trans('plugins/ecommerce::ecommerce.required'),
                'width' => '100px',
                'class' => 'text-center',
            ],
            'enabled' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
                'class' => 'text-center',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '120px',
            ],
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('project-form-builder.create'), 'project-requests.index');
    }

    public function bulkActions(): array
    {
        return parent::bulkActions();
    }

    public function getBulkChanges(): array
    {
        return [
            'label' => [
                'title' => trans('plugins/ecommerce::ecommerce.field_label'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'enabled' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => [
                    '1' => trans('core/base::tables.published'),
                    '0' => trans('core/base::tables.draft'),
                ],
                'validate' => 'required|in:0,1',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}