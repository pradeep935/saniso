<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Enums\WithdrawalStatusEnum;
use Botble\AffiliatePro\Events\WithdrawalApprovedEvent;
use Botble\AffiliatePro\Events\WithdrawalRejectedEvent;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\AffiliatePro\Tables\WithdrawalTable;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Http\Request;

class WithdrawalController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::withdrawal.name'), route('affiliate-pro.withdrawals.index'));
    }
    public function index(WithdrawalTable $table)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::withdrawal.name'));

        return $table->renderTable();
    }

    public function show(Withdrawal $withdrawal)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::withdrawal.view', ['id' => $withdrawal->id]));

        return view('plugins/affiliate-pro::withdrawals.show', compact('withdrawal'));
    }

    public function approve(Withdrawal $withdrawal, BaseHttpResponse $response)
    {
        $withdrawal->status = WithdrawalStatusEnum::APPROVED;
        $withdrawal->save();

        // Update affiliate total_withdrawn (balance was already deducted when withdrawal was created)
        $affiliate = $withdrawal->affiliate;
        $affiliate->total_withdrawn += $withdrawal->amount;
        $affiliate->save();

        // Create transaction record
        $withdrawal->affiliate->transactions()->create([
            'amount' => -$withdrawal->amount,
            'description' => 'Withdrawal approved: ' . $withdrawal->payment_method,
            'type' => 'withdrawal',
            'reference_id' => $withdrawal->id,
            'reference_type' => Withdrawal::class,
        ]);

        // Fire withdrawal approved event
        event(new WithdrawalApprovedEvent($withdrawal));

        return $response
            ->setPreviousUrl(route('affiliate-pro.withdrawals.index'))
            ->setMessage(trans('plugins/affiliate-pro::withdrawal.approve_success'));
    }

    public function reject(Withdrawal $withdrawal, Request $request, BaseHttpResponse $response)
    {
        $withdrawal->status = WithdrawalStatusEnum::REJECTED;
        $withdrawal->save();

        // Return the withdrawal amount back to affiliate balance
        $affiliate = $withdrawal->affiliate;
        $affiliate->balance += $withdrawal->amount;
        $affiliate->save();

        // Create transaction record for the refund
        $withdrawal->affiliate->transactions()->create([
            'amount' => $withdrawal->amount,
            'description' => 'Withdrawal rejected - amount refunded',
            'type' => 'refund',
            'reference_id' => $withdrawal->id,
            'reference_type' => Withdrawal::class,
        ]);

        // Fire withdrawal rejected event
        event(new WithdrawalRejectedEvent($withdrawal, $request->input('reason', '')));

        return $response
            ->setPreviousUrl(route('affiliate-pro.withdrawals.index'))
            ->setMessage(trans('plugins/affiliate-pro::withdrawal.reject_success'));
    }
}
