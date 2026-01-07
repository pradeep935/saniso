<?php

namespace Botble\MultiBranchInventory\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use DB;

class BranchInventoryController extends BaseController
{
    /**
     * Show the form for editing the specified branch inventory.
     * Accepts either an ID or a BranchInventory model (route model binding).
     */
    public function edit($branchInventory)
    {
        if ($branchInventory instanceof BranchInventory) {
            $bi = $branchInventory->load(['product', 'branch']);
        } else {
            $bi = BranchInventory::with(['product', 'branch'])->findOrFail($branchInventory);
        }

        $this->pageTitle('Edit Branch Inventory');

        return view('plugins/multi-branch-inventory::branch-inventory.edit', [
            'branchInventory' => $bi,
        ]);
    }

    /**
     * Legacy / index view for older UI
     */
    public function index(Request $request)
    {
        $this->pageTitle('Branch Inventory (Legacy)');

        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        $inventories = BranchInventory::with(['product', 'branch'])
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('plugins/multi-branch-inventory::branch-inventory.index', compact('inventories', 'branches'));
    }

    /**
     * Store new inventory (simple wrapper around addProductToBranchInventory)
     */
    public function store(Request $request)
    {
        return $this->addProductToBranchInventory($request);
    }

    /**
     * Show adjust stock form
     */
    public function adjustStockForm(Request $request)
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'published')->orderBy('name')->limit(1000)->get();

        return view('plugins/multi-branch-inventory::branch-inventory.adjust-stock-form', compact('branches', 'products'));
    }

    /**
     * Show branch inventory or redirect to edit.
     */
    public function show($branchInventory)
    {
        if ($branchInventory instanceof BranchInventory) {
            $bi = $branchInventory->load(['product', 'branch']);
        } else {
            $bi = BranchInventory::with(['product', 'branch'])->find($branchInventory);
        }

        if (request()->wantsJson()) {
            if (!$bi) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }
            return response()->json(['success' => true, 'data' => $bi]);
        }

        if (!$bi) {
            abort(404);
        }

        // Reuse edit view as a lightweight details page
        return view('plugins/multi-branch-inventory::branch-inventory.edit', ['branchInventory' => $bi]);
    }

    /**
     * Return detailed info for a given inventory id or product id (used by JS)
     */
    public function getDetails(Request $request, $id)
    {
        // If an inventory record exists with this id, return it
        $inventory = BranchInventory::with(['product', 'branch'])->find($id);

        // If not found treat $id as product id and look up by product/branch request param
        if (!$inventory) {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $branchId = $request->get('branch_id');
            $branchInventory = null;
            if ($branchId) {
                $branchInventory = BranchInventory::where('product_id', $product->id)->where('branch_id', $branchId)->first();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'ecommerce_quantity' => $product->quantity ?? 0,
                    'branch_quantity' => $branchInventory ? $branchInventory->quantity_available : 0,
                    'has_branch_inventory' => (bool) $branchInventory,
                    'inventory' => $branchInventory,
                ],
            ]);
        }

        return response()->json(['success' => true, 'data' => $inventory]);
    }

    /**
     * Update branch inventory settings
     */
    public function update(Request $request, $branchInventory)
    {
        if ($branchInventory instanceof BranchInventory) {
            $bi = $branchInventory;
        } else {
            $bi = BranchInventory::findOrFail($branchInventory);
        }

        $data = $request->only(['minimum_stock', 'maximum_stock', 'local_price', 'storage_location']);
        $flags = ['visible_online', 'visible_in_pos', 'only_visible_in_pos'];
        foreach ($flags as $flag) {
            $data[$flag] = $request->has($flag) ? (bool) $request->get($flag) : false;
        }

        $bi->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Updated', 'inventory' => $bi]);
        }

        flash()->success('Branch inventory updated successfully');
        return redirect()->back();
    }

    /**
     * Adjust stock quantities (add/subtract/set)
     */
    public function adjustStock(Request $request, $branchInventory)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($branchInventory instanceof BranchInventory) {
            $bi = $branchInventory;
        } else {
            $bi = BranchInventory::findOrFail($branchInventory);
        }

        $qty = (int) $request->quantity;
        if ($request->adjustment_type === 'add') {
            $bi->quantity_on_hand += $qty;
            $bi->quantity_available += $qty;
        } elseif ($request->adjustment_type === 'subtract') {
            $bi->quantity_on_hand = max(0, $bi->quantity_on_hand - $qty);
            $bi->quantity_available = max(0, $bi->quantity_available - $qty);
        } else { // set
            $bi->quantity_on_hand = $qty;
            $bi->quantity_available = $qty;
        }

        $bi->save();
        $this->syncMainProductQuantity($bi->product_id);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'inventory' => $bi]);
        }

        flash()->success('Stock adjusted successfully');
        return redirect()->back();
    }

    /**
     * AJAX: return products for a branch
     */
    public function getBranchProducts(Request $request)
    {
        $branchId = $request->get('branch_id');
        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'branch_id required'], 400);
        }

        $products = Product::with(['branchInventories' => function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }])->where('status', 'published')->limit(1000)->get();

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Legacy API: Get branch stock
     */
    public function getBranchStock(Request $request)
    {
        $branchId = $request->get('branch_id');
        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'branch_id required'], 400);
        }

        $stocks = BranchInventory::where('branch_id', $branchId)->with('product')->get();
        return response()->json(['success' => true, 'data' => $stocks]);
    }

    /**
     * Legacy API: Reserve stock for a product in a branch
     */
    public function reserveStock(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $bi = BranchInventory::where('branch_id', $request->branch_id)
            ->where('product_id', $request->product_id)->first();

        if (!$bi) {
            return response()->json(['success' => false, 'message' => 'Inventory not found'], 404);
        }

        if ($bi->quantity_available < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock'], 409);
        }

        $bi->quantity_available -= $request->quantity;
        $bi->quantity_reserved += $request->quantity;
        $bi->save();

        return response()->json(['success' => true, 'inventory' => $bi]);
    }

    /**
     * Legacy API: Release reserved stock
     */
    public function releaseStock(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $bi = BranchInventory::where('branch_id', $request->branch_id)
            ->where('product_id', $request->product_id)->first();

        if (!$bi) {
            return response()->json(['success' => false, 'message' => 'Inventory not found'], 404);
        }

        $releaseQty = min($bi->quantity_reserved, (int) $request->quantity);
        $bi->quantity_reserved -= $releaseQty;
        $bi->quantity_available += $releaseQty;
        $bi->save();

        return response()->json(['success' => true, 'inventory' => $bi]);
    }
    /**
     * Simple inventory management page - Optimized for 11K+ products
     * Loads all published products with selected branch inventory attached
     */
    public function inventoryIndex(Request $request)
    {
        $this->pageTitle('Inventory Management');

        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $selectedBranch = $request->branch_id
            ? Branch::find($request->branch_id)
            : ($branches->first() ?? null);

        if ($selectedBranch) {
            // Load ALL published products with the selected branch's inventory attached
            $productQuery = Product::query()
                ->with(['branchInventories' => function ($q) use ($selectedBranch) {
                    $q->where('branch_id', $selectedBranch->id);
                }])
                ->where('status', 'published');

            // Apply search filter with mode selection (name or ean/barcode)
            if ($request->filled('search')) {
                $search = trim($request->get('search'));
                $mode = $request->get('search_mode', 'name');
                $like = '%' . $search . '%';

                if ($mode === 'ean') {
                    // Search by barcode (EAN)
                    $productQuery->where('barcode', 'like', $like);
                } else {
                    // Search by name (default) with relevance ordering
                    $escaped = addcslashes($search, "%_");
                    $productQuery->where('name', 'like', $like);
                    // When searching, apply relevance ordering if no custom sort is set
                    if (!$request->filled('sort')) {
                        $productQuery->orderByRaw(
                            "CASE 
                                WHEN name = ? THEN 0 
                                WHEN name LIKE ? THEN 1 
                                ELSE 2 
                             END",
                            [$escaped, $like]
                        );
                    }
                }
            }

            // Apply sorting
            $sort = $request->get('sort', 'name_asc');
            switch ($sort) {
                case 'name_desc':
                    $productQuery->orderBy('name', 'desc');
                    break;
                case 'qty_low':
                    $productQuery->orderBy('quantity', 'asc');
                    break;
                case 'qty_high':
                    $productQuery->orderBy('quantity', 'desc');
                    break;
                case 'sku_asc':
                    $productQuery->orderBy('sku', 'asc');
                    break;
                case 'price_low':
                    $productQuery->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $productQuery->orderBy('price', 'desc');
                    break;
                case 'name_asc':
                default:
                    $productQuery->orderBy('name', 'asc');
                    break;
            }

            // Paginate by products (100 per page)
            $products = $productQuery->paginate(100)->appends($request->except('page'));

            // Transform products to inventory display format
            $inventory = $products->setCollection(
                $products->getCollection()->map(function ($product) use ($selectedBranch) {
                    $branchInventory = $product->branchInventories->first();

                    return (object) [
                        'id' => $branchInventory ? $branchInventory->id : null,
                        'product_id' => $product->id,
                        'product' => $product,
                        'branch' => $selectedBranch,
                        'sku' => $branchInventory ? $branchInventory->sku : $product->sku,
                        'ean' => $branchInventory ? $branchInventory->ean : null,
                        'quantity_on_hand' => $branchInventory ? $branchInventory->quantity_on_hand : 0,
                        'quantity_reserved' => $branchInventory ? $branchInventory->quantity_reserved : 0,
                        'quantity_available' => $branchInventory ? $branchInventory->quantity_available : 0,
                        'minimum_stock' => $branchInventory ? $branchInventory->minimum_stock : 0,
                        'maximum_stock' => $branchInventory ? $branchInventory->maximum_stock : null,
                        'needs_replenishment' => $branchInventory ? (bool) $branchInventory->needs_replenishment : false,
                        'replenishment_quantity' => $branchInventory ? $branchInventory->replenishment_quantity : null,
                        'replenishment_requested_at' => $branchInventory ? $branchInventory->replenishment_requested_at : null,
                        'cost_price' => $branchInventory ? $branchInventory->cost_price : $product->cost_per_item,
                        'selling_price' => $branchInventory ? $branchInventory->selling_price : $product->price,
                        'visible_online' => $branchInventory ? $branchInventory->visible_online : true,
                        'visible_in_pos' => $branchInventory ? $branchInventory->visible_in_pos : false,
                        'only_visible_in_pos' => $branchInventory ? $branchInventory->only_visible_in_pos : false,
                        'has_branch_inventory' => (bool) $branchInventory,
                        'created_at' => $branchInventory ? $branchInventory->created_at : null,
                        'updated_at' => $branchInventory ? $branchInventory->updated_at : null,
                    ];
                })
            );

            // Reorder current page so items needing replenishment appear first
            $collection = $products->getCollection();
            $needs = $collection->filter(function ($item) { return isset($item->needs_replenishment) && $item->needs_replenishment; });
            $others = $collection->filter(function ($item) { return ! (isset($item->needs_replenishment) && $item->needs_replenishment); });
            $reordered = $needs->concat($others);
            $products->setCollection($reordered);

            // Rebuild inventory variable to use reordered collection
            $inventory = $products;

            // Calculate summary statistics
            $stats = [
                'total_products' => $products->total(),
                'total_in_inventory' => BranchInventory::where('branch_id', $selectedBranch->id)->count(),
                'low_stock_items' => BranchInventory::where('branch_id', $selectedBranch->id)->whereRaw('quantity_available <= minimum_stock')->count(),
                'out_of_stock' => BranchInventory::where('branch_id', $selectedBranch->id)->where('quantity_available', '<=', 0)->count(),
                'replenishment_requests' => BranchInventory::where('branch_id', $selectedBranch->id)->where('needs_replenishment', true)->count(),
                // show total items instead of monetary value
                'total_items' => (int) BranchInventory::where('branch_id', $selectedBranch->id)->sum('quantity_on_hand'),
            ];
        } else {
            $inventory = collect([]);
            $stats = [
                'total_products' => 0,
                'total_in_inventory' => 0,
                'low_stock_items' => 0,
                'out_of_stock' => 0,
                'total_value' => 0,
            ];
        }

        return view('plugins/multi-branch-inventory::branch-inventory.inventory-index', compact(
            'inventory', 'branches', 'selectedBranch', 'stats'
        ));
    }

    /**
     * Update inventory quantity via AJAX for simple inventory management
     */
    public function updateQuantity(Request $request)
    {
        try {
            \Log::info('Update quantity request received', $request->all());
            
            // Handle both inventory_id (from AJAX) and product_id/branch_id (direct calls)
            if ($request->has('inventory_id') && $request->inventory_id) {
                $validatedData = [
                    'inventory_id' => (int) $request->inventory_id,
                    'value' => (int) $request->value,
                    'field' => $request->field ?? 'quantity_available'
                ];
                
                $inventory = BranchInventory::find($validatedData['inventory_id']);
                if (!$inventory) {
                    \Log::error('Branch inventory not found', ['inventory_id' => $validatedData['inventory_id']]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Branch inventory not found'
                    ], 404);
                }
                
                \Log::info('Found inventory record', [
                    'inventory_id' => $inventory->id,
                    'current_quantity' => $inventory->quantity_available,
                    'product_id' => $inventory->product_id,
                    'branch_id' => $inventory->branch_id
                ]);
                
                $quantity = $validatedData['value'];
            } else {
                $validatedData = [
                    'product_id' => (int) $request->product_id,
                    'branch_id' => (int) $request->branch_id,
                    'quantity' => (int) $request->quantity
                ];
                
                $inventory = BranchInventory::where('product_id', $validatedData['product_id'])
                    ->where('branch_id', $validatedData['branch_id'])
                    ->first();
                $quantity = $validatedData['quantity'];
                
                // If inventory doesn't exist, create it
                if (!$inventory) {
                    $product = Product::find($validatedData['product_id']);
                    $branch = Branch::find($validatedData['branch_id']);
                    
                    if (!$product || !$branch) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Product or branch not found'
                        ], 404);
                    }
                    
                    \Log::info('Creating new branch inventory', [
                        'product_id' => $validatedData['product_id'],
                        'branch_id' => $validatedData['branch_id'],
                        'quantity' => $quantity
                    ]);
                    
                    $inventory = BranchInventory::updateOrCreate(
                        [
                            'branch_id' => $validatedData['branch_id'],
                            'product_id' => $validatedData['product_id'],
                        ],
                        [
                            'sku' => $product->sku,
                            'quantity_on_hand' => $quantity,
                            'quantity_available' => $quantity,
                            'quantity_reserved' => 0,
                            'minimum_stock' => 0,
                            'cost_price' => $product->cost_per_item ?? 0,
                            'selling_price' => $product->price ?? 0,
                            'visible_online' => true,
                            'visible_in_pos' => true,
                            'only_visible_in_pos' => false,
                        ]
                    );
                    
                    // Enable storehouse management for the product
                    $product->update(['with_storehouse_management' => true]);
                    
                    \Log::info('Branch inventory created/updated', [
                        'inventory_id' => $inventory->id,
                        'final_quantity' => $inventory->quantity_available
                    ]);
                }
            }
            
            \Log::info('🔍 QUANTITY UPDATE REQUEST RECEIVED', [
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'branch_id' => $inventory->branch_id,
                'current_quantity_available' => $inventory->quantity_available,
                'current_quantity_on_hand' => $inventory->quantity_on_hand,
                'current_quantity_reserved' => $inventory->quantity_reserved,
                'requested_new_quantity' => $quantity,
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Use database transaction with row locking to prevent race conditions
            DB::beginTransaction();
            try {
                // Lock the row for update to prevent concurrent modifications
                $lockedInventory = BranchInventory::where('id', $inventory->id)->lockForUpdate()->first();
                
                if (!$lockedInventory) {
                    throw new \Exception('Inventory record not found or locked');
                }
                
                // Log ALL branch inventories for this product BEFORE update
                $allBefore = BranchInventory::where('product_id', $lockedInventory->product_id)->get();
                \Log::info('=== BEFORE UPDATE - All branch inventories for product ' . $lockedInventory->product_id . ' ===');
                foreach ($allBefore as $bi) {
                    \Log::info("Branch {$bi->branch_id} (ID {$bi->id}): {$bi->quantity_available} available, {$bi->quantity_on_hand} on hand");
                }
                
                // Update the quantity field
                $updated = DB::table('mbi_branch_inventory')
                    ->where('id', $lockedInventory->id)
                    ->update([
                        'quantity_on_hand' => $quantity,
                        'quantity_available' => $quantity,
                        'updated_at' => now()
                    ]);
                
                \Log::info('📊 AFTER UPDATE', [
                    'inventory_id' => $lockedInventory->id,
                    'requested_quantity' => $quantity,
                    'update_result' => $updated
                ]);
                
                // Log ALL branch inventories for this product AFTER update
                $allAfter = BranchInventory::where('product_id', $lockedInventory->product_id)->get();
                \Log::info('=== AFTER UPDATE - All branch inventories for product ' . $lockedInventory->product_id . ' ===');
                foreach ($allAfter as $bi) {
                    \Log::info("Branch {$bi->branch_id} (ID {$bi->id}): {$bi->quantity_available} available, {$bi->quantity_on_hand} on hand");
                }
                
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Database update failed', [
                    'error' => $e->getMessage(),
                    'inventory_id' => $inventory->id
                ]);
                throw $e;
            }

            // Sync main product quantity (sum of all branches)
            $globalQuantity = $this->syncMainProductQuantity($inventory->product_id);

            // Refresh the model to get updated data
            $freshInventory = BranchInventory::find($inventory->id);
            $actualSavedQuantity = $freshInventory ? $freshInventory->quantity_available : $quantity;
            
            \Log::critical('🔍 CHECKING FRESH INVENTORY DATA', [
                'fresh_quantity_available' => $freshInventory ? $freshInventory->quantity_available : 'NULL',
                'fresh_quantity_on_hand' => $freshInventory ? $freshInventory->quantity_on_hand : 'NULL',
                'expected_quantity' => $quantity,
            ]);
            
            $responseData = [
                'success' => true,
                'message' => 'Quantity updated successfully',
                'inventory_id' => $inventory->id,
                'old_quantity' => $inventory->getOriginal('quantity_available'),
                'new_quantity' => $quantity,
                'saved_quantity' => $actualSavedQuantity,
                'new_global_quantity' => $globalQuantity
            ];
            
            \Log::info('🚀 SENDING RESPONSE TO FRONTEND', [
                'response_data' => $responseData,
                'branch_quantity_should_be' => $actualSavedQuantity,
                'global_quantity_should_be' => $responseData['new_global_quantity'],
            ]);
            
            return response()->json($responseData);

        } catch (\Exception $e) {
            \Log::error('Failed to update branch inventory quantity: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add all products to a branch with default quantity
     */
    public function addAllProductsToBranch(Request $request, $branchId)
    {
        $request->validate([
            'default_quantity' => 'required|integer|min:0|max:999999'
        ]);

        try {
            $branch = Branch::findOrFail($branchId);
            $defaultQuantity = $request->default_quantity;
            
            // Get all published products that are not yet in this branch
            $products = Product::where('status', 'published')
                ->whereDoesntHave('branchInventories', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->get();

            $addedCount = 0;

            foreach ($products as $product) {
                BranchInventory::create([
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'quantity_on_hand' => $defaultQuantity,
                    'quantity_available' => $defaultQuantity,
                    'quantity_reserved' => 0,
                    'minimum_stock' => 0,
                    'cost_price' => $product->cost_per_item ?? 0,
                    'selling_price' => $product->price ?? 0,
                    'visible_online' => true,
                    'visible_in_pos' => false,
                    'only_visible_in_pos' => false,
                ]);
                
                // Sync global quantity to main product
                $this->syncMainProductQuantity($product->id);
                
                $addedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully added {$addedCount} products to {$branch->name} with quantity {$defaultQuantity}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restock all products with 0 quantity to 1000
     */
    public function restockZeroQuantity(Request $request, $branchId)
    {
        try {
            $branch = Branch::findOrFail($branchId);
            
            // Update all products with 0 quantity to 1000
            $updated = DB::table('mbi_branch_inventory')
                ->where('branch_id', $branchId)
                ->where('quantity_available', '<=', 0)
                ->update([
                    'quantity_on_hand' => 1000,
                    'quantity_available' => 1000,
                    'updated_at' => now()
                ]);

            // Sync global quantities for affected products
            $productIds = BranchInventory::where('branch_id', $branchId)
                ->where('quantity_available', 1000)
                ->pluck('product_id');
            
            foreach ($productIds as $productId) {
                $this->syncMainProductQuantity($productId);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully restocked {$updated} products in {$branch->name} from 0 to 1000."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restock products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restock zero-quantity products to 1000 in the main branch
     */
    public function restockZeroInMainBranch(Request $request)
    {
        try {
            // Prefer explicit name match, fall back to flag if needed
            $mainBranch = Branch::where('name', 'Main Branch')->first();
            if (!$mainBranch) {
                $mainBranch = Branch::where('is_main_branch', true)->first();
            }
            if (!$mainBranch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main Branch not found'
                ], 404);
            }

            // Update all products with 0 quantity to 1000 in main branch
            $updated = DB::table('mbi_branch_inventory')
                ->where('branch_id', $mainBranch->id)
                ->where('quantity_available', '<=', 0)
                ->update([
                    'quantity_on_hand' => 1000,
                    'quantity_available' => 1000,
                    'updated_at' => now()
                ]);

            // Sync global quantities for affected products
            $productIds = BranchInventory::where('branch_id', $mainBranch->id)
                ->where('quantity_available', 1000)
                ->pluck('product_id');
            
            foreach ($productIds as $productId) {
                $this->syncMainProductQuantity($productId);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully restocked {$updated} products in {$mainBranch->name} (Main Branch) from 0 to 1000."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restock main branch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync main ecommerce product quantity with sum of all branch inventories
     */
    protected function syncMainProductQuantity($productId)
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return 0;
            }

            // Calculate total available quantity across all branches (Global Quantity)
            $globalQuantity = BranchInventory::where('product_id', $productId)
                ->sum('quantity_available');

            // Update main product quantity field with global quantity and ensure storehouse management is enabled
            $product->update([
                'quantity' => $globalQuantity,
                'with_storehouse_management' => true,
                'manage_stock' => true,
                'stock_status' => $globalQuantity > 0 ? 'in_stock' : 'out_of_stock'
            ]);

            \Log::info("Multi-Branch Inventory: Synced product {$productId} global quantity ({$globalQuantity}) to main product quantity field");

            return $globalQuantity;

        } catch (\Exception $e) {
            \Log::error('Failed to sync main product quantity: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Add a product to branch inventory
     */
    public function addProductToBranchInventory(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'branch_id' => 'required|exists:mbi_branches,id',
            'quantity_on_hand' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
        ]);

        // Check if product already exists in branch inventory
        $existingInventory = BranchInventory::where('branch_id', $request->branch_id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingInventory) {
            return response()->json(['error' => 'Product already exists in this branch inventory'], 409);
        }

        // Get product details
        $product = Product::find($request->product_id);
        $branch = Branch::find($request->branch_id);

        try {
            // Create branch inventory record
            $branchInventory = BranchInventory::create([
                'branch_id' => $request->branch_id,
                'product_id' => $request->product_id,
                'sku' => $product->sku,
                'quantity_on_hand' => $request->quantity_on_hand ?? 0,
                'quantity_available' => $request->quantity_on_hand ?? 0,
                'quantity_reserved' => 0,
                'minimum_stock' => $request->minimum_stock ?? 0,
                'maximum_stock' => null,
                'cost_price' => $request->cost_price ?? $product->cost_per_item ?? 0,
                'selling_price' => $request->selling_price ?? $product->price ?? 0,
                'visible_online' => true,
                'visible_in_pos' => false,
                'only_visible_in_pos' => false,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Product '{$product->name}' added to {$branch->name} inventory successfully!",
                    'inventory' => $branchInventory
                ]);
            }

            flash()->success("Product '{$product->name}' added to {$branch->name} inventory successfully!");
            return redirect()->back();
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to add product to inventory: ' . $e->getMessage()], 500);
            }
            
            flash()->error('Failed to add product to inventory: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
