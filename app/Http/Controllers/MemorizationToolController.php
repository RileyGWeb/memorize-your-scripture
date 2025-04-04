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
        $bibleId = '9879dbb7cfe39e4d-01'; // for instance, KJV or another ID

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
            // First, wrap all numbers with <sup> tags
            $lineFull = preg_replace_callback('/\d+/', function($matches) {
                return '<sup>' . $matches[0] . '</sup>';
            }, $line);
            
            $displayFullLines[] = $lineFull;
            // Correct text: remove all <sup> tags
            $correctLines[] = strip_tags($lineFull);
            
            // For the hidden display, we want to keep the <sup> tags intact
            // but replace every character outside them with underscores.
            // We split the line on <sup> tags:
            $parts = preg_split('/(<sup>.*?<\/sup>)/', $lineFull, -1, PREG_SPLIT_DELIM_CAPTURE);
            $hiddenParts = [];
            foreach ($parts as $part) {
                if (preg_match('/^<sup>.*?<\/sup>$/', $part)) {
                    // This part is a <sup> tag, so keep it as is.
                    $hiddenParts[] = $part;
                } else {
                    // Replace every non-whitespace character with an underscore.
                    $hiddenParts[] = preg_replace('/[^\s]/', '_', $part);
                }
            }
            $displayHiddenLines[] = implode('', $hiddenParts);
        }
        
        $displayFull   = implode("\n", $displayFullLines);
        $displayHidden = implode("\n", $displayHiddenLines);
        $correctText   = implode("\n", $correctLines);
        
        return [
            'displayFull'   => $displayFull,   // The full text with all <sup> numbers intact.
            'displayHidden' => $displayHidden, // The same text, but with non-<sup> characters replaced by underscores.
            'correctText'   => $correctText,   // The text without any verse numbers.
        ];
    }
      
    protected function parseVerseSegments(string $rawText): array
    {
        // This regex finds a sequence of digits (the verse number) followed by whitespace and then captures until the next verse number or end of string.
        preg_match_all('/(\d+)\s+(.*?)(?=(\d+\s)|$)/s', $rawText, $matches, PREG_SET_ORDER);
        $segments = [];
        foreach ($matches as $match) {
            $segments[] = [
                'verse' => $match[1],          // e.g. "8"
                'text'  => trim($match[2]),     // e.g. "Be sober and self-controlled. Be watchful. ..."
            ];
        }
        return $segments;
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
