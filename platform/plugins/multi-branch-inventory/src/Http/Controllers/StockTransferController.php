<?php

namespace Botble\MultiBranchInventory\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\MultiBranchInventory\Models\StockTransfer;
use Botble\MultiBranchInventory\Models\StockTransferItem;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferController extends BaseController
{
    /**
     * Display stock transfers
     */
    public function index(Request $request)
    {
        $branches = Branch::where('status', 'active')->get();
        
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'requestedByUser'])
            ->orderBy('created_at', 'desc');

        if ($request->from_branch) {
            $query->where('from_branch_id', $request->from_branch);
        }

        if ($request->to_branch) {
            $query->where('to_branch_id', $request->to_branch);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Add items count for display
        $transfers = $query->withCount('items')->paginate(20);

        return view('plugins/multi-branch-inventory::stock-transfers.index-clean', 
            compact('transfers', 'branches'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->pageTitle('Create Stock Transfer');
        $branches = Branch::where('status', 'active')->get();
        
        return view('plugins/multi-branch-inventory::stock-transfers.create-simple', compact('branches'));
    }

    /**
     * Get products for transfer (API endpoint)
     */
    public function getProducts(Request $request)
    {
        $branchId = $request->from_branch_id;
        $search = $request->search;

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required'], 400);
        }

        $query = DB::table('ec_products as p')
            ->leftJoin('mbi_branch_inventory as bi', function($join) use ($branchId) {
                $join->on('p.id', '=', 'bi.product_id')
                     ->where('bi.branch_id', '=', $branchId);
            })
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.barcode as ean',
                DB::raw('COALESCE(bi.quantity_available, 0) as available')
            )
            ->where('p.status', 'published')
            ->where(DB::raw('COALESCE(bi.quantity_available, 0)'), '>', 0);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                  ->orWhere('p.sku', 'like', "%{$search}%")
                  ->orWhere('p.barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->limit(50)->get();

        return response()->json($products);
    }

    /**
     * Store new stock transfer
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required|exists:mbi_branches,id',
            'to_branch_id' => 'required|exists:mbi_branches,id|different:from_branch_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:ec_products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Check stock availability in source branch
            foreach ($request->items as $itemData) {
                $branchInventory = BranchInventory::where([
                    'branch_id' => $request->from_branch_id,
                    'product_id' => $itemData['product_id'],
                ])->first();

                if (!$branchInventory || $branchInventory->quantity_available < $itemData['quantity']) {
                    $product = Product::find($itemData['product_id']);
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
            }

            $stockTransfer = StockTransfer::create([
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'reference_number' => StockTransfer::generateReferenceNumber(),
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'requested_at' => now(),
                'notes' => $request->notes,
                'total_items' => count($request->items),
                'shipping_method' => $request->shipping_method,
            ]);

            foreach ($request->items as $itemData) {
                StockTransferItem::create([
                    'stock_transfer_id' => $stockTransfer->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_requested' => $itemData['quantity'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('stock-transfers.show', $stockTransfer)
                ->with('success', 'Stock transfer created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create stock transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Show stock transfer details
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'fromBranch', 'toBranch', 'items.product', 
            'requestedByUser', 'approvedByUser'
        ]);
        
        return view('plugins/multi-branch-inventory::stock-transfers.show-clean', compact('stockTransfer'));
    }

    /**
     * Show edit form
     */
    public function edit(StockTransfer $stockTransfer)
    {
        $this->pageTitle(trans('plugins/multi-branch-inventory::multi-branch-inventory.edit_transfer'));
        
        if ($stockTransfer->status !== 'pending') {
            return redirect()->route('stock-transfers.show', $stockTransfer->id)
                ->withErrors(['error' => 'Can only edit pending transfers.']);
        }
        
        $branches = Branch::where('status', 'active')->get();
        $products = Product::where('status', 'published')->orderBy('name')->get();
        
        return view('plugins/multi-branch-inventory::stock-transfers.edit', compact('stockTransfer', 'branches', 'products'));
    }

    /**
     * Update stock transfer
     */
    public function update(Request $request, StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Can only update pending transfers.']);
        }

        $request->validate([
            'from_branch_id' => 'required|exists:mbi_branches,id',
            'to_branch_id' => 'required|exists:mbi_branches,id|different:from_branch_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:ec_products,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request, $stockTransfer) {
                $stockTransfer->update([
                    'from_branch_id' => $request->from_branch_id,
                    'to_branch_id' => $request->to_branch_id,
                    'notes' => $request->notes,
                ]);

                // Delete existing items and recreate
                $stockTransfer->items()->delete();

                foreach ($request->items as $item) {
                    StockTransferItem::create([
                        'stock_transfer_id' => $stockTransfer->id,
                        'product_id' => $item['product_id'],
                        'quantity_requested' => $item['quantity_requested'],
                    ]);
                }
            });

            return redirect()->route('stock-transfers.show', $stockTransfer->id)
                ->with('success', 'Stock transfer updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete stock transfer
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Can only delete pending transfers.']);
        }

        $stockTransfer->delete();
        
        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transfer deleted successfully!');
    }

    /**
     * Duplicate stock transfer
     */
    public function duplicate(StockTransfer $stockTransfer)
    {
        try {
            DB::transaction(function () use ($stockTransfer) {
                $newTransfer = StockTransfer::create([
                    'reference_number' => StockTransfer::generateReferenceNumber(),
                    'from_branch_id' => $stockTransfer->from_branch_id,
                    'to_branch_id' => $stockTransfer->to_branch_id,
                    'status' => 'pending',
                    'notes' => $stockTransfer->notes . ' (Copy)',
                    'requested_by' => Auth::id(),
                ]);

                foreach ($stockTransfer->items as $item) {
                    StockTransferItem::create([
                        'stock_transfer_id' => $newTransfer->id,
                        'product_id' => $item->product_id,
                        'quantity_requested' => $item->quantity_requested,
                    ]);
                }

                return redirect()->route('stock-transfers.edit', $newTransfer->id)
                    ->with('success', 'Stock transfer duplicated successfully!');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to duplicate transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve stock transfer
     */
    public function approve(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Transfer is not in pending status.']);
        }

        DB::beginTransaction();
        try {
            // Approve the main transfer
            $stockTransfer->status = 'approved';
            $stockTransfer->approved_by = Auth::id();
            $stockTransfer->approved_at = now();
            $stockTransfer->save();

            // Auto-approve all items with requested quantities
            foreach ($stockTransfer->items as $item) {
                $item->quantity_approved = $item->quantity_requested;
                $item->approved_by = Auth::id();
                $item->approved_at = now();
                $item->save();
            }

            DB::commit();
            return back()->with('success', 'Stock transfer approved successfully! All items approved with requested quantities.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to approve stock transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Start picking process
     */
    public function startPicking(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'approved') {
            return back()->withErrors(['error' => 'Transfer must be approved before picking.']);
        }

        DB::beginTransaction();
        try {
            // Check and reserve stock for each item
            foreach ($stockTransfer->items as $item) {
                $branchInventory = BranchInventory::where([
                    'branch_id' => $stockTransfer->from_branch_id,
                    'product_id' => $item->product_id,
                ])->first();

                if (!$branchInventory || $branchInventory->quantity_available < $item->quantity_approved) {
                    throw new \Exception("Insufficient stock for product ID: {$item->product_id}");
                }
            }

            // Update transfer status
            $stockTransfer->status = 'picking';
            $stockTransfer->picked_by = Auth::id();
            $stockTransfer->picked_at = now();
            $stockTransfer->save();

            // Update item picking quantities and reserve stock
            foreach ($stockTransfer->items as $item) {
                $item->quantity_picked = $item->quantity_approved;
                $item->picked_by = Auth::id();
                $item->picked_at = now();
                $item->save();

                // Reserve the stock
                $branchInventory = BranchInventory::where([
                    'branch_id' => $stockTransfer->from_branch_id,
                    'product_id' => $item->product_id,
                ])->first();

                if ($branchInventory) {
                    $branchInventory->quantity_available -= $item->quantity_picked;
                    $branchInventory->quantity_reserved += $item->quantity_picked;
                    $branchInventory->save();
                }
            }

            DB::commit();
            return back()->with('success', 'Picking completed successfully! Stock has been reserved.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to start picking: ' . $e->getMessage()]);
        }
    }

    /**
     * Update picked quantities and ship
     */
    public function ship(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'picking') {
            return back()->withErrors(['error' => 'Transfer is not in picking status.']);
        }

        DB::beginTransaction();
        try {
            // Update transfer status
            $stockTransfer->status = 'shipped';
            $stockTransfer->shipped_by = Auth::id();
            $stockTransfer->shipped_at = now();
            $stockTransfer->save();

            // Ship all picked quantities
            foreach ($stockTransfer->items as $item) {
                $item->quantity_shipped = $item->quantity_picked;
                $item->shipped_by = Auth::id();
                $item->shipped_at = now();
                $item->save();

                // Remove from reserved and update available stock in source branch
                $branchInventory = BranchInventory::where([
                    'branch_id' => $stockTransfer->from_branch_id,
                    'product_id' => $item->product_id,
                ])->first();

                if ($branchInventory) {
                    $branchInventory->quantity_reserved -= $item->quantity_shipped;
                    $branchInventory->save();
                }
            }

            DB::commit();
            return back()->with('success', 'Stock transfer shipped successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to ship transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Receive stock transfer
     */
    public function receive(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'shipped') {
            return back()->withErrors(['error' => 'Transfer must be shipped before receiving.']);
        }

        DB::beginTransaction();
        try {
            // Update transfer status
            $stockTransfer->status = 'completed';
            $stockTransfer->received_by = Auth::id();
            $stockTransfer->received_at = now();
            $stockTransfer->save();

            // Receive all shipped quantities and add to destination branch
            foreach ($stockTransfer->items as $item) {
                $item->quantity_received = $item->quantity_shipped;
                $item->received_by = Auth::id();
                $item->received_at = now();
                $item->save();

                // Add inventory to destination branch
                $branchInventory = BranchInventory::where([
                    'branch_id' => $stockTransfer->to_branch_id,
                    'product_id' => $item->product_id,
                ])->first();

                if ($branchInventory) {
                    $branchInventory->quantity_available += $item->quantity_received;
                    $branchInventory->save();
                } else {
                    // Create new inventory record for destination branch
                    BranchInventory::create([
                        'branch_id' => $stockTransfer->to_branch_id,
                        'product_id' => $item->product_id,
                        'quantity_available' => $item->quantity_received,
                        'quantity_reserved' => 0,
                        'reorder_level' => 10,
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Stock transfer completed successfully! Inventory has been added to destination branch.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to complete transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel stock transfer
     */
    public function cancel(StockTransfer $stockTransfer)
    {
        if (!in_array($stockTransfer->status, ['pending', 'approved'])) {
            return back()->withErrors(['error' => 'Transfer cannot be cancelled in current status.']);
        }

        DB::beginTransaction();
        try {
            // Release any reserved stock
            if ($stockTransfer->status === 'picking') {
                foreach ($stockTransfer->items as $item) {
                    $branchInventory = BranchInventory::where([
                        'branch_id' => $stockTransfer->from_branch_id,
                        'product_id' => $item->product_id,
                    ])->first();

                    if ($branchInventory) {
                        $branchInventory->releaseReservedStock($item->quantity_requested);
                    }
                }
            }

            $stockTransfer->status = 'cancelled';
            $stockTransfer->save();

            DB::commit();

            return back()->with('success', 'Stock transfer cancelled successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to cancel transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Get available products in branch for transfer
     */
    public function getBranchProducts(Request $request)
    {
        $branchId = $request->branch_id;
        $search = $request->search;

        $query = BranchInventory::with('product')
            ->where('branch_id', $branchId)
            ->where('quantity_available', '>', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('ean', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->limit(20)->get()->map(function ($inventory) {
            return [
                'id' => $inventory->product_id,
                'name' => $inventory->product->name,
                'sku' => $inventory->sku,
                'available_quantity' => $inventory->quantity_available,
                'storage_location' => $inventory->storage_location,
            ];
        });

        return response()->json($products);
    }

    /**
     * Quick transfer for single product
     */
    public function quickTransfer(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'from_branch_id' => 'required|exists:mbi_branches,id',
            'to_branch_id' => 'required|exists:mbi_branches,id|different:from_branch_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Check available stock
            $fromInventory = BranchInventory::where([
                'branch_id' => $request->from_branch_id,
                'product_id' => $request->product_id,
            ])->first();

            if (!$fromInventory || $fromInventory->quantity_available < $request->quantity) {
                return back()->withErrors(['error' => 'Insufficient stock available for transfer.']);
            }

            // Create stock transfer
            $transfer = StockTransfer::create([
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'transfer_date' => now(),
                'status' => 'completed', // Quick transfers are auto-completed
                'notes' => $request->notes,
                'requested_by_user_id' => Auth::id(),
                'approved_by_user_id' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Add transfer item
            $product = Product::find($request->product_id);
            $transfer->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_cost' => $fromInventory->cost_price,
                'total_cost' => $fromInventory->cost_price * $request->quantity,
            ]);

            // Update inventory - subtract from source
            $fromInventory->updateStock($request->quantity, 'subtract', 'Quick Transfer Out');

            // Update inventory - add to destination
            $toInventory = BranchInventory::where([
                'branch_id' => $request->to_branch_id,
                'product_id' => $request->product_id,
            ])->first();

            if ($toInventory) {
                $toInventory->updateStock($request->quantity, 'add', 'Quick Transfer In');
            } else {
                // Create new inventory record for destination
                BranchInventory::create([
                    'branch_id' => $request->to_branch_id,
                    'product_id' => $request->product_id,
                    'sku' => $fromInventory->sku,
                    'quantity_on_hand' => $request->quantity,
                    'quantity_available' => $request->quantity,
                    'cost_price' => $fromInventory->cost_price,
                    'selling_price' => $fromInventory->selling_price,
                    'minimum_stock' => $fromInventory->minimum_stock,
                    'visible_online' => $fromInventory->visible_online,
                    'visible_in_pos' => $fromInventory->visible_in_pos,
                ]);
            }

            DB::commit();

            return back()->with('success', "Quick transfer completed successfully! Transfer ID: #{$transfer->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }
}