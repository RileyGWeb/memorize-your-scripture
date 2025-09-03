<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemorizationToolController extends Controller
{
    public function showPicker(Request $request)
    {
        session()->forget('verseSelection');
        session()->forget('fetchedVerseText');
        return view('memorization-tool');
    }

    public function fetchVerse(Request $request)
    {
        $selection = session('verseSelection');
        if (! $selection) {
            return redirect()->route('memorization-tool.picker')
                             ->with('error', 'No verse selection found in session.');
        }

        $reference = $this->formatReference($selection);
        $apiKey    = config('services.bible.api_key');
        $bibleId   = $request->cookie('bibleId', '9879dbb7cfe39e4d-01');
// dd($bibleId);
        $response = Http::withHeaders([
            'api-key' => $apiKey,
        ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
            'reference' => $reference,
        ]);

        if ($response->failed()) {
            \Log::error('Bible API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'reference' => $reference,
                'bibleId' => $bibleId,
                'hasApiKey' => !empty($apiKey)
            ]);
            
            $errorMessage = 'Failed to fetch verse data.';
            if ($response->status() === 401) {
                $errorMessage = 'Invalid Bible API key. Please check your configuration.';
            } elseif ($response->status() === 404) {
                $errorMessage = 'Verse not found. Please check your reference.';
            }
            
            return redirect()->route('memorization-tool.picker')
                             ->with('error', $errorMessage);
        }

        session()->put('fetchedVerseText', $response->json());

        return redirect()->route('memorization-tool.display');
    }

    public function displayVerse(Request $request)
    {
        $verseData = session('fetchedVerseText');
        if (! $verseData) {
            return redirect()->route('memorization-tool.picker');
        }

        $raw = $verseData['data'][0]['content'];
        $raw = preg_replace('/(\d)([A-Z])/', '$1 $2', $raw);
        $segments = $this->parseVerseSegments($raw);

        foreach ($segments as &$seg) {
            $seg['numLines'] = ceil(strlen($seg['text']) / 35);
        }
        unset($seg);

        $lineHeightPx      = 24;
        $bibleTranslation  = $request->cookie('bibleId', '9879dbb7cfe39e4d-01');

        return view('memorization-tool-display', [
            'segments'          => $segments,
            'reference'         => $this->formatReference(session('verseSelection')),
            'lineHeightPx'      => $lineHeightPx,
            'bibleTranslation'  => $bibleTranslation,
        ]);
    }

    protected function formatReference(array $selection)
    {
        $book     = $selection['book'];
        $chapter  = $selection['chapter'];
        $ranges   = $selection['verseRanges'];
        $parts    = [];

        foreach ($ranges as $range) {
            [$start, $end] = $range;
            $parts[] = ($start === $end) ? $start : "$start-$end";
        }

        $verseStr = implode(',', $parts);
        return "$book $chapter:$verseStr";
    }

    public function saveMemory(Request $request)
    {
        $validated = $request->validate([
            'book'              => 'required|string',
            'chapter'           => 'required|integer',
            'verses'            => 'required|array',
            'difficulty'        => 'required|in:easy,normal,strict',
            'accuracy_score'    => 'required|numeric',
            'bible_translation' => 'required|string',
            'user_text'         => 'required|string',
        ]);

        $existing = \App\Models\MemoryBank::where('user_id', auth()->id())
            ->where('book', $validated['book'])
            ->where('chapter', $validated['chapter'])
            ->where('verses', json_encode($validated['verses']))
            ->where('difficulty', $validated['difficulty'])
            ->where('bible_translation', $validated['bible_translation'])
            ->first();

        if ($existing) {
            $existing->accuracy_score    = $validated['accuracy_score'];
            $existing->memorized_at      = now();
            $existing->user_text         = $validated['user_text'];
            $existing->save();
            $record = $existing;
        } else {
            $record = \App\Models\MemoryBank::create([
                'user_id'           => auth()->id(),
                'book'              => $validated['book'],
                'chapter'           => $validated['chapter'],
                'verses'            => json_encode($validated['verses']),
                'difficulty'        => $validated['difficulty'],
                'accuracy_score'    => $validated['accuracy_score'],
                'bible_translation' => $validated['bible_translation'],
                'user_text'         => $validated['user_text'],
                'memorized_at'      => now(),
            ]);
        }

        return response()->json([
            'message' => 'Saved to memory bank.',
            'record'  => $record,
        ]);
    }

    protected function parseVerseSegments(string $rawText): array
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
                'text'  => trim(strip_tags($match[2])),
            ];
        }

        return $segments;
    }
}
