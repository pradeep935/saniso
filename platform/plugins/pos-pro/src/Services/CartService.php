<?php

namespace Botble\PosPro\Services;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Repositories\Interfaces\DiscountInterface;
use Botble\PosPro\Facades\PosProHelper;
use Exception;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'pos_cart';

    protected string $couponSessionKey = 'pos_coupon_code';

    protected string $shippingAmountKey = 'pos_shipping_amount';

    protected string $manualDiscountKey = 'pos_manual_discount';

    protected string $manualDiscountDescriptionKey = 'pos_manual_discount_description';

    protected string $manualDiscountTypeKey = 'pos_manual_discount_type';

    protected string $customerIdKey = 'pos_customer_id';

    protected string $paymentMethodKey = 'pos_payment_method';

    public function getCart(): array
    {
        $cart = collect(Session::get($this->sessionKey, []));

        // Calculate subtotal
        $subtotal = $cart->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Get coupon discount if any
        $couponCode = Session::get($this->couponSessionKey);
        $couponDiscount = 0;
        $couponDiscountType = null;

        if ($couponCode) {
            // Get discount from database
            $discount = app(DiscountInterface::class)
                ->getModel()
                ->where('code', $couponCode)
                ->where('type', 'coupon')
                ->where('start_date', '<=', now())
                ->where(function ($query) {
                    return $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->first();

            if ($discount) {
                $couponDiscountType = $discount->type_option;

                if ($couponDiscountType === 'percentage') {
                    $couponDiscount = $subtotal * $discount->value / 100;
                } elseif ($couponDiscountType === 'fixed') {
                    $couponDiscount = $discount->value;
                }

                // Make sure discount doesn't exceed subtotal
                $couponDiscount = min($couponDiscount, $subtotal);
            }
        }

        // Get manual discount if any
        $manualDiscountValue = (float) Session::get($this->manualDiscountKey, 0);
        $manualDiscountDescription = Session::get($this->manualDiscountDescriptionKey, '');
        $manualDiscountType = Session::get($this->manualDiscountTypeKey, 'fixed');

        // Calculate the actual discount amount based on type
        $manualDiscount = $manualDiscountValue;
        if ($manualDiscountType === 'percentage') {
            $manualDiscount = $subtotal * $manualDiscountValue / 100;
        }

        // Make sure manual discount doesn't exceed subtotal minus coupon discount
        $manualDiscount = min($manualDiscount, $subtotal - $couponDiscount);

        $subtotalAfterDiscount = $subtotal - $couponDiscount - $manualDiscount;

        // Calculate tax for each product based on its tax rate
        $taxDetails = [];
        $totalTax = 0;

        if (EcommerceHelper::isTaxEnabled()) {
            foreach ($cart as $item) {
                $product = Product::find($item['id']);

                if ($product) {
                    // Get tax percentage for the product
                    $taxRate = $product->total_taxes_percentage;

                    // Calculate tax amount for this item
                    $itemPrice = $item['price'] * $item['quantity'];
                    $itemTaxAmount = ($itemPrice * $taxRate) / 100;

                    // Add to tax details
                    $taxDetails[] = [
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'tax_rate' => $taxRate,
                        'tax_amount' => $itemTaxAmount,
                    ];

                    $totalTax += $itemTaxAmount;
                }
            }
        }

        // Get shipping amount if any
        $shippingAmount = (float) Session::get($this->shippingAmountKey, 0);

        $total = $subtotalAfterDiscount + $totalTax + $shippingAmount;

        // Get customer ID and payment method from session
        $customerId = Session::get($this->customerIdKey);
        $defaultMethod = PosProHelper::getDefaultPaymentMethod();

        // Map the POS payment method to the corresponding PaymentMethodEnum constant
        $paymentMethodMap = [
            'cash' => POS_PRO_CASH_PAYMENT_METHOD_NAME,
            'card' => POS_PRO_CARD_PAYMENT_METHOD_NAME,
            'other' => POS_PRO_OTHER_PAYMENT_METHOD_NAME,
        ];

        $paymentMethod = Session::get($this->paymentMethodKey, $defaultMethod);
        $paymentMethodEnum = $paymentMethodMap[$paymentMethod] ?? $paymentMethodMap['cash'];

        // Get customer information if customer ID is set
        $customer = null;
        if ($customerId) {
            $customer = Customer::query()->find($customerId);
        }

        return [
            'items' => $cart,
            'subtotal' => $subtotal,
            'subtotal_formatted' => format_price($subtotal),
            'coupon_code' => $couponCode,
            'coupon_discount' => $couponDiscount,
            'coupon_discount_formatted' => format_price($couponDiscount),
            'coupon_discount_type' => $couponDiscountType,
            'manual_discount' => $manualDiscount,
            'manual_discount_value' => $manualDiscountValue,
            'manual_discount_type' => $manualDiscountType,
            'manual_discount_formatted' => format_price($manualDiscount),
            'manual_discount_description' => $manualDiscountDescription,
            'tax' => $totalTax,
            'tax_formatted' => format_price($totalTax),
            'tax_details' => $taxDetails,
            'shipping_amount' => $shippingAmount,
            'shipping_amount_formatted' => format_price($shippingAmount),
            'total' => $total,
            'total_formatted' => format_price($total),
            'count' => $cart->count(),
            'customer_id' => $customerId,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ] : null,
            'payment_method' => $paymentMethod,
            'payment_method_enum' => $paymentMethodEnum,
        ];
    }

    public function addToCart(int $productId, int $quantity = 1, array $attributeIds = []): array
    {
        /**
         * @var Product $product
         */
        $product = Product::query()->findOrFail($productId);
        $attributes = [];

        if ($attributeIds) {
            $attributeItems = [];

            foreach ($attributeIds as $setId => $attributeId) {
                if (empty($setId) || empty($attributeId)) {
                    continue;
                }

                $setId = (int) $setId;
                $attributeId = (int) $attributeId;

                $attributeSet = ProductAttributeSet::query()->find($setId);
                $attribute = ProductAttribute::query()->find($attributeId);

                if ($attributeSet && $attribute) {
                    $attributeItems[] = [
                        'set' => $attributeSet->title,
                        'value' => $attribute->title,
                    ];
                }
            }

            if (! empty($attributeItems)) {
                $attributes = $attributeItems;
            }
        }

        if ($product->isOutOfStock() && ! $product->allow_checkout_when_out_of_stock) {
            throw new Exception(trans('plugins/pos-pro::pos.product_is_out_of_stock'));
        }

        if ($product->with_storehouse_management && $product->quantity < $quantity) {
            throw new Exception(trans('plugins/pos-pro::pos.insufficient_stock'));
        }

        $cart = collect(Session::get($this->sessionKey, []));
        $existingItem = $cart->firstWhere('id', $product->id);

        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + $quantity;
            if ($product->with_storehouse_management && $product->quantity < $newQuantity) {
                throw new Exception(trans('plugins/pos-pro::pos.insufficient_stock'));
            }

            $cart = $cart->map(function ($item) use ($product, $quantity, $attributes) {
                if ($item['id'] === $product->id) {
                    $item['quantity'] += $quantity;
                    if ($attributes) {
                        $item['attributes'] = $attributes;
                    }
                }

                return $item;
            });
        } else {
            // Get tax information
            $taxRate = $product->total_taxes_percentage;

            $cartItem = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->image,
                'price' => $product->sale_price ?: $product->price,
                'quantity' => $quantity,
                'tax_rate' => $taxRate,
            ];

            // Add attributes if available
            if ($attributes) {
                $cartItem['attributes'] = $attributes;
            }

            $cart->push($cartItem);
        }

        Session::put($this->sessionKey, $cart->all());

        return [
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.product_added_to_cart'),
        ];
    }

    public function updateQuantity(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new Exception(trans('plugins/pos-pro::pos.invalid_quantity'));
        }

        $product = Product::query()->findOrFail($productId);

        if ($product->with_storehouse_management && $product->quantity < $quantity) {
            throw new Exception(trans('plugins/pos-pro::pos.insufficient_stock'));
        }

        $cart = collect(Session::get($this->sessionKey, []));
        $cart = $cart->map(function ($item) use ($productId, $quantity) {
            if ($item['id'] === $productId) {
                $item['quantity'] = $quantity;
            }

            return $item;
        });

        Session::put($this->sessionKey, $cart->all());

        return [
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.cart_updated'),
        ];
    }

    public function removeFromCart(int $productId): array
    {
        $cart = collect(Session::get($this->sessionKey, []));
        $cart = $cart->filter(function ($item) use ($productId) {
            return $item['id'] !== $productId;
        });

        Session::put($this->sessionKey, $cart->all());

        return [
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.product_removed_from_cart'),
        ];
    }

    public function clearCart(): array
    {
        Session::forget($this->sessionKey);
        Session::forget($this->couponSessionKey);
        Session::forget($this->shippingAmountKey);
        Session::forget($this->manualDiscountKey);
        Session::forget($this->manualDiscountDescriptionKey);
        Session::forget($this->manualDiscountTypeKey);

        return [
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.cart_cleared'),
        ];
    }

    public function resetCustomerAndPayment(): array
    {
        Session::forget($this->customerIdKey);
        Session::forget($this->paymentMethodKey);

        return [
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.customer_and_payment_reset'),
        ];
    }

    public function applyCoupon(string $couponCode): array
    {
        $couponCode = trim($couponCode);

        // Validate coupon code
        $discount = app(DiscountInterface::class)
            ->getModel()
            ->where('code', $couponCode)
            ->where('type', 'coupon')
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                return $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (! $discount) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.invalid_coupon'),
            ];
        }

        // Check if coupon is expired
        if ($discount->end_date && $discount->end_date->isPast()) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.expired_coupon'),
            ];
        }

        // Check if coupon has reached usage limit
        if ($discount->quantity > 0 && $discount->total_used >= $discount->quantity) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.coupon_used'),
            ];
        }

        // Store coupon code in session
        Session::put($this->couponSessionKey, $couponCode);

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.applied_coupon_success', ['code' => $couponCode]),
        ];
    }

    public function removeCoupon(): array
    {
        $couponCode = Session::get($this->couponSessionKey);

        if (! $couponCode) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.not_used'),
            ];
        }

        Session::forget($this->couponSessionKey);

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.removed_coupon_success', ['code' => $couponCode]),
        ];
    }

    public function updateShippingAmount(float $amount): array
    {
        if ($amount < 0) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.invalid_shipping_amount'),
            ];
        }

        Session::put($this->shippingAmountKey, $amount);

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.shipping_amount_updated'),
        ];
    }

    public function updateManualDiscount(float $amount, string $description = '', string $type = 'fixed'): array
    {
        if ($amount < 0) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.invalid_discount_amount'),
            ];
        }

        $cart = collect(Session::get($this->sessionKey, []));
        $subtotal = $cart->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Get coupon discount if any
        $couponDiscount = 0;
        $couponCode = Session::get($this->couponSessionKey);

        if ($couponCode) {
            $discount = app(DiscountInterface::class)
                ->getModel()
                ->where('code', $couponCode)
                ->where('type', 'coupon')
                ->where('start_date', '<=', now())
                ->where(function ($query) {
                    return $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->first();

            if ($discount) {
                if ($discount->type_option === 'percentage') {
                    $couponDiscount = $subtotal * $discount->value / 100;
                } elseif ($discount->type_option === 'fixed') {
                    $couponDiscount = $discount->value;
                }

                $couponDiscount = min($couponDiscount, $subtotal);
            }
        }

        // Calculate the actual discount amount based on type
        $discountAmount = $amount;
        if ($type === 'percentage') {
            if ($amount > 100) {
                return [
                    'error' => true,
                    'message' => trans('plugins/pos-pro::pos.percentage_discount_cannot_exceed_100'),
                ];
            }
            $discountAmount = $subtotal * $amount / 100;
        }

        // Make sure discount doesn't exceed subtotal minus coupon discount
        $maxDiscount = $subtotal - $couponDiscount;

        if ($discountAmount > $maxDiscount) {
            return [
                'error' => true,
                'message' => trans('plugins/pos-pro::pos.discount_cannot_exceed_subtotal'),
            ];
        }

        Session::put($this->manualDiscountKey, $amount);
        Session::put($this->manualDiscountDescriptionKey, $description);
        Session::put($this->manualDiscountTypeKey, $type);

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.discount_amount_updated'),
        ];
    }

    public function removeManualDiscount(): array
    {
        Session::forget($this->manualDiscountKey);
        Session::forget($this->manualDiscountDescriptionKey);
        Session::forget($this->manualDiscountTypeKey);

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.discount_removed'),
        ];
    }

    public function updateCustomer(?int $customerId): array
    {
        if ($customerId) {
            Session::put($this->customerIdKey, $customerId);
        } else {
            Session::forget($this->customerIdKey);
        }

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => $customerId
                ? trans('plugins/pos-pro::pos.customer_updated')
                : trans('plugins/pos-pro::pos.customer_removed'),
        ];
    }

    public function updatePaymentMethod(string $paymentMethod): array
    {
        Session::put($this->paymentMethodKey, $paymentMethod);

        return [
            'error' => false,
            'cart' => $this->getCart(),
            'message' => trans('plugins/pos-pro::pos.payment_method_updated'),
        ];
    }
}
