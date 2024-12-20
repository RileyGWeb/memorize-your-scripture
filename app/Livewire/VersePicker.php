<?php

namespace App\Livewire;

use Livewire\Component;

class VersePicker extends Component
{
    public $input = '';
    public $book = '';
    public $chapter = '';
    public $verseRanges = [];

    public function updatedInput($value)
    {
        // Reset values
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
            // If parsing fails, you could show an error message,
            // or just leave things blank.
            // You can also log the exception if needed.
        }
    }

    protected function parseInput($input)
    {
        // Basic parsing logic from previous discussion:
        $parts = explode(':', $input, 2);
        if (count($parts) < 2) {
            throw new \Exception("Invalid input format");
        }

        $bookChapter = trim($parts[0]);
        $versesPart = trim($parts[1]);

        // Extract book and chapter:
        $bookChapterParts = explode(' ', $bookChapter);
        $chapter = array_pop($bookChapterParts);
        $book = implode(' ', $bookChapterParts);

        if (!is_numeric($chapter)) {
            throw new \Exception("Chapter must be numeric");
        }

        $chapter = (int) $chapter;

        // Parse verses
        $verseGroups = explode(',', $versesPart);
        $verseRanges = [];

        foreach ($verseGroups as $group) {
            $group = trim($group);
            // Normalize spaces around dashes
            $group = preg_replace('/\s*-\s*/', '-', $group);

            if (strpos($group, '-') !== false) {
                list($start, $end) = explode('-', $group);
                $start = (int)trim($start);
                $end = (int)trim($end);
                if ($start > 0 && $end >= $start) {
                    $verseRanges[] = [$start, $end];
                }
            } else {
                $verseNum = (int)$group;
                if ($verseNum > 0) {
                    $verseRanges[] = [$verseNum, $verseNum];
                }
            }
        }

        if (empty($verseRanges)) {
            throw new \Exception("No valid verses found");
        }

        return [$book, $chapter, $verseRanges];
    }

    public function render()
    {
        return view('livewire.verse-picker');
    }
}
