<?php

namespace App\Livewire;

use Livewire\Component;

class VersePicker extends Component
{
    public $input = '';
    public $book = '';
    public $chapter = '';
    public $verseRanges = [];
    public $errorMessage = '';
    private array $booksOfTheBible = [
        // Old Testament
        'Genesis', 'Exodus', 'Leviticus', 'Numbers', 'Deuteronomy',
        'Joshua', 'Judges', 'Ruth', '1 Samuel', '2 Samuel',
        '1 Kings', '2 Kings', '1 Chronicles', '2 Chronicles', 'Ezra',
        'Nehemiah', 'Esther', 'Job', 'Psalms', 'Proverbs',
        'Ecclesiastes', 'Song of Solomon', 'Isaiah', 'Jeremiah', 'Lamentations',
        'Ezekiel', 'Daniel', 'Hosea', 'Joel', 'Amos',
        'Obadiah', 'Jonah', 'Micah', 'Nahum', 'Habakkuk',
        'Zephaniah', 'Haggai', 'Zechariah', 'Malachi',
    
        // New Testament
        'Matthew', 'Mark', 'Luke', 'John', 'Acts',
        'Romans', '1 Corinthians', '2 Corinthians', 'Galatians', 'Ephesians',
        'Philippians', 'Colossians', '1 Thessalonians', '2 Thessalonians', '1 Timothy',
        '2 Timothy', 'Titus', 'Philemon', 'Hebrews', 'James',
        '1 Peter', '2 Peter', '1 John', '2 John', '3 John',
        'Jude', 'Revelation'
    ];    

    public function mount()
    {
        $this->updatedInput($this->input);
    }

    public function updatedInput($value)
    {
        $this->errorMessage = '';
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
    
        // **Step 1**: Try an exact (case-insensitive) match
        $lowerBooks = array_map('strtolower', $this->booksOfTheBible);
        $rawBookLower = strtolower($rawBook);
    
        $bookIndex = array_search($rawBookLower, $lowerBooks);
        if ($bookIndex === false) {
            // **Step 2**: Not found. Attempt fuzzy matching.
            $suggestedBook = $this->findClosestBook($rawBook);
            if ($suggestedBook) {
                // We have a possible "did you mean?" suggestion
                // Instead of directly using it, you might want to throw an exception with the suggestion
                throw new \Exception("Unrecognized book '$rawBook'. Did you mean '$suggestedBook'?");
            } else {
                // No good suggestion
                throw new \Exception("Unrecognized book '$rawBook'.");
            }
        }
    
        // Retrieve the canonical/correct form from the array
        $book = $this->booksOfTheBible[$bookIndex];
    
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
                $verseRanges[] = [$verseNum, $verseNum];
            }
        }
    
        if (empty($verseRanges)) {
            throw new \Exception("No valid verses found after parsing '$input'.");
        }
    
        return [$book, $chapter, $verseRanges];
    }
    
    
    protected function findClosestBook($rawBook)
{
        $bookLower = strtolower($rawBook);

        // We convert each official book to lowercase, then compare.
        $lowestDistance = PHP_INT_MAX;
        $closestMatch = null;

        foreach ($this->booksOfTheBible as $bookName) {
            $distance = levenshtein($bookLower, strtolower($bookName));
            if ($distance < $lowestDistance) {
                $lowestDistance = $distance;
                $closestMatch = $bookName;
            }
        }

        $threshold = 4;

        if ($lowestDistance <= $threshold) {
            return $closestMatch; 
        }

        return null; 
    }

    public function render()
    {
        return view('livewire.verse-picker');
    }
}
