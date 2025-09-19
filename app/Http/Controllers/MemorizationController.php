<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\MemoryBank;

class MemorizationController extends Controller
{
    public function show(Request $request)
    {
        $quizMode = $request->boolean('quiz_mode', false);
        
        if ($quizMode) {
            return $this->showQuiz($request);
        }
        
        // If not in quiz mode and we're on daily-quiz or quiz route, show the quiz initialization
        if (in_array($request->route()->getName(), ['daily-quiz', 'quiz'])) {
            return view('daily-quiz');
        }
        
        return $this->showMemorization($request);
    }
    
    private function showMemorization(Request $request)
    {
        $verseData = session('fetchedVerseText');
        if (!$verseData) {
            return redirect()->route('memorization-tool.picker');
        }

        $raw = $verseData['data'][0]['content'];
        $raw = preg_replace('/(\d)([A-Z])/', '$1 $2', $raw);
        $segments = $this->parseVerseSegments($raw);

        foreach ($segments as &$seg) {
            $seg['numLines'] = ceil(strlen($seg['text']) / 35);
        }
        unset($seg);

        $lineHeightPx = 24;
        $bibleTranslation = $request->cookie('bibleId', '9879dbb7cfe39e4d-01');

        return view('memorization-tool-display', [
            'segments' => $segments,
            'reference' => $this->formatReference(session('verseSelection')),
            'lineHeightPx' => $lineHeightPx,
            'bibleTranslation' => $bibleTranslation,
            'quizMode' => false,
            'quizData' => null,
        ]);
    }
    
    private function showQuiz(Request $request)
    {
        $quizData = session('dailyQuiz');
        
        if (!$quizData || empty($quizData['verses'])) {
            session()->flash('error', 'No quiz data found. Please start a new quiz.');
            return redirect()->route('daily-quiz');
        }

        $currentIndex = $quizData['currentIndex'] ?? 0;
        
        if ($currentIndex >= count($quizData['verses'])) {
            return $this->showQuizResults($quizData);
        }

        $currentMemoryBankEntry = $quizData['verses'][$currentIndex];
        
        // Convert the MemoryBank entry to individual verses for the quiz
        $verses = $this->convertMemoryBankToVerses($currentMemoryBankEntry);
        
        if (empty($verses)) {
            session()->flash('error', 'Failed to load verse content.');
            return redirect()->route('daily-quiz');
        }

        // For now, just take the first verse since memorization component handles one verse at a time
        $currentVerse = $verses[0];
        
        // Fetch the verse content
        $verseContent = $this->fetchVerseContent($currentVerse);
        
        if (!$verseContent) {
            session()->flash('error', 'Failed to load verse content.');
            return redirect()->route('daily-quiz');
        }

        $segments = $this->parseVerseSegments($verseContent);

        foreach ($segments as &$seg) {
            $seg['numLines'] = ceil(strlen($seg['text']) / 35);
        }
        unset($seg);

        $lineHeightPx = 24;
        $bibleTranslation = $request->cookie('bibleId', '9879dbb7cfe39e4d-01');
        $reference = $this->formatVerseReference($currentVerse);
        $difficulty = $quizData['difficulty'] ?? 'easy';

        return view('memorization-tool-display', [
            'segments' => $segments,
            'reference' => $reference,
            'lineHeightPx' => $lineHeightPx,
            'bibleTranslation' => $bibleTranslation,
            'quizMode' => true,
            'quizData' => $quizData,
            'currentIndex' => $currentIndex,
            'totalVerses' => count($quizData['verses']),
            'initialDifficulty' => $difficulty,
        ]);
    }
    
    private function showQuizResults($quizData)
    {
        $results = $quizData['results'] ?? [];
        $totalScore = array_sum(array_column($results, 'score'));
        $totalPossible = count($results) * 100;
        $averageScore = $totalPossible > 0 ? ($totalScore / $totalPossible) * 100 : 0;
        
        return view('quiz-results', [
            'results' => $results,
            'averageScore' => $averageScore,
            'totalAnswered' => count($results),
        ]);
    }
    
