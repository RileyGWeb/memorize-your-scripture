<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemoryBank;
use Illuminate\Support\Facades\Auth;

class DailyQuiz extends Component
{
    public $numberOfQuestions = 10;
    public $quizType = '';
    public $difficulty = 'easy';

    public function mount()
    {
        // Set numberOfQuestions to the minimum of memorized verses or 10
        $memoryBankCount = $this->getMemoryBankCount();
        $this->numberOfQuestions = min($memoryBankCount > 0 ? $memoryBankCount : 1, 10);
    }

    public function increaseNumber()
    {
        $maxQuestions = min(50, $this->getMemoryBankCount());
        if ($this->numberOfQuestions < $maxQuestions) {
            $this->numberOfQuestions++;
        }
    }

    public function increaseNumberBy10()
    {
        $maxQuestions = min(50, $this->getMemoryBankCount());
        $newNumber = $this->numberOfQuestions + 10;
        $this->numberOfQuestions = min($newNumber, $maxQuestions);
    }

    public function decreaseNumber()
    {
        if ($this->numberOfQuestions > 1) {
            $this->numberOfQuestions--;
        }
    }

    public function decreaseNumberBy10()
    {
        $newNumber = $this->numberOfQuestions - 10;
        $this->numberOfQuestions = max($newNumber, 1);
    }

    public function startQuiz($type)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to take a quiz.');
            return;
        }

        $verses = $this->getVersesForQuizType($type);
        
        if ($verses->isEmpty()) {
            session()->flash('error', 'You need to memorize some verses first before taking a quiz.');
            return;
        }

        // Store initial quiz setup in session
        session()->put('quizSetup', [
            'type' => $type,
            'numberOfQuestions' => $this->numberOfQuestions,
            'verses' => $verses->toArray(),
        ]);

        return redirect()->route('quiz.setup');
    }

    protected function getVersesForQuizType($type)
    {
        $query = MemoryBank::where('user_id', Auth::id());

        switch ($type) {
            case 'random':
                return $query->inRandomOrder()
                    ->limit($this->numberOfQuestions)
                    ->get();

            case 'recent':
                return $query->orderBy('memorized_at', 'desc')
                    ->whereNotNull('memorized_at')
                    ->limit($this->numberOfQuestions)
                    ->get();

            case 'longest':
                return $query->get()
                    ->sortByDesc(function ($verse) {
                        return $this->calculateVerseLength($verse);
                    })
                    ->take($this->numberOfQuestions);

            case 'shortest':
                return $query->get()
                    ->sortBy(function ($verse) {
                        return $this->calculateVerseLength($verse);
                    })
                    ->take($this->numberOfQuestions);

            case 'all':
                return $query->orderBy('memorized_at', 'desc')
                    ->get();

            default:
                return collect();
        }
    }

    public function calculateVerseLength($verse)
    {
        // Calculate total number of verses in the range
        $verseRanges = $verse->verses;
        if (!is_array($verseRanges)) {
            $verseRanges = json_decode($verse->verses, true);
        }
        
        if (!is_array($verseRanges)) {
            return 1;
        }

        $totalVerses = 0;
        foreach ($verseRanges as $range) {
            if (is_array($range) && count($range) === 2) {
                $totalVerses += ($range[1] - $range[0] + 1);
            }
        }

        return $totalVerses;
    }

    public function getMemoryBankCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        return MemoryBank::where('user_id', Auth::id())->count();
    }

    public function render()
    {
        return view('livewire.daily-quiz');
    }
}
