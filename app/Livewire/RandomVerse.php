<?php

namespace App\Livewire;

use Livewire\Component;
use App\Http\Controllers\RandomVerseController;
use App\Models\MemorizeLater;
use App\Helpers\BibleHelper;

class RandomVerse extends Component
{
    public $randomType = 'popular';
    public $verseData = null;
    public $loading = false;
    public $error = null;
    public $successMessage = null;

    public function mount()
    {
        // Clear any existing verse data on mount (page refresh or navigation)
        // This ensures a clean state when the user returns to this page
        $this->clearVerse();
        
        // Don't auto-load a verse - let the user choose when to get one
        // This gives them a clean slate each time they visit the page
    }

    public function clearVerse()
    {
        $this->verseData = null;
        $this->error = null;
        $this->successMessage = null;
        $this->loading = false;
    }

    public function getRandomVerse()
    {
        $this->loading = true;
        $this->error = null;
        $this->successMessage = null;
        $this->verseData = null;

        try {
            if ($this->randomType === 'popular') {
                $this->getRandomPopularVerse();
            } else {
                $this->getTrulyRandomVerse();
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred while fetching the verse.';
            \Log::error('Random verse error: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    private function getRandomPopularVerse()
    {
        $popularVerses = config('popular_verses.popular_verses');
        if (empty($popularVerses)) {
            $this->error = 'Popular verses not configured.';
            return;
        }
        
        // Validate and filter popular verses
        $validVerses = [];
        foreach ($popularVerses as $verseRef) {
            $parsed = BibleHelper::parseAndValidateReference($verseRef);
            if ($parsed) {
                $validVerses[] = $verseRef;
            }
        }
        
        if (empty($validVerses)) {
            $this->error = 'No valid popular verses found.';
            return;
        }
        
        $randomVerse = $validVerses[array_rand($validVerses)];
        \Log::info('Selected random verse: ' . $randomVerse);
        $this->fetchVerseText($randomVerse);
    }

    private function getTrulyRandomVerse()
    {
        // Use BibleHelper to get a truly random, validated verse
        $randomRef = BibleHelper::getRandomVerseReference();
        
        \Log::info('Generated random verse: ' . $randomRef['reference']);
        $this->fetchVerseText($randomRef['reference']);
    }

    private function fetchVerseText($verseReference)
    {
        try {
            \Log::info('Fetching verse: ' . $verseReference);
            
            $apiKey = env('BIBLE_API_KEY');
            if (empty($apiKey)) {
                $this->error = 'Bible API key not configured.';
                return;
            }

            // Use the passages endpoint like other components
            $response = \Http::timeout(10)
                ->withHeaders([
                    'api-key' => $apiKey
                ])
                ->get('https://api.scripture.api.bible/v1/bibles/de4e12af7f28f599-02/passages', [
                    'reference' => $verseReference
                ]);

            \Log::info('API Response status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();
                \Log::info('API Response data structure: ' . json_encode(array_keys($data)));
                
                // Check if we have data - it should be an array of passages
                if (!empty($data['data']) && is_array($data['data'])) {
                    $passage = $data['data'][0]; // Get the first passage
                    
                    // Parse the verse reference to extract book, chapter, verse
                    preg_match('/^(.+?)\s+(\d+):(\d+)/', $verseReference, $matches);
                    
                    $this->verseData = [
                        'reference' => $passage['reference'] ?? $verseReference,
                        'text' => strip_tags($passage['content'] ?? ''),
                        'book' => $matches[1] ?? '',
                        'chapter' => (int)($matches[2] ?? 1),
                        'verse' => (int)($matches[3] ?? 1)
                    ];
                    
                    \Log::info('Successfully set verse data');
                } else {
                    \Log::info('Full API response: ' . $response->body());
                    $this->error = 'No verse found for: ' . $verseReference;
                }
            } else {
                $this->error = 'Failed to fetch verse from API. Status: ' . $response->status();
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to connect to verse API: ' . $e->getMessage();
            \Log::error('Bible API error: ' . $e->getMessage());
        }
    }

    public function addToMemorizeLater()
    {
        if (!$this->verseData) {
            return;
        }

        if (!auth()->check()) {
            $this->error = 'You must be logged in to save verses.';
            return;
        }

        try {
            MemorizeLater::create([
                'user_id' => auth()->id(),
                'book' => $this->verseData['book'],
                'chapter' => $this->verseData['chapter'],
                'verses' => [$this->verseData['verse']],
                'note' => null,
                'added_at' => now(),
            ]);

            $this->successMessage = 'Verse added to memorize later!';
            $this->error = null;
            
            // Dispatch event to refresh the memorize later list
            $this->dispatch('refreshMemorizeLaterList');
        } catch (\Exception $e) {
            $this->error = 'Failed to add verse. Please try again.';
        }
    }

    public function memorizeNow()
    {
        if (!$this->verseData) {
            $this->error = 'No verse selected to memorize.';
            return;
        }

        try {
            // Set up session data that the memorization display expects
            $verseSelection = [
                'book' => $this->verseData['book'],
                'chapter' => $this->verseData['chapter'],
                'verseRanges' => [[$this->verseData['verse'], $this->verseData['verse']]],
                'bible_translation' => config('bible.default', 'de4e12af7f28f599-02') // Add the default translation
            ];

            $fetchedVerseText = [
                'data' => [[
                    'content' => $this->verseData['text'],
                    'reference' => $this->verseData['reference']
                ]]
            ];

            session()->put('verseSelection', $verseSelection);
            session()->put('fetchedVerseText', $fetchedVerseText);

            // Use Livewire's navigate functionality for faster navigation
            return $this->redirect('/memorization-tool/display', navigate: true);
            
        } catch (\Exception $e) {
            \Log::error('Error in memorizeNow: ' . $e->getMessage());
            $this->error = 'Error setting up memorization: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.random-verse');
    }
}
