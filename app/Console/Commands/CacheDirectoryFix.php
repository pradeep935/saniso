<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CacheDirectoryFix extends Command
{
    protected $signature = 'cache:fix-directories';
    protected $description = 'Create and fix Laravel cache directory permissions';

    public function handle()
    {
        $this->info('🔧 Fixing Laravel cache directories...');
        
        $storageDir = storage_path();
        $cacheDir = storage_path('framework/cache/data');
        
        // Create main directories
        $directories = [
            'framework/cache/data',
            'framework/sessions', 
            'framework/views',
            'logs',
            'app/public',
            'app/uploads'
        ];
        
        foreach ($directories as $dir) {
            $fullPath = storage_path($dir);
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0775, true);
                $this->info("✅ Created: $dir");
            }
        }
        
        // Create cache subdirectories
        $this->info('🗂️  Creating cache subdirectories...');
        for ($i = 0; $i <= 255; $i++) {
            $hex = sprintf('%02x', $i);
            $subDir = $cacheDir . '/' . $hex;
            
            if (!File::exists($subDir)) {
                File::makeDirectory($subDir, 0775, true);
            }
            
            // Create second level for common patterns
            for ($j = 0; $j <= 255; $j += 16) {
                $hex2 = sprintf('%02x', $j);
                $subSubDir = $subDir . '/' . $hex2;
                if (!File::exists($subSubDir)) {
                    File::makeDirectory($subSubDir, 0775, true);
                }
            }
        }
        
        $this->info('🧹 Clearing old cache...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        
        $this->info('✅ Cache directory fix completed!');
        $this->info('💡 This command runs automatically daily via cron.');
        
        return 0;
    }
}