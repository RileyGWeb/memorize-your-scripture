<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\BibleHelper;

class UnifiedVersePicker extends Component
{
    public $input = '';
    public $book = '';
    public $chapter = '';
    public $verseRanges = [];
    public $errorMessage = '';
    public $suggestedBook = '';
    public $placeholder = 'John 3:16-18';
    public $showNextButton = false;
    public $nextButtonRoute = '';

    public function mount($showNextButton = false, $nextButtonRoute = '', $placeholder = 'John 3:16-18')
    {
        $this->showNextButton = $showNextButton;
        $this->nextButtonRoute = $nextButtonRoute;
        $this->placeholder = $placeholder;
        $this->updatedInput($this->input);
    }

    public function updatedInput($value)
    {
        $this->errorMessage = '';
        $this->suggestedBook = '';
        $this->book = '';
        $this->chapter = '';
        $this->verseRanges = [];
    
        if (trim($value) === '') {
            // No input, clear session if this is for the memorization tool
            if ($this->showNextButton) {
                session()->forget('verseSelection');
            }
            return;
        }
    
        try {
            [$book, $chapter, $verseRanges] = $this->parseInput($value);
    
            $this->book = $book;
            $this->chapter = $chapter;
            $this->verseRanges = $verseRanges;
    
            // Save to session only if this is for the memorization tool
            if ($this->showNextButton) {
                session()->put('verseSelection', [
                    'book' => $book,
                    'chapter' => $chapter,
                    'verseRanges' => $verseRanges,
                ]);
            }
    
        } catch (\Exception $e) {
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
            $currentInput = $this->input;
            // Extract the original book name from the error
            preg_match("/^([^0-9]+)/", trim($currentInput), $matches);
            if (isset($matches[1])) {
                $originalBook = trim($matches[1]);
                $this->input = str_replace($originalBook, $this->suggestedBook, $currentInput);
            }
        }
    }

    public function getParsedVerse()
    {
        if ($this->book && $this->chapter && !empty($this->verseRanges)) {
            $verses = [];
            foreach ($this->verseRanges as $range) {
                for ($i = $range[0]; $i <= $range[1]; $i++) {
                    $verses[] = $i;
                }
            }
            
            return [
                'book' => $this->book,
                'chapter' => $this->chapter,
                'verses' => $verses,
            ];
        }
        return null;
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

    public function render()
    {
        return view('livewire.unified-verse-picker');
    }
}
