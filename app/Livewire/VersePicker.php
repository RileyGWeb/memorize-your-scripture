<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\BibleHelper;

class VersePicker extends Component
{
    public $input = '';
    public $book = '';
    public $chapter = '';
    public $verseRanges = [];
    public $errorMessage = '';
    public $suggestedBook = '';

    public function mount()
    {
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
            // No input, clear session
            session()->forget('verseSelection');
            return;
        }
    
        try {
            [$book, $chapter, $verseRanges] = $this->parseInput($value);
    
            $this->book = $book;
            $this->chapter = $chapter;
            $this->verseRanges = $verseRanges;
    
            // Save to session
            session()->put('verseSelection', [
                'book' => $book,
                'chapter' => $chapter,
                'verseRanges' => $verseRanges,
            ]);
    
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->suggestedBook = '';
            
            // Check if this is a "Did you mean?" error and extract the suggested book
            if (preg_match("/Unrecognized book '.*?'. Did you mean '(.*?)'\?/", $e->getMessage(), $matches)) {
                $this->suggestedBook = $matches[1];
            }
        }
    }
    

    protected function parseInput($input)
    {
        // Split at the first colon
        $parts = explode(':', $input, 2);
        if (count($parts) < 2) {
            throw new \Exception("Incomplete or invalid structure. Example: 'John 3:16-18'");
        }
    
        // e.g. "John 3"
        $bookChapter = trim($parts[0]);
        // e.g. "16-18, 22"
        $versesPart  = trim($parts[1]);
    
        // "John 3" -> ["John", "3"]
        $bookChapterParts = explode(' ', $bookChapter);
        $chapter = array_pop($bookChapterParts);
        $rawBook = implode(' ', $bookChapterParts);
    
        // Validate that $chapter is numeric
        if (!ctype_digit($chapter)) {
            throw new \Exception("Chapter must be numeric.");
        }
    
        $chapter = (int)$chapter;
    
        // Use BibleHelper to validate the book and get the canonical name
        $book = BibleHelper::findBookByName($rawBook);
        if (!$book) {
            // Try to find a suggestion
            $suggestion = BibleHelper::findClosestBook($rawBook);
            if ($suggestion) {
                throw new \Exception("Unrecognized book '$rawBook'. Did you mean '$suggestion'?");
            } else {
                throw new \Exception("Unrecognized book '$rawBook'.");
            }
        }
    
        // Validate that the chapter exists for this book
        if (!BibleHelper::isValidReference($book, $chapter, 1)) {
            $maxChapter = BibleHelper::getMaxChapter($book);
            throw new \Exception("Chapter $chapter does not exist in $book. Maximum chapter is $maxChapter.");
        }
    
        // parse the verses
        $verseGroups = explode(',', $versesPart);
        $verseRanges = [];
    
        foreach ($verseGroups as $group) {
            $group = trim($group);
            $group = preg_replace('/\s*-\s*/', '-', $group); // normalize dash spacing
    
            if (strpos($group, '-') !== false) {
                // Range format, e.g. "16-18"
                [$start, $end] = explode('-', $group);
                $start = (int)$start;
                $end   = (int)$end;
                if ($start > 0 && $end >= $start) {
                    // Validate that all verses in the range exist
                    if (!BibleHelper::isValidReference($book, $chapter, $start)) {
                        $maxVerse = BibleHelper::getMaxVerse($book, $chapter);
                        throw new \Exception("Verse $start does not exist in $book $chapter. Maximum verse is $maxVerse.");
                    }
                    if (!BibleHelper::isValidReference($book, $chapter, $end)) {
                        $maxVerse = BibleHelper::getMaxVerse($book, $chapter);
                        throw new \Exception("Verse $end does not exist in $book $chapter. Maximum verse is $maxVerse.");
                    }
                    $verseRanges[] = [$start, $end];
                } else {
                    throw new \Exception("Invalid verse range '$group'.");
                }
            } else {
                // Single verse
                $verseNum = (int)$group;
                if ($verseNum <= 0) {
                    throw new \Exception("Invalid verse number '$group'.");
                }
                // Validate that the verse exists
                if (!BibleHelper::isValidReference($book, $chapter, $verseNum)) {
                    $maxVerse = BibleHelper::getMaxVerse($book, $chapter);
                    throw new \Exception("Verse $verseNum does not exist in $book $chapter. Maximum verse is $maxVerse.");
                }
                $verseRanges[] = [$verseNum, $verseNum];
            }
        }
    
        if (empty($verseRanges)) {
            throw new \Exception("No valid verses found after parsing '$input'.");
        }
    
        return [$book, $chapter, $verseRanges];
    }
    
    public function applySuggestion()
    {
        if ($this->suggestedBook) {
            // Replace the incorrect book name with the suggested one in the input
            $currentInput = $this->input;
            
            // Extract the original book name and replace it with the suggestion
            if (preg_match('/^(.+?)\s+(\d+:.+)$/', $currentInput, $matches)) {
                $this->input = $this->suggestedBook . ' ' . $matches[2];
            } else {
                // Fallback: just replace with the suggested book if pattern doesn't match
                $this->input = $this->suggestedBook;
            }
            
            // Clear error and suggestion states
            $this->errorMessage = '';
            $this->suggestedBook = '';
            
            // Trigger the input update to re-parse
            $this->updatedInput($this->input);
        }
    }

    public function render()
    {
        return view('livewire.verse-picker');
    }
}
