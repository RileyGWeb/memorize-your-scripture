<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MemorizeLater;
use Illuminate\Support\Str;

class MemorizeLaterList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'simple-bootstrap';
    public function selectVerse($verseId)
    {
        $verse = MemorizeLater::find($verseId);
        if ($verse) {
            // Convert verses array to proper verse ranges
            $verses = $verse->verses;
            sort($verses); // Ensure verses are in order
            
            $verseRanges = [];
            if (count($verses) === 1) {
                // Single verse
                $verseRanges[] = [$verses[0], $verses[0]];
            } else {
                // Multiple verses - check if they're consecutive
                $start = $verses[0];
                $end = $verses[0];
                
                for ($i = 1; $i < count($verses); $i++) {
                    if ($verses[$i] === $end + 1) {
                        // Consecutive verse, extend the range
                        $end = $verses[$i];
                    } else {
                        // Non-consecutive, close current range and start new one
                        $verseRanges[] = [$start, $end];
                        $start = $end = $verses[$i];
                    }
                }
                // Add the final range
                $verseRanges[] = [$start, $end];
            }
            
            session()->put('verseSelection', [
                'book' => $verse->book,
                'chapter' => $verse->chapter,
                'verseRanges' => $verseRanges,
            ]);
            
            // Redirect to fetch endpoint to get verse text, then display
            $this->redirect('/memorization-tool/fetch-verse');
        }
    }

    public function formatVerseReference($verse)
    {
        $verses = $verse->verses;
        $verseStr = count($verses) === 1 
            ? $verses[0] 
            : $verses[0] . '-' . $verses[count($verses) - 1];
            
        return "{$verse->book} {$verse->chapter}:{$verseStr}";
    }

    public function formatNotePreview($note)
    {
        if (!$note) return null;
        
        return Str::limit($note, 60, '... click to see more');
    }

    public function formatRelativeDate($date)
    {
        $now = now();
        
        // Check if the date is in the future
        if ($date->isFuture()) {
            return 'just now';
        }
        
        // Calculate differences (date is in the past) and floor to get integer values
        $diffInMinutes = floor($date->diffInMinutes($now));
        $diffInHours = floor($date->diffInHours($now));
        $diffInDays = floor($date->diffInDays($now));

        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' minute' . ($diffInMinutes != 1 ? 's' : '') . ' ago';
        } elseif ($diffInHours < 24) {
            return $diffInHours . ' hour' . ($diffInHours != 1 ? 's' : '') . ' ago';
        } else {
            return $diffInDays . ' day' . ($diffInDays != 1 ? 's' : '') . ' ago';
        }
    }

    public function render()
    {
        $verses = auth()->check() 
            ? MemorizeLater::where('user_id', auth()->id())
                ->orderBy('added_at', 'desc')
                ->paginate(6)
            : collect();

        return view('livewire.memorize-later-list', [
            'verses' => $verses
        ]);
    }
}
