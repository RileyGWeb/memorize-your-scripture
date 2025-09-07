<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemorizationToolUXTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function memorization_tool_display_file_contains_sticky_score_card()
    {
        $filePath = resource_path('views/memorization-tool-display.blade.php');
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('sticky top-2 z-10 bg-white', $content);
    }

    /** @test */
    public function memorization_tool_display_file_contains_scroll_functionality()
    {
        $filePath = resource_path('views/memorization-tool-display.blade.php');
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('scrollToMainContent()', $content);
        $this->assertStringContainsString('x-ref="mainContentCard"', $content);
        $this->assertStringContainsString('@focus="scrollToMainContent()"', $content);
    }

    /** @test */
    public function memorization_tool_display_file_contains_scroll_implementation()
    {
        $filePath = resource_path('views/memorization-tool-display.blade.php');
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('getBoundingClientRect', $content);
        $this->assertStringContainsString('stickyHeaderHeight', $content);
        $this->assertStringContainsString('behavior: \'smooth\'', $content);
        $this->assertStringContainsString('$nextTick', $content);
    }

    /** @test */
    public function memorization_tool_display_file_has_proper_structure()
    {
        $filePath = resource_path('views/memorization-tool-display.blade.php');
        $content = file_get_contents($filePath);
        
        // Check that the sticky card comes before the main content card
        $stickyPosition = strpos($content, 'sticky top-2 z-10 bg-white');
        $mainContentPosition = strpos($content, 'x-ref="mainContentCard"');
        
        $this->assertNotFalse($stickyPosition);
        $this->assertNotFalse($mainContentPosition);
        $this->assertLessThan($mainContentPosition, $stickyPosition);
    }
}
