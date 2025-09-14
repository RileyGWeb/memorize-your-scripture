<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\AssetHelper;
use Illuminate\Support\Facades\File;

class AssetHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up any existing test files
        $this->cleanupTestFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        parent::tearDown();
    }

    private function cleanupTestFiles()
    {
        $testFiles = [
            public_path('hot'),
            public_path('build/manifest.json'),
            public_path('build/test-manifest.json')
        ];

        foreach ($testFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Remove build directory if empty
        $buildDir = public_path('build');
        if (is_dir($buildDir) && count(scandir($buildDir)) == 2) {
            rmdir($buildDir);
        }
    }

    /** @test */
    public function it_returns_null_when_manifest_does_not_exist()
    {
        $result = AssetHelper::viteAsset('app.js');
        
        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_asset_path_when_asset_exists_in_manifest()
    {
        // Create build directory
        $buildDir = public_path('build');
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // Create test manifest
        $manifest = [
            'resources/js/app.js' => [
                'file' => 'assets/app-abc123.js',
                'isEntry' => true
            ],
            'resources/css/app.css' => [
                'file' => 'assets/app-def456.css',
                'isEntry' => true
            ]
        ];

        file_put_contents(
            public_path('build/manifest.json'),
            json_encode($manifest)
        );

        $result = AssetHelper::viteAsset('resources/js/app.js');
        
        $this->assertEquals('build/assets/app-abc123.js', $result);
    }

    /** @test */
    public function it_returns_null_when_asset_does_not_exist_in_manifest()
    {
        // Create build directory
        $buildDir = public_path('build');
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // Create test manifest
        $manifest = [
            'resources/js/app.js' => [
                'file' => 'assets/app-abc123.js',
                'isEntry' => true
            ]
        ];

        file_put_contents(
            public_path('build/manifest.json'),
            json_encode($manifest)
        );

        $result = AssetHelper::viteAsset('resources/js/nonexistent.js');
        
        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_invalid_manifest_json()
    {
        // Create build directory
        $buildDir = public_path('build');
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // Create invalid JSON manifest
        file_put_contents(
            public_path('build/manifest.json'),
            'invalid json content'
        );

        $result = AssetHelper::viteAsset('resources/js/app.js');
        
        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_true_when_hot_file_exists()
    {
        // Create hot file
        file_put_contents(public_path('hot'), 'http://localhost:5173');

        $result = AssetHelper::isViteHot();
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_hot_file_does_not_exist()
    {
        $result = AssetHelper::isViteHot();
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_handles_css_assets_correctly()
    {
        // Create build directory
        $buildDir = public_path('build');
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // Create test manifest with CSS asset
        $manifest = [
            'resources/css/app.css' => [
                'file' => 'assets/app-def456.css',
                'isEntry' => true
            ]
        ];

        file_put_contents(
            public_path('build/manifest.json'),
            json_encode($manifest)
        );

        $result = AssetHelper::viteAsset('resources/css/app.css');
        
        $this->assertEquals('build/assets/app-def456.css', $result);
    }

    /** @test */
    public function it_handles_complex_asset_paths()
    {
        // Create build directory
        $buildDir = public_path('build');
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // Create test manifest with nested asset path
        $manifest = [
            'resources/js/components/modal.js' => [
                'file' => 'assets/components/modal-xyz789.js',
                'isEntry' => false
            ]
        ];

        file_put_contents(
            public_path('build/manifest.json'),
            json_encode($manifest)
        );

        $result = AssetHelper::viteAsset('resources/js/components/modal.js');
        
        $this->assertEquals('build/assets/components/modal-xyz789.js', $result);
    }

    /** @test */
    public function it_handles_empty_manifest()
    {
        // Create build directory
        $buildDir = public_path('build');
        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // Create empty manifest
        file_put_contents(
            public_path('build/manifest.json'),
            json_encode([])
        );

        $result = AssetHelper::viteAsset('resources/js/app.js');
        
        $this->assertNull($result);
    }
}
