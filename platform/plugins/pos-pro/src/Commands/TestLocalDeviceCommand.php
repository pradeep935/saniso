<?php

namespace Botble\PosPro\Commands;

use Botble\ACL\Models\User;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Models\Order;
use Botble\PosPro\Models\PosDeviceConfig;
use Illuminate\Console\Command;

class TestLocalDeviceCommand extends Command
{
    protected $signature = 'pos:test-local-device {user_id} {order_id}';

    protected $description = 'Test sending order data to local device';

    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $orderId = $this->argument('order_id');

        $user = User::find($userId);
        if (! $user) {
            $this->error("User with ID {$userId} not found");

            return 1;
        }

        $order = Order::find($orderId);
        if (! $order) {
            $this->error("Order with ID {$orderId} not found");

            return 1;
        }

        $deviceConfig = PosDeviceConfig::getForUser($userId);

        $this->info("Testing local device API call for user: {$user->name}");
        $this->info('Device IP: ' . ($deviceConfig?->device_ip ?: 'Not set'));
        $this->info('Device Name: ' . ($deviceConfig?->device_name ?: 'Not set'));
        $this->info('Device Active: ' . ($deviceConfig?->is_active ? 'Yes' : 'No'));
        $this->info("Order: {$order->code}");

        // Simulate the user being authenticated
        auth()->login($user);

        // Fire the OrderCreated event to test the listener
        event(new OrderCreated($order));

        $this->info('OrderCreated event fired. Check logs for API call results.');

        return 0;
    }
}
