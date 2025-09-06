<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MemoryBank;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

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
    public $score = 0;
    public $totalAnswered = 0;

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
            $verses = $this->currentVerse['verses'];
            
            // Handle both JSON string and array formats
            if (is_string($verses)) {
                $verses = json_decode($verses, true);
            }
            
            if (!is_array($verses)) {
                $this->apiError = 'Invalid verse format';
                return;
            }

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
        // Calculate accuracy for this question
        $accuracy = $this->calculateAccuracy($this->userInput, $this->actualVerseText);
        
        // Store result
        $this->results[] = [
            'verse' => $this->currentVerse,
            'userInput' => $this->userInput,
            'actualText' => $this->actualVerseText,
            'accuracy' => $accuracy,
        ];

        // Update scoring (consider 80%+ accuracy as correct)
        $this->totalAnswered++;
        if ($accuracy >= 80) {
            $this->score++;
        }

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
        $percentageScore = $totalQuestions > 0 ? 
            ($this->score / $totalQuestions) * 100 : 0;
        
        // Calculate grade
        $grade = $this->calculateGrade($percentageScore);

        // Prepare completion data
        $completionData = [
            'results' => $this->results,
            'totalQuestions' => $totalQuestions,
            'correctAnswers' => $this->score,
            'averageAccuracy' => round($averageAccuracy, 1),
            'percentageScore' => round($percentageScore, 1),
            'grade' => $grade,
            'quizType' => $this->quizData['type'],
            'completedAt' => now(),
            'startTime' => $this->quizData['startTime'],
            'duration' => now()->diffInMinutes($this->quizData['startTime'])
        ];

        // Store completion data in session
        session()->put('quizResults', $completionData);

        // Log quiz completion to audit log
        $this->logQuizCompletion($completionData);

        // Clear quiz session
        session()->forget('dailyQuiz');
    }

    public function calculateGrade($percentage)
    {
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    protected function logQuizCompletion($data)
    {
        if (!Auth::check()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'quiz_completed',
            'table_name' => 'quiz_sessions', // Logical table name for quiz activities
            'record_id' => Auth::id(), // Use user ID as the record ID
            'old_values' => null,
            'new_values' => [
                'quiz_type' => $data['quizType'],
                'total_questions' => $data['totalQuestions'],
                'correct_answers' => $data['correctAnswers'],
                'percentage_score' => $data['percentageScore'],
                'grade' => $data['grade'],
                'average_accuracy' => $data['averageAccuracy'],
                'duration_minutes' => $data['duration'],
                'completed_at' => $data['completedAt']->toISOString(),
                'description' => "Completed {$data['quizType']} quiz with {$data['correctAnswers']}/{$data['totalQuestions']} correct ({$data['percentageScore']}% - Grade: {$data['grade']})",
            ],
            'performed_at' => now(),
        ]);
    }

    public function getCurrentPercentage()
    {
        if ($this->totalAnswered === 0) {
            return 0;
        }
        return round(($this->score / $this->totalAnswered) * 100, 1);
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
        $verses = $this->currentVerse['verses'];
        
        // Handle both JSON string and array formats
        if (is_string($verses)) {
            $verses = json_decode($verses, true);
        }
        
        if (!is_array($verses)) {
            return "{$book} {$chapter}:1";
        }
        
        $verseString = $this->buildVerseString($verses);

        return "{$book} {$chapter}:{$verseString}";
    }

    public function render()
    {
        return view('livewire.quiz-taker');
    }
}
