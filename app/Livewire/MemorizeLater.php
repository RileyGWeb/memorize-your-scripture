<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemorizeLater as MemorizeLaterModel;

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

    public function toggleExpanded()
    {
        $this->isExpanded = !$this->isExpanded;
        if (!$this->isExpanded) {
            $this->reset(['verse', 'note', 'successMessage', 'book', 'chapter', 'verseRanges', 'errorMessage', 'suggestedBook']);
        } else {
            // Clear success message when expanding to show fresh form
            $this->successMessage = '';
        }
    }

    public function updatedVerse($value)
    {
        $this->errorMessage = '';
        $this->suggestedBook = '';
        $this->book = '';
        $this->chapter = '';
        $this->verseRanges = [];
    
        if (trim($value) === '') {
            return;
        }
    
        try {
            [$book, $chapter, $verseRanges] = $this->parseInput($value);
    
            $this->book = $book;
            $this->chapter = $chapter;
            $this->verseRanges = $verseRanges;
    
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
            $currentInput = $this->verse;
            // Extract the original book name from the error
            preg_match("/^([^0-9]+)/", trim($currentInput), $matches);
            if (isset($matches[1])) {
                $originalBook = trim($matches[1]);
                $this->verse = str_replace($originalBook, $this->suggestedBook, $currentInput);
            }
        }
    }

    public function saveVerse()
    {
        dd('test');
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
            $this->reset(['verse', 'note', 'book', 'chapter', 'verseRanges', 'errorMessage', 'suggestedBook']);
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
        return view('livewire.memorize-later');
    }
}
