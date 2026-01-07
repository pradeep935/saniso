@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::withdrawal.request'))

@section('content')
    <div class="bb-customer-card-list affiliate-withdrawals-cards">
        {{-- Account Status Card (if not approved) --}}
        @if($affiliate->status != \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
            <div class="bb-customer-card affiliate-status-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.account_status') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-warning text-dark">{{ trans('plugins/affiliate-pro::affiliate.not_approved') }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="alert alert-warning mb-0">
                        <x-core::icon name="ti ti-alert-triangle" class="me-2" />
                        {{ trans('plugins/affiliate-pro::withdrawal.account_not_approved') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Balance Overview Card --}}
        <div class="bb-customer-card balance-overview-card">
            <div class="bb-customer-card-header">
                <div class="bb-customer-card-title">
                    <span class="value">{{ trans('plugins/affiliate-pro::affiliate.account_balance') }}</span>
                </div>
                <div class="bb-customer-card-status">
                    <span class="badge bg-success">{{ format_price($affiliate->balance) }}</span>
                </div>
            </div>
            <div class="bb-customer-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="balance-stat">
                            <div class="balance-icon">
                                <x-core::icon name="ti ti-wallet" class="text-success" />
                            </div>
                            <div class="balance-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.available_balance') }}</h6>
                                <p class="text-success fw-bold">{{ format_price($affiliate->balance) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="balance-stat">
                            <div class="balance-icon">
                                <x-core::icon name="ti ti-coins" class="text-primary" />
                            </div>
                            <div class="balance-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.total_earned') }}</h6>
                                <p class="text-primary fw-bold">{{ format_price($affiliate->total_commission ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="balance-stat">
                            <div class="balance-icon">
                                <x-core::icon name="ti ti-arrow-down" class="text-info" />
                            </div>
                            <div class="balance-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.total_withdrawn') }}</h6>
                                <p class="text-info fw-bold">{{ format_price($affiliate->total_withdrawn ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Withdrawal Request Card --}}
        @if($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
            <div class="bb-customer-card withdrawal-request-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::withdrawal.request') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <x-core::icon name="ti ti-plus" class="text-primary" />
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <form method="POST" action="{{ route('affiliate-pro.withdrawals.store') }}" id="withdrawal-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="available-balance" class="form-label">{{ trans('plugins/affiliate-pro::withdrawal.available_balance') }}</label>
                                    <input type="text" class="form-control" id="available-balance" value="{{ format_price($affiliate->balance) }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="amount" class="form-label">{{ trans('plugins/affiliate-pro::withdrawal.enter_amount') }}</label>
                                    <input type="number" class="form-control" id="amount" name="amount" min="0" max="{{ $affiliate->balance }}" step="0.01" required>
                                    <small class="text-muted">{{ trans('plugins/affiliate-pro::withdrawal.minimum_amount', ['amount' => format_price(app('affiliate-helper')->getMinimumWithdrawalAmount())]) }}</small>
                                </div>
                            </div>
                        </div>

                        @php
                            $payoutMethods = \Botble\AffiliatePro\Enums\PayoutPaymentMethodsEnum::payoutMethodsEnabled();
                            $enabledMethods = collect($payoutMethods)->filter(function($method) {
                                return $method['is_enabled'];
                            });
                        @endphp

                        @if ($enabledMethods->isNotEmpty())
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/affiliate-pro::withdrawal.select_payment_method') }}</label>
                                <div class="payment-method-cards">
                                    @foreach($enabledMethods as $method)
                                        <div class="payment-method-card" data-method="{{ $method['key'] }}">
                                            <input type="radio" id="payment_{{ $method['key'] }}" name="payment_method" value="{{ $method['key'] }}" class="payment-method-input" required>
                                            <label for="payment_{{ $method['key'] }}" class="payment-method-label">
                                                <div class="payment-method-icon">
                                                    @if($method['key'] === 'paypal')
                                                        <x-core::icon name="ti ti-brand-paypal" />
                                                    @elseif($method['key'] === 'bank_transfer')
                                                        <x-core::icon name="ti ti-building-bank" />
                                                    @elseif($method['key'] === 'stripe')
                                                        <x-core::icon name="ti ti-brand-stripe" />
                                                    @else
                                                        <x-core::icon name="ti ti-dots" />
                                                    @endif
                                                </div>
                                                <div class="payment-method-info">
                                                    <h6 class="payment-method-title">{{ $method['label'] }}</h6>
                                                    <p class="payment-method-description">{{ trans('plugins/affiliate-pro::affiliate.' . $method['key'] . '_description') }}</p>
                                                </div>
                                                <div class="payment-method-check">
                                                    <x-core::icon name="ti ti-check" />
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <x-core::icon name="ti ti-alert-triangle" class="me-2" />
                                {{ trans('plugins/affiliate-pro::withdrawal.no_payment_methods_available') }}
                            </div>
                        @endif

                        @if ($enabledMethods->isNotEmpty())
                            <div class="form-group mb-3 payment-details-wrapper">
                                <label for="payment_details" class="form-label" id="payment-details-label">{{ trans('plugins/affiliate-pro::withdrawal.payment_details') }}</label>
                                <textarea class="form-control" id="payment_details" name="payment_details" rows="3" placeholder="{{ trans('plugins/affiliate-pro::withdrawal.payment_details_placeholder') }}" required></textarea>
                                <small class="text-muted payment-details-help" id="paypal-help" style="display: none;">{{ trans('plugins/affiliate-pro::affiliate.paypal_help') }}</small>
                                <small class="text-muted payment-details-help" id="bank_transfer-help" style="display: none;">{{ trans('plugins/affiliate-pro::affiliate.bank_help') }}</small>
                                <small class="text-muted payment-details-help" id="stripe-help" style="display: none;">{{ trans('plugins/affiliate-pro::affiliate.stripe_help') }}</small>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="submit-withdrawal">
                                    <x-core::icon name="ti ti-send" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::withdrawal.submit_request') }}
                                </button>
                            </div>
                        @else
                            <div class="form-group">
                                <button type="button" class="btn btn-secondary" disabled>
                                    <x-core::icon name="ti ti-send" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::withdrawal.submit_request') }}
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        @endif

        {{-- Withdrawal History Card --}}
        <div class="bb-customer-card withdrawal-history-card">
            <div class="bb-customer-card-header">
                <div class="bb-customer-card-title">
                    <span class="value">{{ trans('plugins/affiliate-pro::withdrawal.history') }}</span>
                </div>
                <div class="bb-customer-card-status">
                    <span class="badge bg-info">{{ count($withdrawals) }}</span>
                </div>
            </div>
            <div class="bb-customer-card-body">
                @if(count($withdrawals) > 0)
                    <div class="bb-customer-card-list">
                        @foreach($withdrawals as $withdrawal)
                            <div class="bb-customer-card-content withdrawal-item">
                                <div class="bb-customer-card-image">
                                    <div class="withdrawal-icon">
                                        @if($withdrawal->status == 'pending')
                                            <x-core::icon name="ti ti-clock" class="text-warning" style="font-size: 2rem;" />
                                        @elseif($withdrawal->status == 'processing')
                                            <x-core::icon name="ti ti-loader" class="text-info" style="font-size: 2rem;" />
                                        @elseif($withdrawal->status == 'approved')
                                            <x-core::icon name="ti ti-circle-check" class="text-success" style="font-size: 2rem;" />
                                        @elseif($withdrawal->status == 'rejected')
                                            <x-core::icon name="ti ti-circle-x" class="text-danger" style="font-size: 2rem;" />
                                        @elseif($withdrawal->status == 'canceled')
                                            <x-core::icon name="ti ti-ban" class="text-secondary" style="font-size: 2rem;" />
                                        @endif
                                    </div>
                                </div>
                                <div class="bb-customer-card-details">
                                    <div class="bb-customer-card-name">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="fw-bold">{{ trans('plugins/affiliate-pro::withdrawal.withdrawal_id') }} #{{ $withdrawal->id }}</span>
                                            <span class="fw-bold text-primary">{{ format_price($withdrawal->amount) }}</span>
                                        </div>
                                    </div>

                                    <div class="bb-customer-card-meta">
                                        <div class="withdrawal-details">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <div class="info-item">
                                                        <span class="label">{{ trans('plugins/affiliate-pro::withdrawal.payment_method') }}:</span>
                                                        <span class="value">{{ $withdrawal->payment_method }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-item">
                                                        <span class="label">{{ trans('plugins/affiliate-pro::withdrawal.status') }}:</span>
                                                        <span class="value">
                                                            @if($withdrawal->status == 'pending')
                                                                <span class="badge bg-warning text-dark">{{ trans('plugins/affiliate-pro::withdrawal.statuses.pending') }}</span>
                                                            @elseif($withdrawal->status == 'processing')
                                                                <span class="badge bg-info">{{ trans('plugins/affiliate-pro::withdrawal.statuses.processing') }}</span>
                                                            @elseif($withdrawal->status == 'approved')
                                                                <span class="badge bg-success">{{ trans('plugins/affiliate-pro::withdrawal.statuses.approved') }}</span>
                                                            @elseif($withdrawal->status == 'rejected')
                                                                <span class="badge bg-danger">{{ trans('plugins/affiliate-pro::withdrawal.statuses.rejected') }}</span>
                                                            @elseif($withdrawal->status == 'canceled')
                                                                <span class="badge bg-secondary">{{ trans('plugins/affiliate-pro::withdrawal.statuses.canceled') }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-item">
                                                        <span class="label">{{ trans('plugins/affiliate-pro::withdrawal.date') }}:</span>
                                                        <span class="value">{{ $withdrawal->created_at->translatedFormat('M d, Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($withdrawal->payment_details)
                                            <div class="withdrawal-payment-details mt-2">
                                                <small class="text-muted">
                                                    <strong>{{ trans('plugins/affiliate-pro::withdrawal.payment_details') }}:</strong>
                                                    {{ Str::limit($withdrawal->payment_details, 100) }}
                                                </small>
                                            </div>
                                        @endif

                                        @if($withdrawal->admin_note)
                                            <div class="withdrawal-admin-note mt-2">
                                                <small class="text-info">
                                                    <strong>{{ trans('plugins/affiliate-pro::affiliate.admin_note') }}:</strong>
                                                    {{ $withdrawal->admin_note }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if (!$loop->last)
                                <hr class="my-3">
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-3">
                        {!! $withdrawals->links() !!}
                    </div>
                @else
                    <div class="text-center p-4">
                        <div class="empty-state">
                            <x-core::icon name="ti ti-wallet-off" class="text-muted mb-3" style="font-size: 3rem;" />
                            <h5 class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_withdrawals_yet') }}</h5>
                            <p class="text-muted">{{ trans('plugins/affiliate-pro::withdrawal.no_withdrawals') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- JavaScript for Withdrawal Management --}}
    <script>
    $(document).ready(function() {
        // Handle payment method change
        $('input[name="payment_method"]').on('change', function() {
            var method = $(this).val();

            // Hide all help texts
            $('.payment-details-help').hide();

            // Show appropriate help text based on selected method
            if (method === 'paypal') {
                $('#paypal-help').show();
                $('#payment-details-label').text('{{ trans('plugins/affiliate-pro::affiliate.paypal_email_id') }}');
                $('#payment_details').attr('placeholder', '{{ trans('plugins/affiliate-pro::affiliate.enter_paypal_email') }}');
            } else if (method === 'bank_transfer') {
                $('#bank_transfer-help').show();
                $('#payment-details-label').text('{{ trans('plugins/affiliate-pro::affiliate.bank_account_details') }}');
                $('#payment_details').attr('placeholder', '{{ trans('plugins/affiliate-pro::affiliate.enter_bank_details') }}');
            } else if (method === 'stripe') {
                $('#stripe-help').show();
                $('#payment-details-label').text('{{ trans('plugins/affiliate-pro::affiliate.additional_information') }}');
                $('#payment_details').attr('placeholder', '{{ trans('plugins/affiliate-pro::affiliate.enter_stripe_info') }}');
            } else if (method === 'other') {
                $('#payment-details-label').text('{{ trans('plugins/affiliate-pro::withdrawal.payment_details') }}');
                $('#payment_details').attr('placeholder', '{{ trans('plugins/affiliate-pro::withdrawal.payment_details_placeholder') }}');
            } else {
                $('#payment-details-label').text('{{ trans('plugins/affiliate-pro::withdrawal.payment_details') }}');
                $('#payment_details').attr('placeholder', '{{ trans('plugins/affiliate-pro::withdrawal.payment_details_placeholder') }}');
            }
        });

        // Form submission
        $('#withdrawal-form').on('submit', function(e) {
            e.preventDefault();

            var amount = parseFloat($('#amount').val());
            var availableBalance = parseFloat('{{ $affiliate->balance }}');
            var minimumAmount = parseFloat('{{ app('affiliate-helper')->getMinimumWithdrawalAmount() }}');

            if (amount > availableBalance) {
                window.showAlert('error', '{{ trans('plugins/affiliate-pro::withdrawal.insufficient_balance') }}');
                return false;
            }

            if (amount < minimumAmount) {
                window.showAlert('error', '{{ trans('plugins/affiliate-pro::withdrawal.minimum_amount', ['amount' => format_price(app('affiliate-helper')->getMinimumWithdrawalAmount())]) }}');
                return false;
            }

            var form = $(this);
            var submitBtn = $('#submit-withdrawal');

            $.ajax({
                type: 'POST',
                cache: false,
                url: form.prop('action'),
                data: form.serialize(),
                beforeSend: () => {
                    submitBtn.prop('disabled', true).addClass('button-loading');
                },
                success: res => {
                    if (!res.error) {
                        form.find('input[type=text], input[type=number], textarea, select').val('');
                        window.showAlert('success', res.message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        window.showAlert('error', res.message);
                    }
                },
                error: res => {
                    window.showAlert('error', res.responseJSON.message);
                },
                complete: () => {
                    submitBtn.prop('disabled', false).removeClass('button-loading');
                }
            });
        });
    });
    </script>

    {{-- CSS Styles --}}
    <style>
    /* Payment Method Cards */
    .payment-method-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .payment-method-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        padding-top: 3px;
    }

    .payment-method-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-method-label {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        background-color: #fff;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin: 0;
        min-height: 80px;
        position: relative;
        overflow: hidden;
    }

    .payment-method-label:hover {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.02);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .payment-method-input:checked + .payment-method-label {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.05);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
    }

    .payment-method-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 1rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .payment-method-input:checked + .payment-method-label .payment-method-icon {
        background-color: var(--bs-primary);
        color: white;
    }

    .payment-method-icon svg {
        width: 24px;
        height: 24px;
        color: var(--bs-primary);
        transition: color 0.3s ease;
    }

    .payment-method-input:checked + .payment-method-label .payment-method-icon svg {
        color: white;
    }

    .payment-method-info {
        flex: 1;
        min-width: 0;
    }

    .payment-method-title {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
        margin: 0 0 0.25rem 0;
        line-height: 1.2;
    }

    .payment-method-description {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.3;
    }

    .payment-method-check {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        margin-left: 1rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
        opacity: 0;
    }

    .payment-method-input:checked + .payment-method-label .payment-method-check {
        border-color: var(--bs-primary);
        background-color: var(--bs-primary);
        opacity: 1;
    }

    .payment-method-check svg {
        width: 14px;
        height: 14px;
        color: white;
    }

    .balance-stat {
        display: flex;
        align-items: center;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .balance-stat:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .balance-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 1rem;
    }

    .balance-info h6 {
        margin: 0;
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    .balance-info p {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .withdrawal-item {
        transition: all 0.3s ease;
        border-radius: 8px;
        padding: 1rem;
        background-color: #f8f9fa;
        margin-bottom: 1rem;
    }

    .withdrawal-item:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .withdrawal-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        margin-bottom: 0.5rem;
    }

    .info-item .label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    .info-item .value {
        font-size: 0.9rem;
        color: #212529;
        margin-top: 0.25rem;
    }

    .withdrawal-payment-details,
    .withdrawal-admin-note {
        padding: 0.5rem;
        background-color: rgba(var(--bs-info-rgb), 0.1);
        border-radius: 4px;
        border-left: 3px solid var(--bs-info);
    }

    .withdrawal-admin-note {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
        border-left-color: var(--bs-warning);
    }

    .empty-state {
        padding: 2rem;
    }

    .balance-overview-card .bb-customer-card-body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .withdrawal-request-card .bb-customer-card-body {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    }

    @media (max-width: 768px) {
        /* Payment Method Cards Mobile */
        .payment-method-cards {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .payment-method-label {
            padding: 1rem;
            min-height: 70px;
        }

        .payment-method-icon {
            width: 40px;
            height: 40px;
            margin-right: 0.75rem;
        }

        .payment-method-icon svg {
            width: 20px;
            height: 20px;
        }

        .payment-method-title {
            font-size: 0.9rem;
        }

        .payment-method-description {
            font-size: 0.8rem;
        }

        .payment-method-check {
            width: 20px;
            height: 20px;
            margin-left: 0.75rem;
        }

        .payment-method-check svg {
            width: 12px;
            height: 12px;
        }

        .withdrawal-details .row {
            flex-direction: column;
        }

        .withdrawal-details .col-md-4 {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .info-item {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .info-item .value {
            margin-top: 0;
            text-align: right;
        }

        .balance-stat {
            flex-direction: column;
            text-align: center;
        }

        .balance-icon {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
    }
    </style>
@endsection
