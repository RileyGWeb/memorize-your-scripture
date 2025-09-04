<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizationDifficultyTest extends TestCase
{
    use RefreshDatabase;

    public function test_congratulations_logic_uses_accuracy_only(): void
    {
        // Read the memorization tool display view file
        $viewPath = resource_path('views/memorization-tool-display.blade.php');
        $viewContent = file_get_contents($viewPath);

        // Verify the checkAllSegments function only checks accuracy, not character length
        $this->assertStringContainsString('return state.accuracy >= this.requiredAccuracy();', $viewContent);
        
        // Make sure we don't have the old character length requirement
        $this->assertStringNotContainsString('state.typedText.length >= correct.length &&', $viewContent);
    }

    public function test_difficulty_thresholds_are_correct(): void
    {
        // Read the memorization tool display view file
        $viewPath = resource_path('views/memorization-tool-display.blade.php');
        $viewContent = file_get_contents($viewPath);

        // Verify the requiredAccuracy function returns correct values
        $this->assertStringContainsString('if (this.difficulty === \'easy\') return 80;', $viewContent);
        $this->assertStringContainsString('if (this.difficulty === \'normal\') return 95;', $viewContent);
        $this->assertStringContainsString('if (this.difficulty === \'strict\') return 100;', $viewContent);

        // Verify the UI shows correct thresholds
        $this->assertStringContainsString('80% accuracy', $viewContent); // Easy mode
        $this->assertStringContainsString('95% accuracy', $viewContent); // Normal mode  
        $this->assertStringContainsString('100% accuracy', $viewContent); // Strict mode
    }
}
