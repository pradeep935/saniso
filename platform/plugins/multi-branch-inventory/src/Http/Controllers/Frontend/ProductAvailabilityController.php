<?php

namespace Botble\MultiBranchInventory\Http\Controllers\Frontend;

use Botble\Base\Http\Controllers\BaseController;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductAvailabilityController extends BaseController
{
    /**
     * Get product availability across all branches
     */
    public function getAvailability(Request $request, Product $product): JsonResponse
    {
        $availability = [];

        $branchInventories = BranchInventory::with('branch')
            ->where('product_id', $product->id)
            ->where('visible_online', true)
            ->where('only_visible_in_pos', false)
            ->where('quantity_available', '>', 0)
            ->get();

        foreach ($branchInventories as $inventory) {
            $branch = $inventory->branch;
            
            $availability[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'branch_code' => $branch->code,
                'address' => $branch->full_address,
                'city' => $branch->city,
                'phone' => $branch->phone,
                'quantity_available' => $inventory->quantity_available,
                'price' => $inventory->effective_price,
                'can_reserve' => true,
                'storage_location' => $inventory->storage_location,
            ];
        }

        // Sort by quantity available (highest first)
        usort($availability, function ($a, $b) {
            return $b['quantity_available'] - $a['quantity_available'];
        });

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ],
            'availability' => $availability,
            'total_branches' => count($availability),
            'total_quantity' => array_sum(array_column($availability, 'quantity_available')),
        ]);
    }

    /**
     * Reserve product for pickup at branch
     */
    public function reserveForPickup(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'pickup_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $branchInventory = BranchInventory::where([
                'branch_id' => $request->branch_id,
                'product_id' => $product->id,
            ])->first();

            if (!$branchInventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not available at this branch',
                ], 404);
            }

            if (!$branchInventory->isVisibleOnline()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not available for online reservation',
                ], 400);
            }

            if ($branchInventory->quantity_available < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient quantity available',
                    'available_quantity' => $branchInventory->quantity_available,
                ], 400);
            }

            // Reserve the stock
            if ($branchInventory->reserveStock($request->quantity)) {
                
                // Create a pickup reservation record
                $reservation = \Botble\MultiBranchInventory\Models\PickupReservation::create([
                    'branch_id' => $request->branch_id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'pickup_date' => $request->pickup_date,
                    'notes' => $request->notes,
                    'status' => 'reserved',
                    'reservation_number' => 'RSV' . date('Ymd') . str_pad(
                        \Botble\MultiBranchInventory\Models\PickupReservation::whereDate('created_at', today())->count() + 1,
                        4,
                        '0',
                        STR_PAD_LEFT
                    ),
                    'expires_at' => now()->addDays(3), // Reservation expires in 3 days
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Product reserved successfully for pickup',
                    'reservation' => [
                        'number' => $reservation->reservation_number,
                        'branch_name' => $branchInventory->branch->name,
                        'pickup_date' => $request->pickup_date,
                        'expires_at' => $reservation->expires_at,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve product',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get nearby branches with product availability
     */
    public function getNearbyBranches(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'product_id' => 'nullable|exists:ec_products,id',
        ]);

        $query = Branch::where('status', 'active');

        // Filter by city if provided
        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filter by postal code if provided
        if ($request->postal_code) {
            $query->where('postal_code', 'like', '%' . $request->postal_code . '%');
        }

        $branches = $query->get()->map(function ($branch) use ($request) {
            $branchData = [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'address' => $branch->full_address,
                'city' => $branch->city,
                'postal_code' => $branch->postal_code,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'manager_name' => $branch->manager_name,
            ];

            // Add product availability if product_id is provided
            if ($request->product_id) {
                $inventory = BranchInventory::where([
                    'branch_id' => $branch->id,
                    'product_id' => $request->product_id,
                ])->first();

                $branchData['product_availability'] = $inventory ? [
                    'available' => $inventory->isVisibleOnline(),
                    'quantity' => $inventory->quantity_available,
                    'price' => $inventory->effective_price,
                    'storage_location' => $inventory->storage_location,
                ] : [
                    'available' => false,
                    'quantity' => 0,
                    'price' => null,
                    'storage_location' => null,
                ];
            }

            return $branchData;
        });

        // TODO: Implement distance calculation if lat/lng provided
        if ($request->latitude && $request->longitude) {
            // Sort by distance (placeholder - would need proper distance calculation)
            // For now, just return as is
        }

        return response()->json([
            'success' => true,
            'branches' => $branches,
            'count' => $branches->count(),
        ]);
    }

    /**
     * Check if product can be ordered for local pickup
     */
    public function checkPickupAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'quantity' => 'required|integer|min:1',
            'postal_code' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $availableBranches = BranchInventory::with('branch')
            ->where('product_id', $request->product_id)
            ->where('visible_online', true)
            ->where('only_visible_in_pos', false)
            ->where('quantity_available', '>=', $request->quantity)
            ->get()
            ->map(function ($inventory) {
                return [
                    'branch_id' => $inventory->branch->id,
                    'branch_name' => $inventory->branch->name,
                    'city' => $inventory->branch->city,
                    'postal_code' => $inventory->branch->postal_code,
                    'available_quantity' => $inventory->quantity_available,
                    'price' => $inventory->effective_price,
                ];
            });

        // Filter by location if provided
        if ($request->city) {
            $availableBranches = $availableBranches->filter(function ($branch) use ($request) {
                return stripos($branch['city'], $request->city) !== false;
            });
        }

        if ($request->postal_code) {
            $availableBranches = $availableBranches->filter(function ($branch) use ($request) {
                return strpos($branch['postal_code'], $request->postal_code) === 0;
            });
        }

        return response()->json([
            'success' => true,
            'available' => $availableBranches->isNotEmpty(),
            'branches' => $availableBranches->values(),
            'total_quantity' => $availableBranches->sum('available_quantity'),
        ]);
    }
}