<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Get the built asset path from the Vite manifest
     */
    public static function viteAsset(string $asset): ?string
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            return null;
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        if (!isset($manifest[$asset])) {
            return null;
        }
        
        return 'build/' . $manifest[$asset]['file'];
    }
    
    /**
     * Check if we're in development mode with hot reload
     */
    public static function isViteHot(): bool
    {
        return file_exists(public_path('hot'));
    }
}
