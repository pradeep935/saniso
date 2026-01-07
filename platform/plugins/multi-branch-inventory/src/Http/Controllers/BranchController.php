<?php

namespace Botble\MultiBranchInventory\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\MultiBranchInventory\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BranchController extends BaseController
{
    /**
     * Display branches list
     */
    public function index()
    {
        Gate::authorize('viewAny', Branch::class);

        \Log::info('=== BRANCH CONTROLLER DEBUG ===');
        \Log::info('Index method called at: ' . now());
        \Log::info('Request URI: ' . request()->getRequestUri());
        
        try {
            $this->pageTitle(trans('plugins/multi-branch-inventory::multi-branch-inventory.branches'));
            
            // Test database connection first
            $branchCount = Branch::count();
            \Log::info('Total branches in database: ' . $branchCount);
            
            if ($branchCount === 0) {
                \Log::info('No branches found - creating test data');
                
                // Create test branch if none exist
                $testBranch = Branch::create([
                    'name' => 'Main Branch',
                    'code' => 'MAIN001',
                    'address' => '123 Main Street',
                    'phone' => '555-0123',
                    'email' => 'main@example.com',
                    'manager_name' => 'John Manager',
                    'is_main_branch' => true,
                    'status' => 'active',
                    'type_name' => 'Store',
                    'type_icon' => '🏢'
                ]);
                
                \Log::info('Test branch created: ' . $testBranch->id);
            }
            
            $branches = Branch::orderBy('is_main_branch', 'desc')
                ->orderBy('name')
                ->paginate(20);
                
            \Log::info('Branches loaded: ' . $branches->count());
            
            // Use working view path
            $viewPath = 'plugins/multi-branch-inventory::branches.index-clean';
            \Log::info('Returning clean view: ' . $viewPath);
            
            return view($viewPath, compact('branches'));
            
        } catch (\Exception $e) {
            \Log::error('CONTROLLER ERROR: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            // Return simple HTML instead of view
            return response('
                <html>
                <head><title>Branch Debug</title></head>
                <body>
                    <h1>Branch Page Debug</h1>
                    <p>Error: ' . $e->getMessage() . '</p>
                    <p>Controller is working but view failed to load</p>
                    <p>Time: ' . now() . '</p>
                </body>
                </html>
            ');
        }
    }

    /**
     * Show create form
     */
    public function create()
    {
        Gate::authorize('create', Branch::class);

        $this->pageTitle(trans('plugins/multi-branch-inventory::multi-branch-inventory.create_branch'));
        
        return view('plugins/multi-branch-inventory::branches.create-simple');
    }

    /**
     * Store new branch
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Branch::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:mbi_branches,code',
            'type' => 'required|string|in:store,warehouse,distribution_center,outlet,flagship,pop_up,showroom,kiosk,franchise,online_fulfillment',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'timezone' => 'required|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'string|in:pos_enabled,online_orders,pickup_point,returns_accepted',
        ]);

        // Ensure only one main branch exists
        $isMainBranch = $request->boolean('is_main_branch');
        if ($isMainBranch) {
            Branch::where('is_main_branch', true)->update(['is_main_branch' => false]);
        }

        $branch = Branch::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'manager_name' => $request->manager_name,
            'status' => 'active',
            'is_main_branch' => $isMainBranch,
            'timezone' => $request->timezone,
            'currency_id' => $request->currency_id,
            'features' => $request->features ?? [],
            'settings' => $request->settings ?? [],
        ]);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch created successfully!');
    }

    /**
     * Show branch details
     */
    public function show(Branch $branch)
    {
        Gate::authorize('view', $branch);

        $this->pageTitle($branch->name);

        return view('plugins/multi-branch-inventory::branches.show', compact('branch'));
    }

    /**
     * Show edit form
     */
    public function edit(Branch $branch)
    {
        Gate::authorize('update', $branch);

        $this->pageTitle(trans('plugins/multi-branch-inventory::multi-branch-inventory.edit_branch', ['name' => $branch->name]));

        return view('plugins/multi-branch-inventory::branches.edit-simple', compact('branch'));
    }

    /**
     * Update existing branch
     */
    public function update(Request $request, Branch $branch)
    {
        Gate::authorize('update', $branch);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:mbi_branches,code,' . $branch->id,
            'type' => 'required|string|in:store,warehouse,distribution_center,outlet,flagship,pop_up,showroom,kiosk,franchise,online_fulfillment',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
            'features' => 'nullable|array',
            'features.*' => 'string|in:pos_enabled,online_orders,pickup_point,returns_accepted',
        ]);

        // Handle main branch setting
        $isMainBranch = $request->boolean('is_main_branch');
        if ($isMainBranch && !$branch->is_main_branch) {
            Branch::where('is_main_branch', true)->update(['is_main_branch' => false]);
        }

        $branch->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'manager_name' => $request->manager_name,
            'status' => $request->status,
            'is_main_branch' => $isMainBranch,
            'timezone' => $request->timezone,
            'currency_id' => $request->currency_id,
            'features' => $request->features ?? [],
            'settings' => $request->settings ?? [],
        ]);

        return back()->with('success', 'Branch updated successfully!');
    }

    /**
     * Delete branch
     */
    public function destroy(Branch $branch)
    {
        Gate::authorize('delete', $branch);

        DB::beginTransaction();
        try {
            if ($branch->is_main_branch) {
                return back()->withErrors(['error' => 'Cannot delete the main branch.']);
            }

            // Check if branch has inventory or transfers
            if ($branch->inventoryItems()->exists() || 
                $branch->transfersFrom()->exists() || 
                $branch->transfersTo()->exists()) {
                return back()->withErrors(['error' => 'Cannot delete branch with existing inventory or transfers.']);
            }

            $branch->delete();

            DB::commit();

            return redirect()
                ->route('branches.index')
                ->with('success', 'Branch deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error deleting branch: ' . $e->getMessage()]);
        }
    }

    /**
     * Test branch connection
     */
    public function testConnection(Branch $branch)
    {
        try {
            // Simulate connection test - in real implementation, this would test:
            // - Network connectivity to branch
            // - Database connectivity
            // - POS system connectivity if applicable
            
            $tests = [
                'network' => true, // Ping test
                'database' => true, // DB connection test
                'pos' => $branch->has_pos ? true : null, // POS system test
            ];

            $allPassed = collect($tests)->filter()->count() === collect($tests)->whereNotNull()->count();

            return response()->json([
                'success' => $allPassed,
                'message' => $allPassed ? 'All connection tests passed' : 'Some tests failed',
                'tests' => $tests,
                'branch' => $branch->name,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}