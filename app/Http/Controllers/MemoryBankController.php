<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemoryBank;
use Illuminate\Support\Facades\Http;

class MemoryBankController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve the logged-in user's memory bank items.
        // Assume your MemoryBank model has columns like 'book', 'chapter', 'verses', 'difficulty', 'memorized_at', 'verse_text'
        // If you only store references, you'd need to fetch the verse text separately or store a truncated snippet.
        $items = MemoryBank::where('user_id', auth()->id())
            ->latest('memorized_at')
            ->get();

        return view('bank', compact('items'));
    }

    public function fetchVerseText(Request $request)
    {
        // 1) Accept a bible_translation parameter (falling back to your default)
        $validated = $request->validate([
            'book'               => 'required|string',
            'chapter'            => 'required|integer',
            'verses'             => 'required|array',
            'bible_translation'  => 'nullable|string',
        ]);
    
        // 2) Pick up the translation from the request or config default
        $bibleId = $validated['bible_translation']
            ?: config('bible.default');  // e.g. set this in config/bible.php
    
        // 3) Build the reference string exactly as before
        $book        = $validated['book'];
        $chapter     = $validated['chapter'];
        $versesArray = $validated['verses'];
    
        sort($versesArray, SORT_NUMERIC);
        $first = reset($versesArray);
        $last  = end($versesArray);
    
        $reference = "$book $chapter";
        if (count($versesArray) === 1) {
            $reference .= ":$first";
        } else {
            $continuous = true;
            for ($i = 0; $i < count($versesArray) - 1; $i++) {
                if ($versesArray[$i+1] !== $versesArray[$i] + 1) {
                    $continuous = false;
                    break;
                }
            }
            $reference .= $continuous
                ? ":{$first}-{$last}"
                : ':' . implode(',', $versesArray);
        }
    
        // 4) Fetch from scripture.api.bible
        $apiKey = config('services.bible.api_key');
        $response = Http::withHeaders([
            'api-key' => $apiKey,
        ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
            'reference' => $reference,
        ]);
    
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch verse text.'], 500);
        }
    
        $data     = $response->json();
        $fullText = '';
        foreach ($data['data'] as $passage) {
            $content = $passage['content'] ?? '';
            // Convert verse number spans to superscript
            $content = preg_replace('/<span[^>]*class="v"[^>]*>(\d+)<\/span>/', '<sup>$1</sup>', $content);
            $fullText .= $content;
        }
    
        return response()->json([
            'reference'         => $reference,
            'verse_text'        => strip_tags($fullText, '<sup>'),
            'bible_translation' => $bibleId,      // so your modal can show which version
        ]);
    }    

    public function searchVerses(Request $request)
    {
        $query = $request->input('q');

        // Replace the stub logic below with your real verse search logic.
        // For example, you may query your Scripture API or search a verses table.
        // Here, we return a simple static response if a query is provided.
        if ($query) {
            // Example response: these items should have the same structure as those
            // originally passed to the memory bank (i.e. with keys like book, chapter, verses, etc.)
            $items = [
                [
                    'id' => 101,
                    'book' => 'John',
                    'chapter' => 3,
                    'verses' => [16],
                    'difficulty' => 'Easy',
                    'memorized_at' => now()->toDateTimeString(),
                    'verse_text' => 'For God so loved the world, that he gave his only Son...',
                ],
                // Add additional items as necessary...
            ];
        } else {
            $items = [];
        }

        return response()->json(['items' => $items]);
    }
}
