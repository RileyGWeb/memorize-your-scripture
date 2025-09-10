<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizationToolAlpineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function memorization_tool_display_loads_without_alpine_errors()
    {
        // Set up session data that the controller expects
        session([
            'fetchedVerseText' => [
                'data' => [
                    [
                        'content' => '15For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.'
                    ]
                ]
            ],
            'verseSelection' => [
                'book' => 'John',
                'chapter' => 3,
                'verseRanges' => [[15, 15]]
            ]
        ]);
        
        $response = $this->get('/memorization-tool/display?book=John&chapter=3&verses=15');
        
        $response->assertStatus(200);
        
        // Check that Alpine.js component data is properly defined
        $content = $response->getContent();
        
        // Look for the memTool component function call
        $this->assertStringContainsString('x-data="memTool({', $content);
        
        // Check that the Alpine.js script block exists
        $this->assertStringContainsString('Alpine.data(\'memTool\'', $content);
        
        // Check that all essential Alpine.js variables are defined in the component
        
        // Verify that the component defines all the properties used in the template
        $this->assertStringContainsString('hidden:', $content);
        $this->assertStringContainsString('reference:', $content);
        
        // Verify that methods are defined
        $this->assertStringContainsString('buildDisplayFull()', $content);
        $this->assertStringContainsString('hideVerse()', $content);
        $this->assertStringContainsString('showVerse()', $content);
    }
    
    /** @test */
    public function memorization_tool_contains_valid_javascript_syntax()
    {
        // Set up session data that the controller expects
        session([
            'fetchedVerseText' => [
                'data' => [
                    [
                        'content' => '15For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.'
                    ]
                ]
            ],
            'verseSelection' => [
                'book' => 'John',
                'chapter' => 3,
                'verseRanges' => [[15, 15]]
            ]
        ]);
        
        $response = $this->get('/memorization-tool/display?book=John&chapter=3&verses=15');
        
        $content = $response->getContent();
        
        // Extract the script content - look for our specific script
        preg_match_all('/<script>(.*?)<\/script>/s', $content, $allMatches);
        $this->assertNotEmpty($allMatches[0], 'Script blocks should be present');
        
        // Find our Alpine.js script specifically
        $scriptContent = '';
        foreach ($allMatches[1] as $script) {
            if (str_contains($script, 'Alpine.data(\'memTool\'')) {
                $scriptContent = $script;
                break;
            }
        }
        
        $this->assertNotEmpty($scriptContent, 'Alpine.js memTool script should be present');
        
        // Check for common syntax issues that would break Alpine.js
        $this->assertStringNotContainsString('undefined', $scriptContent, 'Should not contain undefined values');
        $this->assertStringNotContainsString('{{', $scriptContent, 'Should not contain unprocessed Blade syntax');
        
        // Verify proper JSON structure in @js() outputs
        $this->assertStringContainsString('segments', $scriptContent);
        $this->assertStringContainsString('reference', $scriptContent);
    }
    
    /** @test */
    public function daily_quiz_page_loads_successfully()
    {
        $response = $this->get('/quiz');
        
        $response->assertStatus(200);
        
        // Should load the daily quiz page with proper title
        $content = $response->getContent();
        $this->assertStringContainsString('<title>', $content);
    }
}
