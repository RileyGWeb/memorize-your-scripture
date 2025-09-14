<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we have a basic Vite manifest for testing
        $this->ensureViteManifestExists();
    }
    
    /**
     * Ensure a basic Vite manifest exists for testing.
     * This prevents tests from failing due to missing build files in development.
     */
    protected function ensureViteManifestExists(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $buildDir = dirname($manifestPath);
        $assetsDir = $buildDir . '/assets';
        
        // Create build directories if they don't exist
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }
        
        // Only create a manifest if one doesn't exist
        if (!file_exists($manifestPath)) {
            $minimalManifest = [
                'resources/js/app.js' => [
                    'file' => 'assets/app-test.js',
                    'src' => 'resources/js/app.js',
                    'isEntry' => true,
                    'css' => ['assets/app-test.css']
                ],
                'resources/css/app.css' => [
                    'file' => 'assets/app-test.css',
                    'src' => 'resources/css/app.css',
                    'isEntry' => true
                ]
            ];
            
            file_put_contents($manifestPath, json_encode($minimalManifest, JSON_PRETTY_PRINT));
            
            // Create dummy asset files
            file_put_contents($assetsDir . '/app-test.js', '// Test JS file');
            file_put_contents($assetsDir . '/app-test.css', '/* Test CSS file */');
        }
    }
}
