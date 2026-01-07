-- Multi-Branch Inventory System - Sample Data Setup
-- Run this after plugin installation and migrations

-- Insert sample branches
INSERT INTO `mbi_branches` (`name`, `code`, `address`, `city`, `country`, `postal_code`, `phone`, `email`, `manager_name`, `status`, `is_main_branch`, `timezone`, `created_at`, `updated_at`) VALUES
('Zwaag Netherlands Hoofdvestiging', 'ZWAAG-NL', 'Industrieweg 123', 'Zwaag', 'Netherlands', '1689 AB', '+31 229 123456', 'zwaag@stalco.nl', 'Tom van der Berg', 'active', 1, 'Europe/Amsterdam', NOW(), NOW()),
('Utrecht Netherlands', 'UTRECHT-NL', 'Kanaalweg 45', 'Utrecht', 'Netherlands', '3511 BH', '+31 30 234567', 'utrecht@stalco.nl', 'Jan Peters', 'active', 0, 'Europe/Amsterdam', NOW(), NOW()),
('Rotterdam Netherlands', 'RDAM-NL', 'Havenstraat 78', 'Rotterdam', 'Netherlands', '3012 CD', '+31 10 345678', 'rotterdam@stalco.nl', 'Marie de Jong', 'active', 0, 'Europe/Amsterdam', NOW(), NOW());

-- Note: After installation, you should:

-- 1. Add sample products to your main Botble ecommerce system
-- 2. Run incoming goods registration for Stalco products
-- 3. Set up branch inventory for existing products

-- Sample incoming goods registration (adjust product_id values to match your actual products)
-- This would typically be done through the admin interface, but here's the structure:

/*
INSERT INTO `mbi_incoming_goods` (`branch_id`, `supplier_name`, `receiving_date`, `reference_number`, `status`, `received_by`, `total_items`, `created_at`, `updated_at`) VALUES
(1, 'Stalco Distribution', NOW(), 'INC20241209001', 'received', 1, 5, NOW(), NOW());

INSERT INTO `mbi_incoming_good_items` (`incoming_good_id`, `ean`, `sku`, `product_name`, `quantity_received`, `unit_cost`, `storage_location`, `is_new_product`, `created_at`, `updated_at`) VALUES
(1, '8713647001234', 'STALCO-HAMMER-01', 'Stalco Professional Hammer 500g', 20, 15.50, 'A1-R1-S3', 1, NOW(), NOW()),
(1, '8713647001241', 'STALCO-DRILL-01', 'Stalco Drill Bit Set 10pc', 15, 25.00, 'A1-R2-S1', 1, NOW(), NOW()),
(1, '8713647001258', 'STALCO-MEASURE-01', 'Stalco Measuring Tape 5m', 30, 8.75, 'A1-R1-S5', 1, NOW(), NOW()),
(1, '8713647001265', 'STALCO-LEVEL-01', 'Stalco Spirit Level 60cm', 12, 22.50, 'A1-R3-S2', 1, NOW(), NOW()),
(1, '8713647001272', 'STALCO-SCREWS-01', 'Stalco Wood Screws 4x40mm 100pc', 50, 5.25, 'A2-R1-S1', 1, NOW(), NOW());
*/

-- Sample temporary products for drinks (POS-only)
INSERT INTO `mbi_temporary_products` (`branch_id`, `sku`, `name`, `quantity`, `cost_price`, `selling_price`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'DRINK-COLA-05', 'Coca-Cola 0.5L', 24, 1.20, 1.50, 'active', 1, NOW(), NOW()),
(1, 'DRINK-WATER-05', 'Spa Water 0.5L', 20, 0.80, 1.00, 'active', 1, NOW(), NOW()),
(1, 'DRINK-COFFEE-CUP', 'Coffee To-Go', 100, 0.50, 2.50, 'active', 1, NOW(), NOW()),
(1, 'SNACK-SANDWICH', 'Ham & Cheese Sandwich', 10, 2.50, 4.00, 'active', 1, NOW(), NOW());

-- Instructions for Tom:
-- 
-- 1. ACTIVATE THE PLUGIN:
--    - Login to admin panel
--    - Go to Admin → Plugins → Multi-Branch Inventory System → Activate
--
-- 2. RUN THE MIGRATIONS:
--    - Execute: php artisan migrate
--    - This creates all the necessary database tables
--
-- 3. SETUP BRANCHES:
--    - The sample branches above will be created
--    - You can modify addresses/details as needed
--    - Set Zwaag as the main branch
--
-- 4. START USING INCOMING GOODS:
--    - Go to Multi-Branch Inventory → Incoming Goods → Create New
--    - Select "Zwaag Netherlands Hoofdvestiging"
--    - Add Stalco as supplier
--    - Start scanning/entering EAN codes for incoming products
--    - Products will immediately be available in POS
--
-- 5. CONFIGURE POS INTEGRATION:
--    - Use API endpoint: /api/multi-branch-inventory/pos/
--    - Branch ID for Zwaag will be 1
--    - Scan products, process sales, check stock
--
-- 6. PRIORITY WORKFLOW FOR STALCO PRODUCTS:
--    a) Receive goods via Incoming Goods form
--    b) Products automatically added to branch inventory
--    c) Set as "Visible in POS" (default)
--    d) Staff can immediately scan and sell
--    e) Add to main product catalog when ready for online sales
--
-- 7. TEMPORARY PRODUCTS FOR DRINKS:
--    - Sample drinks are already created
--    - Visible only in POS system
--    - Can be sold immediately at Zwaag location
--
-- NEXT STEPS:
-- - Add Kerakoll, Raimondi, and Progress Profiles as suppliers
-- - Create product catalog entries for main brands
-- - Set up stock transfers between branches
-- - Configure online product availability per branch
--
-- IMPORTANT: This system provides immediate POS functionality while allowing
-- gradual migration to full multi-branch e-commerce integration.