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
        // Validate the query params from the request
        $validated = $request->validate([
            'book' => 'required|string',
            'chapter' => 'required|integer',
            'verses' => 'required|array', // e.g. ["16","17","18"] or a single verse
        ]);

        // Format a reference string like "John 3:16-18"
        $book = $validated['book'];
        $chapter = $validated['chapter'];
        $versesArray = $validated['verses']; // array of ints or strings
        // Convert it to a string "16-18" or "16,17"
        // We'll do a naive approach for continuous vs. non-continuous:
        sort($versesArray, SORT_NUMERIC);
        $first = reset($versesArray);
        $last  = end($versesArray);

        $reference = "$book $chapter";
        if (count($versesArray) === 1) {
            $reference .= ":$first";
        } else {
            // Check if it's continuous
            $continuous = true;
            for ($i = 0; $i < count($versesArray) - 1; $i++) {
                if ($versesArray[$i+1] != ($versesArray[$i] + 1)) {
                    $continuous = false;
                    break;
                }
            }
            if ($continuous) {
                $reference .= ":$first-$last";
            } else {
                $reference .= ':'.implode(',', $versesArray);
            }
        }

        // Call scripture.api.bible with your API key
        $apiKey = config('services.bible.api_key');
        $bibleId = '06125adad2d5898a-01'; // For example, KJV ID

        $response = Http::withHeaders([
            'api-key' => $apiKey,
        ])->get("https://api.scripture.api.bible/v1/bibles/{$bibleId}/passages", [
            'reference' => $reference,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to fetch verse text.',
            ], 500);
        }

        $data = $response->json(); // Something like ['data' => [...]]
        
        // Usually, $data['data'] might be an array of passages,
        // but let's just return the 'content' from the first item or combine them
        $fullText = '';
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $passage) {
                $fullText .= $passage['content'] ?? '';
            }
        }

        return response()->json([
            'reference' => $reference,
            'verse_text' => strip_tags($fullText), // or keep HTML if you want
        ]);
    }
}
