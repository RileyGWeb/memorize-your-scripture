<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemoryBank;
use Illuminate\Support\Facades\Auth;

class QuizSetup extends Component
{
    public $quizSetup;
    public $numberOfQuestions;
    public $difficulty = 'easy';
    public $quizType;
    public $quizTypeLabel;

    public function mount()
    {
        $this->quizSetup = session('quizSetup');
        
        if (!$this->quizSetup) {
            return redirect()->route('daily-quiz')->with('error', 'No quiz setup found. Please start a new quiz.');
        }

        $this->numberOfQuestions = $this->quizSetup['numberOfQuestions'];
        $this->quizType = $this->quizSetup['type'];
        $this->quizTypeLabel = $this->getQuizTypeLabel($this->quizType);
    }

    public function increaseNumber()
    {
        $maxQuestions = min(50, $this->getMemoryBankCount());
        if ($this->numberOfQuestions < $maxQuestions) {
            $this->numberOfQuestions++;
            $this->updateQuizVerses();
        }
    }

    public function decreaseNumber()
    {
        if ($this->numberOfQuestions > 1) {
            $this->numberOfQuestions--;
            $this->updateQuizVerses();
        }
    }

    public function startQuiz()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to take a quiz.');
            return;
        }

        // Update quiz setup with final settings
        $this->quizSetup['numberOfQuestions'] = $this->numberOfQuestions;
        $this->quizSetup['difficulty'] = $this->difficulty;

        // Create the actual quiz session
        session()->put('dailyQuiz', [
            'type' => $this->quizSetup['type'],
            'numberOfQuestions' => $this->numberOfQuestions,
            'verses' => array_slice($this->quizSetup['verses'], 0, $this->numberOfQuestions),
            'currentIndex' => 0,
            'startTime' => now(),
            'difficulty' => $this->difficulty,
        ]);

        // Clear the setup session
        session()->forget('quizSetup');

        return redirect()->route('daily-quiz', ['quiz_mode' => 1]);
    }

    protected function updateQuizVerses()
    {
        // Re-fetch verses with new count
        $verses = $this->getVersesForQuizType($this->quizType);
        $this->quizSetup['verses'] = $verses->toArray();
        $this->quizSetup['numberOfQuestions'] = $this->numberOfQuestions;
        session()->put('quizSetup', $this->quizSetup);
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
                return $query->orderByRaw('LENGTH(verses) DESC')
                    ->limit($this->numberOfQuestions)
                    ->get();

            case 'shortest':
                return $query->orderByRaw('LENGTH(verses) ASC')
                    ->limit($this->numberOfQuestions)
                    ->get();

            default:
                return $query->inRandomOrder()
                    ->limit($this->numberOfQuestions)
                    ->get();
        }
    }

    protected function getQuizTypeLabel($type)
    {
        switch ($type) {
            case 'random':
                return 'Random verses';
            case 'recent':
                return 'Most recent verses';
            case 'longest':
                return 'Longest verses';
            case 'shortest':
                return 'Shortest verses';
            default:
                return 'Random verses';
        }
    }

    protected function getMemoryBankCount()
    {
        return MemoryBank::where('user_id', Auth::id())->count();
    }

    public function render()
    {
        return view('livewire.quiz-setup');
    }
}