    private function convertMemoryBankToVerses($memoryBankEntry)
    {
        $verses = [];
        $verseRanges = $memoryBankEntry['verses'];
        
        if (is_string($verseRanges)) {
            $verseRanges = json_decode($verseRanges, true);
        }
        
        if (!is_array($verseRanges)) {
            return [];
        }
        
        // Handle verse ranges like [[1, 3]] to generate individual verses [1, 2, 3]
        foreach ($verseRanges as $range) {
            if (is_array($range) && count($range) == 2) {
                // Range format like [1, 3]
                [$start, $end] = $range;
                for ($verse = $start; $verse <= $end; $verse++) {
                    $verses[] = [
                        'book' => $memoryBankEntry['book'],
                        'chapter' => $memoryBankEntry['chapter'],
                        'verse' => $verse,
                    ];
                }
            } elseif (is_numeric($range)) {
                // Single verse format like 16
                $verses[] = [
                    'book' => $memoryBankEntry['book'],
                    'chapter' => $memoryBankEntry['chapter'],
                    'verse' => $range,
                ];
            }
        }
        
        return $verses;
    }
    
    private function fetchVerseContent($verse)
    {
        $reference = $this->formatVerseReference($verse);
        $apiKey = config('services.bible.api_key');
        $bibleId = request()->cookie('bibleId', '9879dbb7cfe39e4d-01');

        $response = Http::withHeaders([
            'api-key' => $apiKey,
        ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
            'reference' => $reference,
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        return $data['data'][0]['content'] ?? null;
    }
    
    private function formatVerseReference($verse)
    {
        return "{$verse['book']} {$verse['chapter']}:{$verse['verse']}";
    }
    
    private function formatReference(array $selection)
    {
        $book = $selection['book'];
        $chapter = $selection['chapter'];
        $ranges = $selection['verseRanges'];
        $parts = [];

        foreach ($ranges as $range) {
            [$start, $end] = $range;
            $parts[] = ($start === $end) ? $start : "$start-$end";
        }

        $verseStr = implode(',', $parts);
        return "$book $chapter:$verseStr";
    }
    
    private function parseVerseSegments(string $rawText): array
    {
        preg_match_all(
            '/<span\s+[^>]*class=["\']v["\'][^>]*>(\d+)<\/span>(.*?)(?=<span\s+[^>]*class=["\']v["\'][^>]*>|$)/s',
            $rawText,
            $matches,
            PREG_SET_ORDER
        );

        $segments = [];
        foreach ($matches as $match) {
            $segments[] = [
                'verse' => $match[1],
                'text' => trim(strip_tags($match[2])),
            ];
        }

        return $segments;
    }
    
    public function nextQuizVerse(Request $request)
    {
        $quizData = session('dailyQuiz');
        
        if (!$quizData) {
            return response()->json(['error' => 'No quiz session found'], 404);
        }
        
        $currentIndex = $quizData['currentIndex'] ?? 0;
        $score = $request->input('score', 0);
        $difficulty = $request->input('difficulty', 'easy');
        $userText = $request->input('user_text', '');
        
        // Save the result for this verse
        if (!isset($quizData['results'])) {
            $quizData['results'] = [];
        }
        
        $currentVerse = $quizData['verses'][$currentIndex];
        $quizData['results'][] = [
            'verse' => $currentVerse,
            'score' => $score,
            'difficulty' => $difficulty,
            'user_text' => $userText,
            'completed_at' => now()->toISOString(),
        ];
        
        // Move to next verse
        $quizData['currentIndex'] = $currentIndex + 1;
        
        session()->put('dailyQuiz', $quizData);
        
        // Check if quiz is complete
        if ($quizData['currentIndex'] >= count($quizData['verses'])) {
            return response()->json([
                'quiz_complete' => true,
                'redirect_url' => route('daily-quiz.results'),
            ]);
        }
        
        return response()->json([
            'quiz_complete' => false,
            'redirect_url' => route('daily-quiz') . '?quiz_mode=1',
        ]);
    }
    
    public function getQuizResults()
    {
        $quizData = session('dailyQuiz');
        
        if (!$quizData || !isset($quizData['results'])) {
            return redirect()->route('home')->with('error', 'No quiz results found.');
        }
        
        return $this->showQuizResults($quizData);
    }
}
