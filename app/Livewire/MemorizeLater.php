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

    public function toggleExpanded()
    {
        $this->isExpanded = !$this->isExpanded;
        if (!$this->isExpanded) {
            $this->reset(['verse', 'note', 'successMessage']);
        } else {
            $this->successMessage = '';
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

        // Parse the verse reference (e.g., "John 3:16-18" or "Psalms 23:1")
        $parsed = $this->parseVerseReference($this->verse);
        
        if (!$parsed) {
            $this->addError('verse', 'Please enter a valid verse reference (e.g., "John 3:16" or "Psalms 23:1-3")');
            return;
        }

        // Check if user is authenticated
        if (!auth()->check()) {
            $this->addError('verse', 'You must be logged in to save verses.');
            return;
        }

        try {
            // Save to database
            MemorizeLaterModel::create([
                'user_id' => auth()->id(),
                'book' => $parsed['book'],
                'chapter' => $parsed['chapter'],
                'verses' => $parsed['verses'],
                'note' => $this->note ?: null,
                'added_at' => now(),
            ]);

            $this->successMessage = 'Verse saved successfully!';
            $this->reset(['verse', 'note']);
        } catch (\Exception $e) {
            $this->addError('verse', 'Failed to save verse. Please try again.');
        }
    }

    public function clearSuccess()
    {
        $this->successMessage = '';
        $this->isExpanded = false;
    }

    private function parseVerseReference($reference)
    {
        // Remove extra spaces and normalize
        $reference = trim($reference);
        
        // Pattern to match "Book Chapter:Verse-Verse" format
        // Handles: "John 3:16", "John 3:16-18", "1 John 3:16", "Psalms 23:1-3"
        if (preg_match('/^(.+?)\s+(\d+):(\d+)(?:-(\d+))?$/', $reference, $matches)) {
            $book = trim($matches[1]);
            $chapter = (int)$matches[2];
            $startVerse = (int)$matches[3];
            $endVerse = isset($matches[4]) ? (int)$matches[4] : $startVerse;
            
            // Ensure end verse is not less than start verse
            if ($endVerse < $startVerse) {
                return null;
            }
            
            // Create array of verse numbers
            $verses = range($startVerse, $endVerse);
            
            return [
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
            ];
        }
        
        // Alternative pattern for just "Book Chapter" (assume verse 1)
        if (preg_match('/^(.+?)\s+(\d+)$/', $reference, $matches)) {
            $book = trim($matches[1]);
            $chapter = (int)$matches[2];
            
            return [
                'book' => $book,
                'chapter' => $chapter,
                'verses' => [1],
            ];
        }
        
        return null;
    }

    public function render()
    {
        return view('livewire.memorize-later');
    }
}
