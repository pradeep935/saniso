<?php

namespace Botble\MultiBranchInventory\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\MultiBranchInventory\Models\StockTransfer;
use Botble\MultiBranchInventory\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class InventoryReportsController extends BaseController
{
    public function index(): View
    {
        $this->pageTitle('Inventory Reports');

        $branches = Branch::where('status', 'active')->get();
        
        return view('plugins/multi-branch-inventory::incoming-goods.reports.dashboard', compact('branches'));
    }

    public function lowStock(Request $request)
    {
        $branchId = $request->get('branch_id');
        
        $query = BranchInventory::with(['branch', 'product'])
            ->whereRaw('quantity_available <= minimum_stock');
            
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $lowStockItems = $query->get();
        
        return response()->json($lowStockItems);
    }

    public function stockLevels(Request $request)
    {
        $branchId = $request->get('branch_id');
        
        $query = BranchInventory::with(['branch', 'product']);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $stockLevels = $query->get();
        
        return response()->json($stockLevels);
    }

    public function transferHistory(Request $request)
    {
        $branchId = $request->get('branch_id');
        
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'items.product']);
        
        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            });
        }
        
        $transfers = $query->latest()->paginate(20);
        
        return response()->json($transfers);
    }
}