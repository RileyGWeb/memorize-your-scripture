<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RandomVerseController extends Controller
{
    /**
     * Get a random verse (either popular or truly random)
     */
    public function getRandomVerse(Request $request)
    {
        $type = $request->input('type', 'popular'); // 'popular' or 'random'
        
        try {
            if ($type === 'popular') {
                return $this->getRandomPopularVerse();
            } else {
                return $this->getTrulyRandomVerse();
            }
        } catch (\Exception $e) {
            Log::error('Failed to get random verse: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch verse. Please try again.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a random verse from the popular verses list
     */
    private function getRandomPopularVerse()
    {
        $popularVerses = config('popular_verses.popular_verses');
        $randomVerse = $popularVerses[array_rand($popularVerses)];
        
        return $this->fetchVerseText($randomVerse);
    }

    /**
     * Get a truly random verse from the Bible
     */
    private function getTrulyRandomVerse()
    {
        // Bible books with chapter counts (approximate)
        $books = [
            'Genesis' => 50, 'Exodus' => 40, 'Leviticus' => 27, 'Numbers' => 36,
            'Deuteronomy' => 34, 'Joshua' => 24, 'Judges' => 21, 'Ruth' => 4,
            '1 Samuel' => 31, '2 Samuel' => 24, '1 Kings' => 22, '2 Kings' => 25,
            '1 Chronicles' => 29, '2 Chronicles' => 36, 'Ezra' => 10, 'Nehemiah' => 13,
            'Esther' => 10, 'Job' => 42, 'Psalm' => 150, 'Proverbs' => 31,
            'Ecclesiastes' => 12, 'Song of Songs' => 8, 'Isaiah' => 66, 'Jeremiah' => 52,
            'Lamentations' => 5, 'Ezekiel' => 48, 'Daniel' => 12, 'Hosea' => 14,
            'Joel' => 3, 'Amos' => 9, 'Obadiah' => 1, 'Jonah' => 4,
            'Micah' => 7, 'Nahum' => 3, 'Habakkuk' => 3, 'Zephaniah' => 3,
            'Haggai' => 2, 'Zechariah' => 14, 'Malachi' => 4,
            'Matthew' => 28, 'Mark' => 16, 'Luke' => 24, 'John' => 21,
            'Acts' => 28, 'Romans' => 16, '1 Corinthians' => 16, '2 Corinthians' => 13,
            'Galatians' => 6, 'Ephesians' => 6, 'Philippians' => 4, 'Colossians' => 4,
            '1 Thessalonians' => 5, '2 Thessalonians' => 3, '1 Timothy' => 6, '2 Timothy' => 4,
            'Titus' => 3, 'Philemon' => 1, 'Hebrews' => 13, 'James' => 5,
            '1 Peter' => 5, '2 Peter' => 3, '1 John' => 5, '2 John' => 1,
            '3 John' => 1, 'Jude' => 1, 'Revelation' => 22
        ];

        // Select random book and chapter
        $bookNames = array_keys($books);
        $randomBook = $bookNames[array_rand($bookNames)];
        $randomChapter = rand(1, $books[$randomBook]);
        
        // Try to get verse count for the chapter, default to a reasonable range
        $verseCount = $this->getVerseCountForChapter($randomBook, $randomChapter);
        $randomVerse = rand(1, $verseCount);
        
        $reference = "{$randomBook} {$randomChapter}:{$randomVerse}";
        
        return $this->fetchVerseText($reference);
    }

    /**
     * Fetch verse text from Scripture API
     */
    private function fetchVerseText($reference)
    {
        $bibleId = session('bible_id', 'de4e12af7f28f599-02'); // Default to NIV
        
        // Parse the reference
        $parts = explode(' ', $reference, 2);
        $book = $parts[0];
        if (isset($parts[1])) {
            $chapterVerse = $parts[1];
            $cvParts = explode(':', $chapterVerse);
            $chapter = $cvParts[0];
            $verse = isset($cvParts[1]) ? $cvParts[1] : '1';
        } else {
            $chapter = '1';
            $verse = '1';
        }

        // Build the API URL
        $verseId = $this->buildVerseId($book, $chapter, $verse);
        $url = "https://api.scripture.api.bible/v1/bibles/{$bibleId}/verses/{$verseId}";
        
        $response = Http::withHeaders([
            'api-key' => config('services.bible.api_key')
        ])->get($url, [
            'content-type' => 'text',
            'include-notes' => false,
            'include-titles' => true,
            'include-chapter-numbers' => false,
            'include-verse-numbers' => true,
            'include-verse-spans' => false,
            'use-org-id' => false
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'reference' => $data['data']['reference'] ?? $reference,
                'text' => strip_tags($data['data']['content']) ?? 'Text not available',
                'book' => $book,
                'chapter' => (int)$chapter,
                'verse' => (int)$verse
            ]);
        } else {
            throw new \Exception("Failed to fetch verse from Scripture API");
        }
    }

    /**
     * Build verse ID for Scripture API
     */
    private function buildVerseId($book, $chapter, $verse)
    {
        // Convert book name to Scripture API book abbreviation
        $bookMappings = [
            'Genesis' => 'GEN',
            'Exodus' => 'EXO',
            'Leviticus' => 'LEV',
            'Numbers' => 'NUM',
            'Deuteronomy' => 'DEU',
            'Joshua' => 'JOS',
            'Judges' => 'JDG',
            'Ruth' => 'RUT',
            '1 Samuel' => '1SA',
            '2 Samuel' => '2SA',
            '1 Kings' => '1KI',
            '2 Kings' => '2KI',
            '1 Chronicles' => '1CH',
            '2 Chronicles' => '2CH',
            'Ezra' => 'EZR',
            'Nehemiah' => 'NEH',
            'Esther' => 'EST',
            'Job' => 'JOB',
            'Psalm' => 'PSA',
            'Proverbs' => 'PRO',
            'Ecclesiastes' => 'ECC',
            'Song of Songs' => 'SNG',
            'Isaiah' => 'ISA',
            'Jeremiah' => 'JER',
            'Lamentations' => 'LAM',
            'Ezekiel' => 'EZK',
            'Daniel' => 'DAN',
            'Hosea' => 'HOS',
            'Joel' => 'JOL',
            'Amos' => 'AMO',
            'Obadiah' => 'OBA',
            'Jonah' => 'JON',
            'Micah' => 'MIC',
            'Nahum' => 'NAM',
            'Habakkuk' => 'HAB',
            'Zephaniah' => 'ZEP',
            'Haggai' => 'HAG',
            'Zechariah' => 'ZEC',
            'Malachi' => 'MAL',
            'Matthew' => 'MAT',
            'Mark' => 'MRK',
            'Luke' => 'LUK',
            'John' => 'JHN',
            'Acts' => 'ACT',
            'Romans' => 'ROM',
            '1 Corinthians' => '1CO',
            '2 Corinthians' => '2CO',
            'Galatians' => 'GAL',
            'Ephesians' => 'EPH',
            'Philippians' => 'PHP',
            'Colossians' => 'COL',
            '1 Thessalonians' => '1TH',
            '2 Thessalonians' => '2TH',
            '1 Timothy' => '1TI',
            '2 Timothy' => '2TI',
            'Titus' => 'TIT',
            'Philemon' => 'PHM',
            'Hebrews' => 'HEB',
            'James' => 'JAS',
            '1 Peter' => '1PE',
            '2 Peter' => '2PE',
            '1 John' => '1JN',
            '2 John' => '2JN',
            '3 John' => '3JN',
            'Jude' => 'JUD',
            'Revelation' => 'REV'
        ];

        $bookCode = $bookMappings[$book] ?? 'PSA';
        return "{$bookCode}.{$chapter}.{$verse}";
    }

    /**
     * Get approximate verse count for a chapter (fallback method)
     */
    private function getVerseCountForChapter($book, $chapter)
    {
        // This is a simplified approach - in reality, you'd want a more accurate database
        // For now, we'll use reasonable defaults based on common patterns
        if ($book === 'Psalm') {
            return rand(10, 25); // Psalms vary widely
        } elseif (in_array($book, ['Proverbs', 'Ecclesiastes'])) {
            return rand(15, 35);
        } elseif (in_array($book, ['Genesis', 'Exodus', 'Numbers', 'Deuteronomy'])) {
            return rand(20, 45);
        } elseif (in_array($book, ['Matthew', 'Mark', 'Luke', 'John', 'Acts'])) {
            return rand(15, 40);
        } else {
            return rand(10, 30); // General fallback
        }
    }
}
