<?php

namespace App\Livewire;

use Livewire\Component;

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
            
            $book = $this->findBook($bookName);
            if (!$book) {
                $suggestion = $this->suggestBook($bookName);
                if ($suggestion) {
                    throw new \Exception("Unrecognized book '$bookName'. Did you mean '$suggestion'?");
                } else {
                    throw new \Exception("Unrecognized book '$bookName'. Please check the spelling.");
                }
            }
            
            $verseRanges = $this->parseVerses($versesPart);
            
            return [$book, $chapter, $verseRanges];
        }
        
        throw new \Exception('Invalid format. Please use format like "John 3:16" or "John 3:16-18".');
    }

    protected function findBook($input)
    {
        $input = trim($input);
        
        foreach ($this->booksOfTheBible as $book) {
            if (strcasecmp($book, $input) === 0) {
                return $book;
            }
        }
        
        return null;
    }

    protected function suggestBook($input)
    {
        $input = strtolower(trim($input));
        $bestMatch = null;
        $bestDistance = PHP_INT_MAX;
        
        foreach ($this->booksOfTheBible as $book) {
            $distance = levenshtein($input, strtolower($book));
            if ($distance < $bestDistance && $distance <= 3) {
                $bestDistance = $distance;
                $bestMatch = $book;
            }
        }
        
        return $bestMatch;
    }

    protected function parseVerses($versesPart)
    {
        $ranges = explode(',', $versesPart);
        $verseRanges = [];
        
        foreach ($ranges as $range) {
            $range = trim($range);
            
            if (strpos($range, '-') !== false) {
                [$start, $end] = explode('-', $range, 2);
                $start = (int)trim($start);
                $end = (int)trim($end);
                
                if ($start <= 0 || $end <= 0 || $start > $end) {
                    throw new \Exception('Invalid verse range.');
                }
                
                $verseRanges[] = [$start, $end];
            } else {
                $verse = (int)$range;
                if ($verse <= 0) {
                    throw new \Exception('Invalid verse number.');
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
