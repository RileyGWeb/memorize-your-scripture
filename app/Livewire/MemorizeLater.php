<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemorizeLater as MemorizeLaterModel;
use Illuminate\Support\Facades\Http;
use App\Helpers\BibleHelper;

class MemorizeLater extends Component
{
    public $verse = '';
    public $note = '';
    public $isExpanded = false;
    public $successMessage = '';
    
    // Verse picker properties
    public $book = '';
    public $chapter = '';
    public $verseRanges = [];
    public $errorMessage = '';
    public $suggestedBook = '';
    public $verseText = '';

    public function toggleExpanded()
    {
        $this->isExpanded = !$this->isExpanded;
        if (!$this->isExpanded) {
            $this->reset(['verse', 'note', 'successMessage', 'book', 'chapter', 'verseRanges', 'errorMessage', 'suggestedBook', 'verseText']);
        } else {
            // Clear success message when expanding to show fresh form
            $this->successMessage = '';
        }
    }

    public function updatedVerse($value)
    {
        \Log::info('updatedVerse called', ['value' => $value]);
        
        $this->errorMessage = '';
        $this->suggestedBook = '';
        $this->book = '';
        $this->chapter = '';
        $this->verseRanges = [];
        $this->verseText = '';
    
        if (trim($value) === '') {
            \Log::info('Empty value, returning');
            return;
        }
    
        try {
            \Log::info('Parsing input');
            [$book, $chapter, $verseRanges] = $this->parseInput($value);
    
            $this->book = $book;
            $this->chapter = $chapter;
            $this->verseRanges = $verseRanges;
            
            \Log::info('About to fetch verse text', ['book' => $book, 'chapter' => $chapter]);
            
            // Fetch the verse text from the API
            $this->fetchVerseText();
            
            \Log::info('After fetchVerseText', ['verseText' => $this->verseText]);
    
        } catch (\Exception $e) {
            \Log::error('Exception in updatedVerse', ['error' => $e->getMessage()]);
            $this->errorMessage = $e->getMessage();
            $this->suggestedBook = '';
            
            // Check if this is a "Did you mean?" error and extract the suggested book
            if (preg_match("/Unrecognized book '.*?'. Did you mean '(.*?)'\?/", $e->getMessage(), $matches)) {
                $this->suggestedBook = $matches[1];
            }
        }
    }

    public function applySuggestion()
    {
        if ($this->suggestedBook) {
            // Replace the book name in the input with the suggested one
            $currentInput = $this->verse;
            // Extract the original book name from the error
            preg_match("/^([^0-9]+)/", trim($currentInput), $matches);
            if (isset($matches[1])) {
                $originalBook = trim($matches[1]);
                $this->verse = str_replace($originalBook, $this->suggestedBook, $currentInput);
                
                // Manually trigger the parsing to update the component state
                $this->updatedVerse($this->verse);
            }
        }
    }

    public function saveVerse()
    {
        $this->validate([
            'verse' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
        ], [
            'verse.required' => 'Please enter a verse reference.',
            'verse.max' => 'Verse reference is too long.',
            'note.max' => 'Note is too long (maximum 1000 characters).',
        ]);

        // Use the parsed verse data from the verse picker
        if (!$this->book || !$this->chapter || empty($this->verseRanges)) {
            $this->addError('verse', 'Please enter a valid verse reference (e.g., "John 3:16" or "Psalms 23:1-3")');
            return;
        }

        // Check if user is authenticated
        if (!auth()->check()) {
            $this->addError('verse', 'You must be logged in to save verses.');
            return;
        }

        // Validate that the verse exists using Bible API
        if (!$this->validateVerseExists()) {
            $this->addError('verse', 'This verse reference does not exist. Please check the book, chapter, and verse numbers.');
            return;
        }

        try {
            // Convert verse ranges to simple array of verse numbers
            $verses = [];
            foreach ($this->verseRanges as $range) {
                for ($i = $range[0]; $i <= $range[1]; $i++) {
                    $verses[] = $i;
                }
            }

            // Save to database
            MemorizeLaterModel::create([
                'user_id' => auth()->id(),
                'book' => $this->book,
                'chapter' => $this->chapter,
                'verses' => $verses,
                'note' => $this->note ?: null,
                'added_at' => now(),
            ]);

            $this->successMessage = 'Verse saved successfully!';
            $this->reset(['verse', 'note', 'book', 'chapter', 'verseRanges', 'errorMessage', 'suggestedBook', 'verseText']);
            $this->isExpanded = false; // Close the dropdown
        } catch (\Exception $e) {
            $this->addError('verse', 'Failed to save verse. Please try again.');
        }
    }

