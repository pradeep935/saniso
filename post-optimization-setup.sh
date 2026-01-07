#!/bin/bash
# Performance Optimization Quick Start Guide
# Run after implementing optimizations

echo "🚀 Post-Optimization Checklist"
echo "=============================="
echo ""

# 1. Clear caches
echo "1️⃣  Clearing Laravel caches..."
php artisan cache:clear
php artisan optimize:clear
echo "   ✓ Caches cleared"
echo ""

# 2. Verify database indexes
echo "2️⃣  Verifying database indexes..."
php artisan tinker << 'EOF'
$indexes = \DB::select("SHOW INDEX FROM audit_histories");
$unique = array_unique(array_column($indexes, 'Key_name'));
echo "   Audit indexes: " . implode(", ", $unique) . "\n";
exit;
EOF
echo ""

# 3. Test dashboard cache
echo "3️⃣  Testing dashboard cache service..."
php artisan tinker << 'EOF'
$count = \Botble\Ecommerce\Services\DashboardCacheService::getOrderCount();
echo "   Order count (cached): $count\n";
$count = \Botble\Ecommerce\Services\DashboardCacheService::getProductCount();
echo "   Product count (cached): $count\n";
exit;
EOF
echo ""

# 4. Verify PHP-FPM
echo "4️⃣  Checking PHP-FPM worker pool..."
WORKERS=$(ps aux | grep "php-fpm.*pool" | grep -v grep | wc -l)
echo "   Active PHP-FPM workers: $WORKERS"
echo "   (Healthy range: 5-30)"
echo ""

# 5. Schedule cleanup task
echo "5️⃣  To enable daily audit cleanup, add to crontab:"
echo ""
echo "   crontab -e"
echo "   # Add this line:"
echo "   0 2 * * * cd /home/i23/public_html && php artisan audit-histories:prune --days=90 --force >> /var/log/audit-cleanup.log 2>&1"
echo ""
echo "   Then save (Ctrl+X, Y, Enter)"
echo ""

echo "✅ Post-optimization setup complete!"
echo ""
echo "📊 Performance targets:"
echo "   • Dashboard load time: < 2 seconds"
echo "   • Database queries per load: ≤ 2 (cached)"
echo "   • MySQL connection pool: < 10 connections"
echo ""
echo "🔍 Monitor performance:"
echo "   • Visit /admin (watch Network tab in F12)"
echo "   • Check MySQL: mysql -u root -p -e 'SHOW PROCESSLIST;'"
echo "   • View slow queries: tail -f /var/log/mysql/slow.log"
echo ""
