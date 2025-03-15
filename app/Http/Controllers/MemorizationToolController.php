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
            // If there's no verse data, go back to the picker
            return redirect()->route('memorization-tool.picker');
        }

        // Render a Blade view that shows the verse
        return view('memorization-tool-display', [
            'verseData' => $verseData,
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
}
