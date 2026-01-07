<?php

namespace Botble\AffiliatePro\Models;

use Botble\AffiliatePro\Enums\PayoutPaymentMethodsEnum;
use Botble\AffiliatePro\Enums\WithdrawalStatusEnum;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class Withdrawal extends BaseModel
{
    protected $table = 'affiliate_withdrawals';

    protected $fillable = [
        'affiliate_id',
        'amount',
        'status',
        'payment_method',
        'payment_details',
        'notes',
        'payment_channel',
        'transaction_id',
        'bank_info',
        'customer_id',
        'currency',
    ];

    protected $casts = [
        'amount' => 'float',
        'status' => WithdrawalStatusEnum::class,
        'payment_method' => SafeContent::class,
        'payment_details' => SafeContent::class,
        'notes' => SafeContent::class,
        'payment_channel' => PayoutPaymentMethodsEnum::class,
        'bank_info' => 'array',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withDefault();
    }

    public function canEditStatus(): bool
    {
        return in_array($this->status->getValue(), [
            WithdrawalStatusEnum::PENDING,
            WithdrawalStatusEnum::PROCESSING,
        ]);
    }

    public function getNextStatuses(): array
    {
        return match ($this->status->getValue()) {
            WithdrawalStatusEnum::PENDING => Arr::except(
                WithdrawalStatusEnum::labels(),
                WithdrawalStatusEnum::CANCELED
            ),
            WithdrawalStatusEnum::PROCESSING => Arr::except(
                WithdrawalStatusEnum::labels(),
                [WithdrawalStatusEnum::PENDING, WithdrawalStatusEnum::CANCELED]
            ),
            default => [$this->status->getValue() => $this->status->label()],
        };
    }
}
