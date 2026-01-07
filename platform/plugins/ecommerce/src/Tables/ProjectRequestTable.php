<?php

namespace Botble\Ecommerce\Tables;

use Botble\Ecommerce\Models\ProjectRequest;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProjectRequestTable extends TableAbstract
{
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('customer_name', function (ProjectRequest $item) {
                return $item->customer_name ?: 'N/A';
            })
            ->editColumn('customer_email', function (ProjectRequest $item) {
                return $item->customer_email ?: 'N/A';
            })
            ->editColumn('status', function (ProjectRequest $item) {
                return ucfirst(str_replace('_', ' ', $item->status));
            })
            ->editColumn('created_at', function (ProjectRequest $item) {
                return $item->created_at->format('Y-m-d H:i:s');
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = ProjectRequest::query()
            ->select([
                'id',
                'customer_name',
                'customer_email',
                'status',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'customer_name' => [
                'title' => trans('plugins/ecommerce::ecommerce.customer_name'),
                'class' => 'text-start',
            ],
            'customer_email' => [
                'title' => trans('plugins/ecommerce::ecommerce.customer_email'),
                'class' => 'text-start',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'class' => 'text-center',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
                'class' => 'text-start',
            ],
        ];
    }

    public function buttons(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('project-requests.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => [
                    'pending' => trans('plugins/ecommerce::ecommerce.pending'),
                    'in_progress' => trans('plugins/ecommerce::ecommerce.in_progress'),
                    'quoted' => trans('plugins/ecommerce::ecommerce.quoted'),
                    'accepted' => trans('plugins/ecommerce::ecommerce.accepted'),
                    'completed' => trans('plugins/ecommerce::ecommerce.completed'),
                    'rejected' => trans('plugins/ecommerce::ecommerce.rejected'),
                    'cancelled' => trans('plugins/ecommerce::ecommerce.cancelled'),
                ],
                'validate' => 'required|in:pending,in_progress,quoted,accepted,completed,rejected,cancelled',
            ],
        ];
    }
}