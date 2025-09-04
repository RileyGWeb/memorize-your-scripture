<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemorizeLater;
use Illuminate\Support\Str;

class MemorizeLaterList extends Component
{
    public $showOnMemorizationTool = false;

    public function mount($showOnMemorizationTool = false)
    {
        $this->showOnMemorizationTool = $showOnMemorizationTool;
    }

    public function selectVerse($verseId)
    {
        if ($this->showOnMemorizationTool) {
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
                
                return redirect()->route('memorization-tool.fetch');
            }
        }
    }

    public function removeVerse($verseId)
    {
        if (auth()->check()) {
            MemorizeLater::where('id', $verseId)
                ->where('user_id', auth()->id())
                ->delete();
            
            // Refresh the component
            $this->dispatch('$refresh');
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

    public function formatDate($date)
    {
        return $date->format('n/j/y');
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
