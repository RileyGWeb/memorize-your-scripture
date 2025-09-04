<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemoryBank;
use Illuminate\Support\Facades\Http;

class QuizTaker extends Component
{
    public $quizData;
    public $currentVerse;
    public $currentIndex = 0;
    public $userInput = '';
    public $showAnswer = false;
    public $quizCompleted = false;
    public $results = [];
    public $actualVerseText = '';
    public $isLoading = false;
    public $apiError = '';

    public function mount()
    {
        $this->quizData = session('dailyQuiz');
        
        if (!$this->quizData || empty($this->quizData['verses'])) {
            session()->flash('error', 'No quiz data found. Please start a new quiz.');
            return redirect()->route('home');
        }

        $this->currentIndex = $this->quizData['currentIndex'];
        $this->loadCurrentVerse();
    }

    public function loadCurrentVerse()
    {
        if ($this->currentIndex >= count($this->quizData['verses'])) {
            $this->completeQuiz();
            return;
        }

        $this->currentVerse = $this->quizData['verses'][$this->currentIndex];
        $this->showAnswer = false;
        $this->userInput = '';
        $this->actualVerseText = '';
        $this->apiError = '';
    }

    public function submitAnswer()
    {
        $this->showAnswer = true;
        $this->fetchVerseText();
    }

    public function fetchVerseText()
    {
        $this->isLoading = true;
        $this->apiError = '';

        try {
            $book = $this->currentVerse['book'];
            $chapter = $this->currentVerse['chapter'];
            $verses = json_decode($this->currentVerse['verses'], true);

            // Build verse string for API
            $verseString = $this->buildVerseString($verses);
            $reference = $book . '.' . $chapter . '.' . $verseString;

            $apiKey = config('bible.api_key');
            $bibleId = config('bible.bible_id', 'de4e12af7f28f599-02');

            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/verses/{$reference}", [
                'content-type' => 'text',
                'include-notes' => false,
                'include-titles' => false,
                'include-chapter-numbers' => false,
                'include-verse-numbers' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->actualVerseText = strip_tags($data['data']['content'] ?? 'Content not available');
            } else {
                $this->apiError = 'Failed to fetch verse text. Please try again.';
            }
        } catch (\Exception $e) {
            $this->apiError = 'Error fetching verse: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    protected function buildVerseString($verses)
    {
        $parts = [];
        foreach ($verses as $range) {
            if ($range[0] === $range[1]) {
                $parts[] = $range[0];
            } else {
                $parts[] = $range[0] . '-' . $range[1];
            }
        }
        return implode(',', $parts);
    }

    public function nextQuestion()
    {
        // Store result
        $this->results[] = [
            'verse' => $this->currentVerse,
            'userInput' => $this->userInput,
            'actualText' => $this->actualVerseText,
            'accuracy' => $this->calculateAccuracy($this->userInput, $this->actualVerseText),
        ];

        $this->currentIndex++;
        
        // Update session
        $this->quizData['currentIndex'] = $this->currentIndex;
        session()->put('dailyQuiz', $this->quizData);

        $this->loadCurrentVerse();
    }

    protected function calculateAccuracy($userText, $actualText)
    {
        if (empty($actualText) || empty($userText)) {
            return 0;
        }

        // Simple accuracy calculation based on similar_text
        $similarity = 0;
        similar_text(strtolower(trim($userText)), strtolower(trim($actualText)), $similarity);
        return round($similarity, 1);
    }

    public function completeQuiz()
    {
        $this->quizCompleted = true;
        
        // Calculate overall stats
        $totalQuestions = count($this->results);
        $averageAccuracy = $totalQuestions > 0 ? 
            collect($this->results)->avg('accuracy') : 0;

        // Store completion data
        session()->put('quizResults', [
            'results' => $this->results,
            'totalQuestions' => $totalQuestions,
            'averageAccuracy' => round($averageAccuracy, 1),
            'quizType' => $this->quizData['type'],
            'completedAt' => now(),
        ]);

        // Clear quiz session
        session()->forget('dailyQuiz');
    }

    public function restartQuiz()
    {
        session()->forget(['dailyQuiz', 'quizResults']);
        return redirect()->route('home');
    }

    public function getVerseReference()
    {
        if (!$this->currentVerse) return '';

        $book = $this->currentVerse['book'];
        $chapter = $this->currentVerse['chapter'];
        $verses = json_decode($this->currentVerse['verses'], true);
        $verseString = $this->buildVerseString($verses);

        return "{$book} {$chapter}:{$verseString}";
    }

    public function render()
    {
        return view('livewire.quiz-taker');
    }
}
