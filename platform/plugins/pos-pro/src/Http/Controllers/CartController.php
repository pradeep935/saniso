<?php

namespace Botble\PosPro\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Customer;
use Botble\PosPro\Services\CartService;
use Exception;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    public function __construct(protected CartService $cartService)
    {
    }

    protected function getCustomers()
    {
        return Customer::query()
            ->select(['id', 'name', 'email', 'phone'])->latest()
            ->limit(20)
            ->get();
    }

    /**
     * Check if a string is valid JSON
     *
     * @param string $string
     * @return bool
     */
    protected function isJson($string)
    {
        if (! is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function renderCart(array $cart): string
    {
        $customers = $this->getCustomers();

        return view('plugins/pos-pro::partials.cart', compact('cart', 'customers'))->render();
    }

    public function add(Request $request, BaseHttpResponse $response)
    {
        try {
            $attributes = (array) $request->input('attributes', []);
            $attributes = array_filter($attributes);

            $result = $this->cartService->addToCart(
                $request->input('product_id'),
                $request->input('quantity', 1),
                $attributes
            );

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            if (! isset($cart['count']) && isset($cart['items'])) {
                $cart['count'] = count($cart['items']);
            }

            return $response
                ->setData([
                    'cart' => $cart,
                    'message' => $result['message'],
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            BaseHelper::logError($e);

            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function update(Request $request, BaseHttpResponse $response)
    {
        try {
            $result = $this->cartService->updateQuantity(
                $request->input('product_id'),
                $request->input('quantity')
            );

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            BaseHelper::logError($e);

            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function remove(Request $request, BaseHttpResponse $response)
    {
        try {
            $result = $this->cartService->removeFromCart($request->input('product_id'));

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function clear(BaseHttpResponse $response)
    {
        try {
            $result = $this->cartService->clearCart();

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function applyCoupon(Request $request, BaseHttpResponse $response)
    {
        try {
            $couponCode = $request->input('coupon_code');

            if (! $couponCode) {
                return $response
                    ->setError()
                    ->setMessage(trans('plugins/pos-pro::pos.please_enter_coupon_code'))
                    ->toApiResponse();
            }

            $result = $this->cartService->applyCoupon($couponCode);

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function removeCoupon(BaseHttpResponse $response)
    {
        try {
            $result = $this->cartService->removeCoupon();

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function updateShippingAmount(Request $request, BaseHttpResponse $response)
    {
        try {
            $amount = (float) $request->input('shipping_amount', 0);

            $result = $this->cartService->updateShippingAmount($amount);

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function updateManualDiscount(Request $request, BaseHttpResponse $response)
    {
        try {
            $amount = (float) $request->input('discount_amount', 0);
            $description = (string) $request->input('discount_description', '');
            $type = (string) $request->input('discount_type', 'fixed');

            $result = $this->cartService->updateManualDiscount($amount, $description, $type);

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function removeManualDiscount(BaseHttpResponse $response)
    {
        try {
            $result = $this->cartService->removeManualDiscount();

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function updateCustomer(Request $request, BaseHttpResponse $response)
    {
        try {
            $customerId = $request->input('customer_id');

            $result = $this->cartService->updateCustomer($customerId);

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function updatePaymentMethod(Request $request, BaseHttpResponse $response)
    {
        try {
            $paymentMethod = $request->input('payment_method', 'cash');

            $result = $this->cartService->updatePaymentMethod($paymentMethod);

            if ($result['error']) {
                return $response
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function resetCustomerAndPayment(BaseHttpResponse $response)
    {
        try {
            $result = $this->cartService->resetCustomerAndPayment();

            $cart = $result['cart'];
            $cart['html'] = $this->renderCart($cart);

            return $response
                ->setData([
                    'cart' => $cart,
                ])
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }
}
