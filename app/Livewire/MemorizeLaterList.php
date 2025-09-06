<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemorizeLater;
use Illuminate\Support\Str;

class MemorizeLaterList extends Component
{
    public function selectVerse($verseId)
    {
        $verse = MemorizeLater::find($verseId);
        if ($verse) {
            // Store the verse selection in session for the memorization tool
            $verseRanges = [];
            foreach ($verse->verses as $verseNum) {
                $verseRanges[] = [$verseNum, $verseNum];
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
                ->get()
            : collect();

        return view('livewire.memorize-later-list', [
            'verses' => $verses
        ]);
    }
}
