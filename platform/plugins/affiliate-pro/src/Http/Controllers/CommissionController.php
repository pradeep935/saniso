<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\AffiliatePro\Models\Commission;
use Botble\AffiliatePro\Tables\CommissionTable;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Http\Request;

class CommissionController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::commission.name'), route('affiliate-pro.commissions.index'));
    }
    public function index(CommissionTable $table)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::commission.name'));

        return $table->renderTable();
    }

    public function show(Commission $commission)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::commission.view', ['id' => $commission->id]));

        return view('plugins/affiliate-pro::commissions.show', compact('commission'));
    }

    public function approve(Commission $commission, BaseHttpResponse $response)
    {
        $commission->status = CommissionStatusEnum::APPROVED;
        $commission->save();

        // Update affiliate balance
        $affiliate = $commission->affiliate;
        $affiliate->balance += $commission->amount;
        $affiliate->total_commission += $commission->amount;
        $affiliate->save();

        // Create transaction record
        $commission->affiliate->transactions()->create([
            'amount' => $commission->amount,
            'description' => 'Commission approved for order #' . $commission->order_id,
            'type' => 'commission',
            'reference_id' => $commission->id,
            'reference_type' => Commission::class,
        ]);

        return $response
            ->setPreviousUrl(route('affiliate-pro.commissions.index'))
            ->setMessage(trans('plugins/affiliate-pro::commission.approve_success'));
    }

    public function reject(Commission $commission, Request $request, BaseHttpResponse $response)
    {
        $commission->status = CommissionStatusEnum::REJECTED;
        $commission->save();

        return $response
            ->setPreviousUrl(route('affiliate-pro.commissions.index'))
            ->setMessage(trans('plugins/affiliate-pro::commission.reject_success'));
    }
}