    public function clearSuccess()
    {
        $this->successMessage = '';
        $this->isExpanded = false;
    }

    /**
     * Add a verse directly with book, chapter, and verses
     * Used by the random verse feature
     */
    public function addVerse($book, $chapter, $verses, $note = null)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            session()->flash('error', 'You must be logged in to save verses.');
            return false;
        }

        try {
            // Validate the verse reference using BibleHelper
            if (!BibleHelper::isValidBook($book)) {
                session()->flash('error', "Invalid book: $book");
                return false;
            }
            
            if (!BibleHelper::isValidChapter($book, $chapter)) {
                $maxChapter = BibleHelper::getMaxChapter($book);
                session()->flash('error', "Chapter $chapter does not exist in $book. Maximum chapter is $maxChapter.");
                return false;
            }

            // Ensure verses is an array
            if (!is_array($verses)) {
                $verses = [$verses];
            }
            
            // Validate each verse
            $maxVerse = BibleHelper::getMaxVerse($book, $chapter);
            foreach ($verses as $verse) {
                if (!BibleHelper::isValidVerse($book, $chapter, $verse)) {
                    session()->flash('error', "Verse $verse does not exist in $book $chapter. Maximum verse is $maxVerse.");
                    return false;
                }
            }

            // Save to database
            MemorizeLaterModel::create([
                'user_id' => auth()->id(),
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
                'note' => $note,
                'added_at' => now(),
            ]);

            session()->flash('success', 'Verse added to memorize later!');
            $this->dispatch('refreshMemorizeLaterList');
            return true;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add verse. Please try again.');
            return false;
        }
    }

    protected function parseInput($input)
    {
        $input = trim($input);
        
        // Updated regex to handle optional comma and space after chapter:verse
        if (preg_match('/^(.+?)\s+(\d+):(.+)$/', $input, $matches)) {
            $bookName = trim($matches[1]);
            $chapter = (int)$matches[2];
            $versesPart = trim($matches[3]);
            
            // Use BibleHelper for book validation
            if (!BibleHelper::isValidBook($bookName)) {
                $suggestions = BibleHelper::getSimilarBooks($bookName, 1);
                if (!empty($suggestions)) {
                    throw new \Exception("Unrecognized book '$bookName'. Did you mean '{$suggestions[0]}'?");
                } else {
                    throw new \Exception("Unrecognized book '$bookName'. Please check the spelling.");
                }
            }
            
            // Validate chapter exists
            if (!BibleHelper::isValidChapter($bookName, $chapter)) {
                $maxChapter = BibleHelper::getMaxChapter($bookName);
                throw new \Exception("Chapter $chapter does not exist in $bookName. Maximum chapter is $maxChapter.");
            }
            
            $verseRanges = $this->parseVerses($versesPart, $bookName, $chapter);
            
            return [$bookName, $chapter, $verseRanges];
        }
        
        throw new \Exception('Invalid format. Please use format like "John 3:16" or "John 3:16-18".');
    }

    protected function findBook($input)
    {
        return BibleHelper::findBookByName($input);
    }

    protected function suggestBook($input)
    {
        return BibleHelper::findClosestBook($input);
    }

    protected function parseVerses($versesPart, $book, $chapter)
    {
        $ranges = explode(',', $versesPart);
        $verseRanges = [];
        $maxVerse = BibleHelper::getMaxVerse($book, $chapter);
        
        foreach ($ranges as $range) {
            $range = trim($range);
            
            if (strpos($range, '-') !== false) {
                [$start, $end] = explode('-', $range, 2);
                $start = (int)trim($start);
                $end = (int)trim($end);
                
                if ($start <= 0 || $end <= 0 || $start > $end) {
                    throw new \Exception('Invalid verse range.');
                }
                
                // Validate verses exist
                if ($start > $maxVerse) {
                    throw new \Exception("Verse $start does not exist in $book $chapter. Maximum verse is $maxVerse.");
                }
                if ($end > $maxVerse) {
                    throw new \Exception("Verse $end does not exist in $book $chapter. Maximum verse is $maxVerse.");
                }
                
                $verseRanges[] = [$start, $end];
            } else {
                $verse = (int)$range;
                if ($verse <= 0) {
                    throw new \Exception('Invalid verse number.');
                }
                
                // Validate verse exists
                if ($verse > $maxVerse) {
                    throw new \Exception("Verse $verse does not exist in $book $chapter. Maximum verse is $maxVerse.");
                }
                
                $verseRanges[] = [$verse, $verse];
            }
        }
        
        return $verseRanges;
    }

    /**
     * Validate that the verse reference exists using the Bible API
     */
    private function validateVerseExists()
    {
        try {
            $apiKey = config('services.bible.api_key');
            if (!$apiKey) {
                // If no API key is configured, skip validation
                return true;
            }

            $bibleId = config('bible.default_id', '9879dbb7cfe39e4d-02');
            
            // Format the reference for API call
            $reference = $this->formatReferenceForApi();
            
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
                'reference' => $reference,
            ]);

            // If the API call succeeds (status 200), the verse exists
            // If it returns 404, the verse doesn't exist
            return $response->successful();
            
        } catch (\Exception $e) {
            // If there's an error with the API call, don't block the user
            \Log::warning('Verse validation failed', [
                'error' => $e->getMessage(),
                'reference' => $this->verse
            ]);
            return true;
        }
    }

    /**
     * Format the verse reference for Bible API
     */
    private function formatReferenceForApi()
    {
        $formattedRanges = [];
        foreach ($this->verseRanges as $range) {
            if ($range[0] === $range[1]) {
                $formattedRanges[] = $range[0];
            } else {
                $formattedRanges[] = $range[0] . '-' . $range[1];
            }
        }
        
        return $this->book . ' ' . $this->chapter . ':' . implode(',', $formattedRanges);
    }
    
    /**
     * Get formatted verse reference for display
     */
    public function getFormattedReferenceProperty()
    {
        if (!$this->book || !$this->chapter || empty($this->verseRanges)) {
            return '';
        }
        
        $formattedRanges = [];
        foreach ($this->verseRanges as $range) {
            if ($range[0] === $range[1]) {
                $formattedRanges[] = $range[0];
            } else {
                $formattedRanges[] = $range[0] . '-' . $range[1];
            }
        }
        
        return $this->book . ' ' . $this->chapter . ':' . implode(', ', $formattedRanges);
    }
    
    /**
     * Fetch the verse text from the Bible API
     */
    private function fetchVerseText()
    {
        try {
            $apiKey = config('services.bible.api_key');
            if (!$apiKey) {
                // If no API key is configured, skip fetching text
                \Log::info('No API key configured for verse text');
                return;
            }

            $bibleId = config('bible.default_id', '9879dbb7cfe39e4d-02');
            $reference = $this->formatReferenceForApi();
            
            \Log::info('Fetching verse text', ['reference' => $reference, 'bibleId' => $bibleId]);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'api-key' => $apiKey
                ])
                ->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
                    'reference' => $reference
                ]);

            \Log::info('API Response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['data']) && is_array($data['data']) && isset($data['data'][0]['content'])) {
                    // Strip HTML tags and clean up the text
                    $this->verseText = strip_tags($data['data'][0]['content']);
                    \Log::info('Verse text set', ['text' => $this->verseText]);
                } else {
                    \Log::warning('No content in API response', ['data' => $data]);
                }
            } else {
                \Log::warning('API request failed', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            // If there's an error fetching the text, just log it and continue
            \Log::warning('Failed to fetch verse text', [
                'error' => $e->getMessage(),
                'reference' => $this->formatReferenceForApi()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.memorize-later');
    }
}
