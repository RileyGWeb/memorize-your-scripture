<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemorizationToolController extends Controller
{
    /**
     * 1) Always forget old verse data so the user sees a fresh picker
     */
    public function showPicker(Request $request)
    {
        session()->forget('verseSelection');
        session()->forget('fetchedVerseText');

        // Return a Blade view that has the <livewire:verse-picker />
        return view('memorization-tool'); 
    }

    /**
     * 2) Fetch the verse from the Scripture API, store in session
     */
    public function fetchVerse(Request $request)
    {
        $selection = session('verseSelection');
        if (!$selection) {
            return redirect()->route('memorization-tool.picker')
                             ->with('error', 'No verse selection found in session.');
        }

        // Format "John 3:16-18,22"
        $reference = $this->formatReference($selection);

        // Scripture API call
        $apiKey = config('services.bible.api_key'); 
        $bibleId = '06125adad2d5898a-01'; // for instance, KJV or another ID

        $response = Http::withHeaders([
            'api-key' => $apiKey,
        ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
            'reference' => $reference,
        ]);

        if ($response->failed()) {
            return redirect()->route('memorization-tool.picker')
                             ->with('error', 'Failed to fetch verse data.');
        }

        $data = $response->json();
        session()->put('fetchedVerseText', $data);

        // Redirect to the "display" page
        return redirect()->route('memorization-tool.display');
    }

    /**
     * 3) Display the fetched verse
     */
    public function displayVerse(Request $request)
    {
        $verseData = session('fetchedVerseText');
        if (!$verseData) {
            return redirect()->route('memorization-tool.picker');
        }
    
        $raw = strip_tags($verseData['data'][0]['content']);
    
        // parse out 3 versions now:
        $parsed = $this->parseAndStripVerseNumbers($raw);
    
        // This has 'displayFull', 'displayHidden', 'correctText'
        $displayFull   = $parsed['displayFull'];
        $displayHidden = $parsed['displayHidden'];
        $correctText   = $parsed['correctText'];
    
        // For lined paper approach
        $numLines = ceil(strlen($correctText)/35);
    
        return view('memorization-tool-display', [
            'displayFull'   => $displayFull,
            'displayHidden' => $displayHidden,
            'correctText'   => $correctText,
            'reference'     => $this->formatReference(session('verseSelection')),
            'numLines'      => $numLines,
            'lineHeightPx'  => 24,
        ]);
    }    

    /**
     * Utility method: turn ['book'=>'John','chapter'=>3,'verseRanges'=>[[16,18],[22,22]]] 
     * into "John 3:16-18,22"
     */
    protected function formatReference(array $selection)
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

    public function saveMemory(Request $request)
    {
        $validated = $request->validate([
            'book'           => 'required|string',
            'chapter'        => 'required|integer',
            'verses'         => 'required|array',
            'difficulty'     => 'required|in:easy,normal,strict',
            'accuracy_score' => 'required|numeric',
        ]);

        // Create in memory_bank
        $record = \App\Models\MemoryBank::create([
            'user_id'        => auth()->id(),
            'book'           => $validated['book'],
            'chapter'        => $validated['chapter'],
            'verses'         => json_encode($validated['verses']),
            'difficulty'     => $validated['difficulty'],
            'accuracy_score' => $validated['accuracy_score'],
            'memorized_at'   => now(), // Mark as memorized immediately
        ]);

        return response()->json([
            'message' => 'Saved to memory bank.',
            'record'  => $record,
        ]);
    }

    protected function parseAndStripVerseNumbers(string $rawText): array
    {
        $lines = preg_split('/\r?\n/', $rawText);
        
        $displayFullLines   = [];
        $displayHiddenLines = [];
        $correctLines       = [];
    
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
    
            // Check if line has leading digits
            if (preg_match('/^(\d+)(.*)$/', $trimmed, $matches)) {
                $verseNum = $matches[1]; // e.g. "16"
                $rest     = $matches[2]; // e.g. " For God so loved..."
    
                // full display
                $displayFullLines[]   = "<sup>{$verseNum}</sup>{$rest}";
                // hidden display: keep the verse num, but replace the rest with underscores
                // $displayHiddenLines[] = "<sup>{$verseNum}</sup>" . $this->underscore($rest);
                $displayHiddenLines[] = "<sup>{$verseNum}</sup>";
                // correct text (no verse num)
                $correctLines[]       = $rest;
            } else {
                // no leading digits
                $displayFullLines[]   = $line;
                $displayHiddenLines[] = $this->underscore($line); // entire line replaced
                $correctLines[]       = $line;
            }
        }
    
        $displayFull   = implode("\n", $displayFullLines);
        $displayHidden = implode("\n", $displayHiddenLines);
        $correctText   = implode("\n", $correctLines);
    
        return [
            'displayFull'   => $displayFull,   // e.g. "<sup>16</sup> For God so loved..."
            'displayHidden' => $displayHidden, // e.g. "<sup>16</sup>________"
            'correctText'   => $correctText,   // e.g. " For God so loved..."
        ];
    }
    
    /**
     * Replaces all non-whitespace characters with underscores (or spaces).
     */
    protected function underscore(string $text): string
    {
        // Example: convert each letter/punctuation to '_', keep spaces/newlines
        // Adjust the regex to match your exact "hidden" style (maybe just letters).
        return preg_replace('/[^\s]/', '_', $text);
    }
}
