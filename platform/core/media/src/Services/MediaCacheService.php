<?php

namespace Botble\Media\Services;

use Illuminate\Support\Facades\Cache;

class MediaCacheService
{
    const CACHE_PREFIX = 'media_list_';
    const CACHE_TTL = 1800; // 30 minutes

    /**
     * Generate cache key for media list query
     */
    public static function getCacheKey(array $params): string
    {
        return self::CACHE_PREFIX . md5(json_encode([
            'folder_id' => $params['folder_id'] ?? 0,
            'page' => $params['paged'] ?? 1,
            'per_page' => $params['posts_per_page'] ?? 30,
            'filter' => $params['filter'] ?? 'everything',
            'search' => $params['search'] ?? '',
            'sort_by' => $params['sort_by'] ?? '',
            'view_in' => $params['view_in'] ?? 'all_media',
        ]));
    }

    /**
     * Clear media list cache
     */
    public static function clearCache(?int $folderId = null): void
    {
        if ($folderId !== null) {
            // Clear specific folder cache
            Cache::forget(self::CACHE_PREFIX . 'folder_' . $folderId);
        }
        
        // Clear general list cache (use pattern matching if available)
        Cache::flush(); // Or use tags if Redis is available
    }

    /**
     * Clear cache when media is updated
     */
    public static function clearMediaCache(): void
    {
        // Clear all media-related caches
        $keys = [
            'media_list_',
            'media_folder_',
            'media_recent_',
        ];

        foreach ($keys as $prefix) {
            // In production, use Redis tags for better cache management
            Cache::tags(['media'])->flush();
        }
    }
}
