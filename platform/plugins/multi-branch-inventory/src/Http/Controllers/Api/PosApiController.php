<?php

namespace Botble\MultiBranchInventory\Http\Controllers\Api;

use Botble\Base\Http\Controllers\BaseController;
use Botble\MultiBranchInventory\Services\PosIntegrationService;
use Botble\MultiBranchInventory\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PosApiController extends BaseController
{
    protected PosIntegrationService $posService;

    public function __construct(PosIntegrationService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * Get all available products for POS in a branch
     */
    public function getBranchProducts(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
        ]);

        $products = $this->posService->getAvailableProductsForBranch($request->branch_id);

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Search products in branch for POS
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'search' => 'required|string|min:2',
        ]);

        $products = $this->posService->searchProductsInBranch(
            $request->branch_id,
            $request->search
        );

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Check product availability by barcode/SKU scan
     */
    public function scanProduct(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'code' => 'required|string', // EAN, SKU, or product code
            'quantity' => 'integer|min:1',
        ]);

        $quantity = $request->get('quantity', 1);
        $availability = $this->posService->checkProductAvailability(
            $request->branch_id,
            $request->code,
            $quantity
        );

        return response()->json([
            'success' => $availability['available'],
            'data' => $availability,
        ]);
    }

    /**
     * Process a POS sale
     */
    public function processSale(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'sale_reference' => 'nullable|string',
        ]);

        try {
            $result = $this->posService->processSale(
                $request->branch_id,
                $request->items
            );

            if ($result['success']) {
                // Sync to main inventory if needed
                $this->posService->syncToMainInventory([
                    'branch_id' => $request->branch_id,
                    'items' => $request->items,
                    'sale_reference' => $request->sale_reference,
                ]);
            }

            return response()->json([
                'success' => $result['success'],
                'data' => $result['results'],
                'errors' => $result['errors'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sale processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get low stock alerts for branch
     */
    public function getLowStockAlerts(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
        ]);

        $alerts = $this->posService->getLowStockAlerts($request->branch_id);

        return response()->json([
            'success' => true,
            'data' => $alerts,
            'count' => $alerts->count(),
        ]);
    }

    /**
     * Get active branches for POS
     */
    public function getActiveBranches(): JsonResponse
    {
        $branches = Branch::where('status', 'active')
            ->select(['id', 'name', 'code', 'address', 'city'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Quick stock check for multiple products
     */
    public function bulkStockCheck(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required',
            'products.*.quantity' => 'integer|min:1',
        ]);

        $results = [];
        
        foreach ($request->products as $product) {
            $availability = $this->posService->checkProductAvailability(
                $request->branch_id,
                $product['id'],
                $product['quantity'] ?? 1
            );

            $results[] = array_merge([
                'requested_id' => $product['id'],
                'requested_quantity' => $product['quantity'] ?? 1,
            ], $availability);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Reserve products for a transaction (hold stock temporarily)
     */
    public function reserveProducts(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:ec_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'session_id' => 'required|string', // POS session identifier
        ]);

        try {
            $reservations = [];
            $errors = [];

            foreach ($request->items as $item) {
                $branchInventory = \Botble\MultiBranchInventory\Models\BranchInventory::where([
                    'branch_id' => $request->branch_id,
                    'product_id' => $item['product_id'],
                ])->first();

                if ($branchInventory && $branchInventory->reserveStock($item['quantity'])) {
                    $reservations[] = [
                        'product_id' => $item['product_id'],
                        'quantity_reserved' => $item['quantity'],
                    ];
                } else {
                    $errors[] = [
                        'product_id' => $item['product_id'],
                        'error' => 'Insufficient stock available',
                    ];
                }
            }

            return response()->json([
                'success' => empty($errors),
                'reservations' => $reservations,
                'errors' => $errors,
                'session_id' => $request->session_id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Release reserved products (if transaction cancelled)
     */
    public function releaseReservation(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:ec_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'session_id' => 'required|string',
        ]);

        try {
            foreach ($request->items as $item) {
                $branchInventory = \Botble\MultiBranchInventory\Models\BranchInventory::where([
                    'branch_id' => $request->branch_id,
                    'product_id' => $item['product_id'],
                ])->first();

                if ($branchInventory) {
                    $branchInventory->releaseReservedStock($item['quantity']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Reservation released successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Release failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}