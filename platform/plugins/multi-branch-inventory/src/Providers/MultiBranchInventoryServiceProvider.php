<?php

namespace Botble\MultiBranchInventory\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\MultiBranchInventory\Models\IncomingGood;
use Botble\MultiBranchInventory\Models\StockTransfer;
use Botble\MultiBranchInventory\Models\TemporaryProduct;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Botble\Dashboard\Events\RenderingDashboardWidgets;
use Botble\Dashboard\Supports\DashboardWidgetInstance;
use Botble\MultiBranchInventory\Policies\BranchPolicy;
use Illuminate\Support\Facades\Gate;

class MultiBranchInventoryServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        //
    }

    protected function registerDashboardWidget(): void
    {
        try {
            $this->app['events']->listen(RenderingDashboardWidgets::class, function (): void {
                add_filter(DASHBOARD_FILTER_ADMIN_LIST, function ($widgets, $widgetSettings) {
                    $count = \Botble\MultiBranchInventory\Models\BranchInventory::where('needs_replenishment', true)->count();

                    return (new DashboardWidgetInstance())
                        ->setType('stats')
                        ->setPermission('branch-inventory.index')
                        ->setTitle('Replenishment Requests')
                        ->setKey('mbi_replenishment_requests')
                        ->setIcon('fas fa-truck-loading')
                        ->setColor('#ff6b6b')
                        ->setStatsTotal($count)
                        ->setRoute(route('branch-inventory.index'))
                        ->setColumn('col-12 col-md-6 col-lg-3')
                        ->init($widgets, $widgetSettings);
                }, 20, 2);
            });
        } catch (\Exception $e) {
            Log::warning('Multi-Branch Inventory: Could not register dashboard widget: ' . $e->getMessage());
        }
    }

    public function boot(): void
    {
        Gate::policy(Branch::class, BranchPolicy::class);

        try {
            $this
                ->setNamespace('plugins/multi-branch-inventory')
                ->loadAndPublishConfigurations(['permissions'])
                ->loadMigrations()
                ->loadAndPublishTranslations()
                ->loadRoutes(['web']);

            // Register views manually since setNamespace doesn't handle views automatically
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'plugins/multi-branch-inventory');

            // Load helpers if file exists
            $helpersPath = __DIR__ . '/../../helpers/helpers.php';
            if (file_exists($helpersPath)) {
                require_once $helpersPath;
            }

            // Register event listeners
            $this->registerEventListeners();

            // Register quantity sync listeners
            $this->registerQuantitySyncListeners();

            // Extend existing models
            $this->extendModels();

            // Register console commands
            $this->registerCommands();

            // Register scheduled tasks
            $this->registerScheduledTasks();
            
            // Register dashboard menu
            $this->registerDashboardMenu();

            // Register dashboard widget for replenishment requests
            $this->registerDashboardWidget();
            
            // Add branch inventory integration to ecommerce
            $this->addEcommerceIntegration();
            
            // Auto-enable storehouse management in admin
            $this->addStorehouseManagementAutoEnable();
            
            // Register assets
            $this->registerAssets();
            
        } catch (\Exception $e) {
            Log::error('Multi-Branch Inventory Plugin Boot Error: ' . $e->getMessage());
        }
    }

    protected function registerEventListeners(): void
    {
        try {
            // Listen for product stock updates from POS
            Event::listen('pos.product.sold', function ($event) {
                $this->updateBranchInventoryFromSale($event);
            });

            // Check if ecommerce events exist before registering
            if (class_exists(\Botble\Ecommerce\Events\OrderCreated::class)) {
                Event::listen(\Botble\Ecommerce\Events\OrderCreated::class, 
                    \Botble\MultiBranchInventory\Listeners\HandleEcommerceOrderListener::class
                );
            }

            if (class_exists(\Botble\Ecommerce\Events\ProductQuantityUpdatedEvent::class)) {
                Event::listen(\Botble\Ecommerce\Events\ProductQuantityUpdatedEvent::class,
                    \Botble\MultiBranchInventory\Listeners\SyncProductQuantityListener::class
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail plugin activation
            Log::warning('Multi-Branch Inventory: Could not register some event listeners: ' . $e->getMessage());
        }
    }

    protected function registerQuantitySyncListeners(): void
    {
        try {
            // Note: Event listeners are now handled in BranchInventory model's boot() method
            // to avoid circular dependency issues. This method is kept for future custom events.
            if (config('app.debug')) {
                Log::info('Multi-Branch Inventory: Quantity sync listeners registered via model boot method');
            }

        } catch (\Exception $e) {
            Log::warning('Multi-Branch Inventory: Could not register quantity sync listeners: ' . $e->getMessage());
        }
    }

    protected function syncProductQuantity($productId): void
    {
        try {
            if (!class_exists(\Botble\Ecommerce\Models\Product::class)) {
                return;
            }

            $product = \Botble\Ecommerce\Models\Product::find($productId);
            if (!$product) {
                return;
            }

            // Calculate total quantity across all branches
            $totalQuantity = BranchInventory::where('product_id', $productId)
                ->sum('quantity_available');

            // Update the main product quantity
            $product->update(['quantity' => $totalQuantity]);

            Log::info("Multi-Branch Inventory: Synced product {$productId} quantity to {$totalQuantity}");

        } catch (\Exception $e) {
            Log::error('Multi-Branch Inventory: Failed to sync product quantity: ' . $e->getMessage());
        }
    }

    protected function extendModels(): void
    {
        try {
            // Check if ecommerce models exist before extending
            if (class_exists(\Botble\Ecommerce\Models\Product::class)) {
                // Add both singular and plural relationship names for compatibility
                \Botble\Ecommerce\Models\Product::resolveRelationUsing('branchInventory', function ($productModel) {
                    return $productModel->hasMany(BranchInventory::class);
                });
                
                \Botble\Ecommerce\Models\Product::resolveRelationUsing('branchInventories', function ($productModel) {
                    return $productModel->hasMany(BranchInventory::class, 'product_id');
                });
            }

            if (class_exists(\Botble\Ecommerce\Models\Customer::class)) {
                \Botble\Ecommerce\Models\Customer::resolveRelationUsing('preferredBranch', function ($customerModel) {
                    return $customerModel->belongsTo(Branch::class, 'preferred_branch_id');
                });
            }
        } catch (\Exception $e) {
            // Log error but don't fail plugin activation  
            Log::warning('Multi-Branch Inventory: Could not extend some models: ' . $e->getMessage());
        }
    }

    protected function updateBranchInventoryFromSale($event): void
    {
        if (!isset($event->branchId, $event->productId, $event->quantity)) {
            return;
        }

        $branchInventory = BranchInventory::where([
            'branch_id' => $event->branchId,
            'product_id' => $event->productId,
        ])->first();

        if ($branchInventory) {
            $branchInventory->updateStock($event->quantity, 'subtract', 'POS Sale');
        }
    }

    protected function handleEcommerceOrder($event): void
    {
        // Handle online orders with branch selection
        $order = $event->order;
        
        if ($order->pickup_branch_id) {
            foreach ($order->products as $product) {
                $branchInventory = BranchInventory::where([
                    'branch_id' => $order->pickup_branch_id,
                    'product_id' => $product->id,
                ])->first();

                if ($branchInventory) {
                    $branchInventory->reserveStock($product->pivot->qty);
                }
            }
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Botble\MultiBranchInventory\Console\Commands\ExpireReservationsCommand::class,
                \Botble\MultiBranchInventory\Console\Commands\SyncInventoryCommand::class,
                \Botble\MultiBranchInventory\Console\Commands\SeedSampleDataCommand::class,
                \Botble\MultiBranchInventory\Console\Commands\SyncProductQuantitiesCommand::class,
                \Botble\MultiBranchInventory\Console\EnableStorehouseManagementCommand::class,
                \Botble\MultiBranchInventory\Console\SyncExistingProductsCommand::class,
            ]);
        }
    }

    protected function registerScheduledTasks(): void
    {
        if ($this->app->runningInConsole()) {
            try {
                $this->app->booted(function () {
                    $schedule = $this->app->make(Schedule::class);
                    
                    $schedule->command('multi-branch-inventory:expire-reservations')
                        ->hourly()
                        ->description('Expire old pickup reservations');

                    $schedule->command('multi-branch-inventory:sync')
                        ->everyTenMinutes()
                        ->description('Sync inventory between branches and main system');
                });
            } catch (\Exception $e) {
                Log::warning('Multi-Branch Inventory: Could not register scheduled tasks: ' . $e->getMessage());
            }
        }
    }

    protected function registerDashboardMenu(): void
    {
        DashboardMenu::beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-multi-branch-inventory',
                    'priority' => 8,
                    'icon' => 'ti ti-building-warehouse',
                    'name' => 'Multi-Branch Inventory',
                    'permissions' => ['plugins.multi-branch-inventory'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-multi-branch-inventory-branches',
                    'parent_id' => 'cms-plugins-multi-branch-inventory',
                    'priority' => 0,
                    'name' => 'Branches',
                    'icon' => 'ti ti-building',
                    'url' => fn () => route('branches.index'),
                    'permissions' => ['branches.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-multi-branch-inventory-inventory',
                    'parent_id' => 'cms-plugins-multi-branch-inventory',
                    'priority' => 1,
                    'name' => 'Inventory Management',
                    'icon' => 'ti ti-package',
                    'url' => fn () => route('branch-inventory.index'),
                    'permissions' => ['branch-inventory.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-multi-branch-inventory-transfers',
                    'parent_id' => 'cms-plugins-multi-branch-inventory',
                    'priority' => 2,
                    'name' => 'Stock Transfers',
                    'icon' => 'ti ti-arrows-exchange',
                    'url' => fn () => route('stock-transfers.index'),
                    'permissions' => ['stock-transfers.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-multi-branch-inventory-incoming',
                    'parent_id' => 'cms-plugins-multi-branch-inventory',
                    'priority' => 3,
                    'name' => 'Incoming Goods',
                    'icon' => 'ti ti-truck-delivery',
                    'url' => fn () => route('incoming-goods.index'),
                    'permissions' => ['incoming-goods.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-multi-branch-inventory-reports',
                    'parent_id' => 'cms-plugins-multi-branch-inventory',
                    'priority' => 5,
                    'name' => 'Reports & Analytics',
                    'icon' => 'ti ti-chart-bar',
                    'url' => fn () => route('inventory-reports.index'),
                    'permissions' => ['inventory-reports.index'],
                ]);
        });
    }
    
    /**
     * Add ecommerce integration for branch inventory management
     */
    protected function addEcommerceIntegration(): void
    {
        try {
            if (!class_exists(\Botble\Ecommerce\Models\Product::class)) {
                return;
            }
            
            // Add branch inventory information to product management
            add_action('ecommerce.product.after_general_info', function ($product) {
                if (!$product || !$product->id) {
                    return;
                }
                
                $branches = Branch::where('is_active', true)->get();
                $branchInventories = BranchInventory::where('product_id', $product->id)
                    ->with('branch')
                    ->get();
                
                echo view('plugins/multi-branch-inventory::partials.product-branch-inventory', compact('product', 'branches', 'branchInventories'))->render();
            });
            


            // Add branch selection to product creation/editing
            add_filter('ecommerce.product.tabs', function ($tabs, $product) {
                $tabs['branch-inventory'] = [
                    'name' => trans('plugins/multi-branch-inventory::multi-branch-inventory.inventory_management'),
                    'icon' => 'ti ti-building-warehouse',
                    'priority' => 15,
                ];
                
                return $tabs;
            }, 10, 2);

            // Auto-create branch inventory for all new products
            if (class_exists(\Botble\Ecommerce\Models\Product::class)) {
                // When a product is created, automatically add it to all active branches
                Event::listen('eloquent.created: Botble\Ecommerce\Models\Product', function ($product) {
                    // Always enable storehouse management for new products
                    $product->update(['with_storehouse_management' => true]);
                    
                    // Auto-assign to main branch with product's actual quantity (or 100 if 0)
                    $defaultQuantity = $product->quantity > 0 ? $product->quantity : 100;
                    $this->createBranchInventoryForNewProduct($product, $defaultQuantity);
                });
                
                // Enable storehouse management for existing products when they are updated
                Event::listen('eloquent.saved: Botble\Ecommerce\Models\Product', function ($product) {
                    if (!$product->with_storehouse_management) {
                        $product->update(['with_storehouse_management' => true]);
                    }
                });
            }
            
            // Add branch inventory content to product tabs
            add_action('ecommerce.product.branch-inventory.content', function ($product) {
                if (!$product || !$product->id) {
                    echo '<div class="alert alert-info">Save the product first to manage branch inventory.</div>';
                    return;
                }
                
                $branches = Branch::where('is_active', true)->get();
                $branchInventories = BranchInventory::where('product_id', $product->id)
                    ->with('branch')
                    ->get()
                    ->keyBy('branch_id');
                
                echo view('plugins/multi-branch-inventory::partials.product-branch-inventory-tab', compact('product', 'branches', 'branchInventories'))->render();
            });
            
        } catch (\Exception $e) {
            Log::warning('Multi-Branch Inventory: Could not add ecommerce integration: ' . $e->getMessage());
        }
    }

    /**
     * Add branch selection to product form
     */
    protected function addBranchSelectionToProductForm($form): void
    {
        try {
            $branches = Branch::where('status', 'active')->get();
            
            if ($branches->isEmpty()) {
                return;
            }

            // Get selected branches and quantities if editing existing product
            $selectedBranches = [];
            $branchQuantities = [];
            $branchMinQuantities = [];
            $product = $form->getModel();
            
            if ($product && $product->id) {
                $branchInventories = BranchInventory::where('product_id', $product->id)->get();
                
                foreach ($branchInventories as $inventory) {
                    $selectedBranches[] = $inventory->branch_id;
                    $branchQuantities[$inventory->branch_id] = $inventory->quantity;
                    $branchMinQuantities[$inventory->branch_id] = $inventory->min_quantity ?? 0;
                }
            }

            // Add branch selection fields to the form
            $form->add('branch_inventory_section', 'html', [
                'html' => view('plugins/multi-branch-inventory::partials.product-branch-selection', 
                    compact('branches', 'selectedBranches', 'branchQuantities', 'branchMinQuantities', 'product')
                )->render(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error adding branch selection to product form: ' . $e->getMessage());
        }
    }
    

    
    /**
     * Render branch selection HTML for product form
     */
    /**
     * Automatically create branch inventory for new products in all active branches
     */
    public function createBranchInventoryForNewProduct($product, $defaultQuantity = 100): void
    {
        try {
            // Get all active branches, prioritize main branch
            $branches = Branch::where('status', 'active')
                ->orderByDesc('is_main_branch')
                ->get();
            
            if ($branches->isEmpty()) {
                return;
            }
            
            // Create branch inventory records - ONLY for main branch to ensure Global = Main Branch quantity
            $mainBranch = $branches->where('is_main_branch', true)->first() ?: $branches->first();
            
            BranchInventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'branch_id' => $mainBranch->id,
                ],
                [
                    'quantity_on_hand' => $defaultQuantity,
                    'quantity_available' => $defaultQuantity,
                    'quantity_reserved' => 0,
                    'minimum_stock' => 0,
                    'cost_price' => $product->cost_per_item ?? 0,
                    'selling_price' => $product->price ?? 0,
                    'visible_online' => true,
                    'visible_in_pos' => true,
                    'only_visible_in_pos' => false,
                ]
            );
            
            // Update product quantity to match main branch quantity (ensure sync)
            if ($product->quantity != $defaultQuantity) {
                $product->update(['quantity' => $defaultQuantity]);
            }
            
            Log::info('Created branch inventory for product: ' . $product->name . ' in ' . $branches->count() . ' branches');
            
        } catch (\Exception $e) {
            Log::error('Error creating branch inventory for new product: ' . $e->getMessage());
        }
    }

    /**
     * Sync existing products that don't have branch inventory to main branch
     */
    public function syncExistingProductsToMainBranch(): void
    {
        try {
            // Get main branch
            $mainBranch = Branch::where('is_main_branch', true)->first();
            if (!$mainBranch) {
                $mainBranch = Branch::where('status', 'active')->first();
            }
            
            if (!$mainBranch) {
                Log::warning('No active branch found to sync existing products');
                return;
            }

            // Get products that don't have any branch inventory
            $productsWithoutBranch = \Botble\Ecommerce\Models\Product::where('status', 'published')
                ->whereDoesntHave('branchInventories')
                ->get();

            $syncedCount = 0;
            foreach ($productsWithoutBranch as $product) {
                // Create branch inventory with the product's current quantity
                BranchInventory::create([
                    'product_id' => $product->id,
                    'branch_id' => $mainBranch->id,
                    'sku' => $product->sku,
                    'quantity_on_hand' => $product->quantity ?: 0,
                    'quantity_available' => $product->quantity ?: 0,
                    'quantity_reserved' => 0,
                    'minimum_stock' => 0,
                    'cost_price' => $product->cost_per_item ?? 0,
                    'selling_price' => $product->price ?? 0,
                    'visible_online' => true,
                    'visible_in_pos' => true,
                    'only_visible_in_pos' => false,
                ]);
                
                $syncedCount++;
            }

            if ($syncedCount > 0) {
                Log::info("Multi-Branch Inventory: Synced {$syncedCount} existing products to main branch ({$mainBranch->name})");
            }

        } catch (\Exception $e) {
            Log::error('Error syncing existing products to main branch: ' . $e->getMessage());
        }
    }
    
    /**
     * Sync product quantity from all branch inventories when branch inventory is updated
     */
    public function syncProductQuantityFromBranches($branchInventory): void
    {
        try {
            $productId = $branchInventory->product_id;
            
            // Calculate total available quantity across all branches for this product
            $totalQuantity = BranchInventory::where('product_id', $productId)
                ->sum('quantity_available');
            
            // Update the main product quantity and ensure storehouse management is enabled
            \Botble\Ecommerce\Models\Product::where('id', $productId)
                ->update([
                    'quantity' => $totalQuantity,
                    'with_storehouse_management' => true
                ]);
            
            Log::info('Synced product quantity for product ID: ' . $productId . ' - Total: ' . $totalQuantity);
            
        } catch (\Exception $e) {
            Log::error('Error syncing product quantity from branches: ' . $e->getMessage());
        }
    }
    
    protected function addStorehouseManagementAutoEnable(): void
    {
        try {
            // Add JavaScript to auto-enable storehouse management checkbox and set quantity to 100
            add_action('admin_footer', function () {
                if (request()->is('admin/ecommerce/products/*') || request()->is('admin/ecommerce/products')) {
                    echo '<script>
                        (function() {
                            function enableStorehouseManagement() {
                                // Try multiple selectors to find the checkbox
                                const storehouseCheckbox = document.querySelector("input[name=\\"with_storehouse_management\\"]") ||
                                                          document.querySelector("#with_storehouse_management") ||
                                                          document.querySelector("input[type=\\"checkbox\\"][value=\\"1\\"]");
                                
                                if (storehouseCheckbox && !storehouseCheckbox.checked) {
                                    storehouseCheckbox.checked = true;
                                    // Trigger both change and click events for compatibility
                                    storehouseCheckbox.dispatchEvent(new Event("change", { bubbles: true }));
                                    storehouseCheckbox.dispatchEvent(new Event("click", { bubbles: true }));
                                }
                                
                                // Set default quantity to 100 for new products (if empty)
                                const quantityInput = document.querySelector("input[name=\\"quantity\\"]");
                                if (quantityInput && (!quantityInput.value || quantityInput.value === "0")) {
                                    quantityInput.value = "100";
                                }
                            }
                            
                            // Try immediately on DOMContentLoaded
                            if (document.readyState === "loading") {
                                document.addEventListener("DOMContentLoaded", enableStorehouseManagement);
                            } else {
                                enableStorehouseManagement();
                            }
                            
                            // Retry after a short delay to handle dynamic content
                            setTimeout(enableStorehouseManagement, 500);
                            setTimeout(enableStorehouseManagement, 1000);
                        })();
                    </script>';
                }
            });
            
        } catch (\Exception $e) {
            Log::warning('Multi-Branch Inventory: Could not add storehouse management auto-enable: ' . $e->getMessage());
        }
    }
    
    protected function registerAssets(): void
    {
        try {
            // Publish and register CSS and JS assets for the plugin
            $this->publishes([
                __DIR__ . '/../../resources/assets' => public_path('vendor/plugins/multi-branch-inventory'),
            ], 'multi-branch-inventory-assets');
            
            // Auto-publish assets in development
            if (app()->environment('local', 'development')) {
                $this->callAfterResolving('files', function ($files) {
                    $assetsPath = __DIR__ . '/../../resources/assets';
                    $publicPath = public_path('vendor/plugins/multi-branch-inventory');
                    
                    if (!$files->exists($publicPath)) {
                        $files->makeDirectory($publicPath, 0755, true);
                    }
                    
                    // Copy CSS files
                    if ($files->exists($assetsPath . '/css')) {
                        $files->copyDirectory($assetsPath . '/css', $publicPath . '/css');
                    }
                    
                    // Copy JS files
                    if ($files->exists($assetsPath . '/js')) {
                        $files->copyDirectory($assetsPath . '/js', $publicPath . '/js');
                    }
                });
            }
            
            if (config('app.debug')) {
                Log::info('Multi-Branch Inventory: Assets registered successfully');
            }
            
        } catch (\Exception $e) {
            Log::warning('Multi-Branch Inventory: Could not register assets: ' . $e->getMessage());
        }
    }
}