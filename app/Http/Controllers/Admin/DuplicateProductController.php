<?php

namespace App\Http\Controllers\Admin;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicateProductController extends BaseController
{
    public function index(Request $request)
    {
        $filterType = $request->get('filter_type', 'barcode');
        $duplicates = [];
        
        if ($filterType === 'barcode') {
            // Find duplicates by barcode
            $duplicates = DB::table('ec_products')
                ->select('barcode', DB::raw('GROUP_CONCAT(id) as product_ids'), DB::raw('COUNT(*) as count'), DB::raw('MIN(name) as name'))
                ->whereNotNull('barcode')
                ->where('barcode', '!=', '')
                ->groupBy('barcode')
                ->having('count', '>', 1)
                ->orderBy('count', 'desc')
                ->get();
                
            foreach ($duplicates as &$duplicate) {
                $duplicate->product_ids = explode(',', $duplicate->product_ids);
                $duplicate->type = 'barcode';
            }
        } else {
            // Find duplicates by title
            $duplicates = DB::table('ec_products')
                ->select('name', DB::raw('GROUP_CONCAT(id) as product_ids'), DB::raw('COUNT(*) as count'))
                ->groupBy('name')
                ->having('count', '>', 1)
                ->orderBy('count', 'desc')
                ->get();
                
            foreach ($duplicates as &$duplicate) {
                $duplicate->product_ids = explode(',', $duplicate->product_ids);
                $duplicate->barcode = DB::table('ec_products')->whereIn('id', $duplicate->product_ids)->first()->barcode ?? 'N/A';
                $duplicate->type = 'title';
            }
        }
        
        return view('admin.duplicate-products', compact('duplicates', 'filterType'));
    }
    
    public function remove(Request $request)
    {
        $productIds = $request->get('product_ids', []);
        $keepId = $request->get('keep_id');
        
        if (empty($productIds) || !$keepId) {
            return response()->json(['success' => false, 'message' => 'Invalid request']);
        }
        
        // Remove the keep_id from the list
        $idsToDelete = array_filter($productIds, function($id) use ($keepId) {
            return $id != $keepId;
        });
        
        if (empty($idsToDelete)) {
            return response()->json(['success' => false, 'message' => 'No products to delete']);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete related data first
            // Get variation IDs for these products
            $variationIds = DB::table('ec_product_variations')
                ->whereIn('product_id', $idsToDelete)
                ->pluck('id');
            
            // Delete variation items first
            if ($variationIds->isNotEmpty()) {
                DB::table('ec_product_variation_items')->whereIn('variation_id', $variationIds)->delete();
            }
            
            // Delete variations
            DB::table('ec_product_variations')->whereIn('product_id', $idsToDelete)->delete();
            
            // Delete other related data
            DB::table('ec_product_category_product')->whereIn('product_id', $idsToDelete)->delete();
            DB::table('ec_product_tag_product')->whereIn('product_id', $idsToDelete)->delete();
            DB::table('ec_product_collections_products')->whereIn('product_id', $idsToDelete)->delete();
            DB::table('ec_product_related_relations')->whereIn('from_product_id', $idsToDelete)->delete();
            DB::table('ec_product_related_relations')->whereIn('to_product_id', $idsToDelete)->delete();
            
            // Delete the products
            $deleted = DB::table('ec_products')->whereIn('id', $idsToDelete)->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true, 
                'message' => "Successfully deleted {$deleted} duplicate product(s)"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Error deleting products: ' . $e->getMessage()
            ]);
        }
    }
    
    public function bulkRemove(Request $request)
    {
        $filterType = $request->get('filter_type', 'barcode');
        $keepStrategy = $request->get('keep_strategy', 'first'); // first, last, or lowest_id
        
        try {
            DB::beginTransaction();
            
            $totalDeleted = 0;
            $duplicateGroups = [];
            
            if ($filterType === 'barcode') {
                $duplicateGroups = DB::table('ec_products')
                    ->select('barcode', DB::raw('GROUP_CONCAT(id ORDER BY id) as product_ids'))
                    ->whereNotNull('barcode')
                    ->where('barcode', '!=', '')
                    ->groupBy('barcode')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
            } else {
                $duplicateGroups = DB::table('ec_products')
                    ->select('name', DB::raw('GROUP_CONCAT(id ORDER BY id) as product_ids'))
                    ->groupBy('name')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
            }
            
            foreach ($duplicateGroups as $group) {
                $productIds = explode(',', $group->product_ids);
                
                // Determine which product to keep based on strategy
                if ($keepStrategy === 'first') {
                    $keepId = $productIds[0];
                } elseif ($keepStrategy === 'last') {
                    $keepId = end($productIds);
                } else { // lowest_id
                    $keepId = min($productIds);
                }
                
                // Get IDs to delete
                $idsToDelete = array_filter($productIds, function($id) use ($keepId) {
                    return $id != $keepId;
                });
                
                if (!empty($idsToDelete)) {
                    // Get variation IDs for these products
                    $variationIds = DB::table('ec_product_variations')
                        ->whereIn('product_id', $idsToDelete)
                        ->pluck('id');
                    
                    // Delete variation items first
                    if ($variationIds->isNotEmpty()) {
                        DB::table('ec_product_variation_items')->whereIn('variation_id', $variationIds)->delete();
                    }
                    
                    // Delete variations
                    DB::table('ec_product_variations')->whereIn('product_id', $idsToDelete)->delete();
                    
                    // Delete other related data
                    DB::table('ec_product_category_product')->whereIn('product_id', $idsToDelete)->delete();
                    DB::table('ec_product_tag_product')->whereIn('product_id', $idsToDelete)->delete();
                    DB::table('ec_product_collection_products')->whereIn('product_id', $idsToDelete)->delete();
                    DB::table('ec_product_related_relations')->whereIn('from_product_id', $idsToDelete)->delete();
                    DB::table('ec_product_related_relations')->whereIn('to_product_id', $idsToDelete)->delete();
                    
                    // Delete the products
                    $deleted = DB::table('ec_products')->whereIn('id', $idsToDelete)->delete();
                    $totalDeleted += $deleted;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$totalDeleted} duplicate products from " . count($duplicateGroups) . " groups"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk deletion: ' . $e->getMessage()
            ]);
        }
    }
}
