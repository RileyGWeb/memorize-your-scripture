<?php

namespace App\Helpers;

class BibleHelper
{
    private static $bibleData = null;
    
    /**
     * Load and parse the Bible chapter/verse data
     */
    private static function loadBibleData()
    {
        if (self::$bibleData !== null) {
            return self::$bibleData;
        }
        
        $filePath = base_path('bible_chapter_verses_FULL.txt');
        if (!file_exists($filePath)) {
            throw new \Exception('Bible verse data file not found');
        }
        
        $content = file_get_contents($filePath);
        $lines = explode("\n", trim($content));
        
        $bibleData = [];
        $currentBook = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if this line contains a colon (chapter:verse format)
            if (strpos($line, ':') !== false) {
                if ($currentBook === null) continue;
                
                list($chapter, $maxVerse) = explode(':', $line);
                $bibleData[$currentBook][(int)$chapter] = (int)$maxVerse;
            } else {
                // This is a book name
                $currentBook = $line;
                $bibleData[$currentBook] = [];
            }
        }
        
        self::$bibleData = $bibleData;
        return $bibleData;
    }
    
    /**
     * Get all book names
     */
    public static function getAllBooks()
    {
        $data = self::loadBibleData();
        return array_keys($data);
    }
    
    /**
     * Get max chapter for a book
     */
    public static function getMaxChapter($book)
    {
        $data = self::loadBibleData();
        if (!isset($data[$book])) {
            return 0;
        }
        
        return max(array_keys($data[$book]));
    }
    
    /**
     * Get max verse for a book and chapter
     */
    public static function getMaxVerse($book, $chapter)
    {
        $data = self::loadBibleData();
        if (!isset($data[$book][$chapter])) {
            return 0;
        }
        
        return $data[$book][$chapter];
    }
    
    /**
     * Validate if a book exists
     */
    public static function isValidBook($book)
    {
        $data = self::loadBibleData();
        return isset($data[$book]);
    }
    
    /**
     * Validate if a chapter exists in a book
     */
    public static function isValidChapter($book, $chapter)
    {
        $data = self::loadBibleData();
        return isset($data[$book][$chapter]);
    }
    
    /**
     * Validate if a verse exists in a book and chapter
     */
    public static function isValidVerse($book, $chapter, $verse)
    {
        $data = self::loadBibleData();
        if (!isset($data[$book][$chapter])) {
            return false;
        }
        
        return $verse >= 1 && $verse <= $data[$book][$chapter];
    }
    
    /**
     * Validate a complete verse reference
     */
    public static function isValidReference($book, $chapter, $verse)
    {
        return self::isValidBook($book) && 
               self::isValidChapter($book, $chapter) && 
               self::isValidVerse($book, $chapter, $verse);
    }
    
    /**
     * Get a random book
     */
    public static function getRandomBook()
    {
        $books = self::getAllBooks();
        return $books[array_rand($books)];
    }
    
    /**
     * Get a random chapter for a book
     */
    public static function getRandomChapter($book)
    {
        $data = self::loadBibleData();
        if (!isset($data[$book])) {
            return 1;
        }
        
        $chapters = array_keys($data[$book]);
        return $chapters[array_rand($chapters)];
    }
    
    /**
     * Get a random verse for a book and chapter
     */
    public static function getRandomVerse($book, $chapter)
    {
        $maxVerse = self::getMaxVerse($book, $chapter);
        if ($maxVerse === 0) {
            return 1;
        }
        
        return rand(1, $maxVerse);
    }
    
    /**
     * Get a completely random verse reference
     */
    public static function getRandomVerseReference()
    {
        $book = self::getRandomBook();
        $chapter = self::getRandomChapter($book);
        $verse = self::getRandomVerse($book, $chapter);
        
        return [
            'book' => $book,
            'chapter' => $chapter,
            'verse' => $verse,
            'reference' => "{$book} {$chapter}:{$verse}"
        ];
    }
    
    /**
     * Parse a verse reference string like "Genesis 1:1" and validate it
     */
    public static function parseAndValidateReference($reference)
    {
        if (!preg_match('/^(.+?)\s+(\d+):(\d+)$/', trim($reference), $matches)) {
            return false;
        }
        
        $book = $matches[1];
        $chapter = (int)$matches[2];
        $verse = (int)$matches[3];
        
        if (self::isValidReference($book, $chapter, $verse)) {
            return [
                'book' => $book,
                'chapter' => $chapter,
                'verse' => $verse,
                'reference' => $reference
            ];
        }
        
        return false;
    }
    
    /**
     * Find a book by name (case-insensitive)
     */
    public static function findBookByName($search)
    {
        $data = self::loadBibleData();
        $search = strtolower(trim($search));
        
        foreach (array_keys($data) as $book) {
            if (strtolower($book) === $search) {
                return $book;
            }
        }
        
        return null;
    }
    
    /**
     * Find the closest book name using Levenshtein distance
     */
    public static function findClosestBook($search)
    {
        $data = self::loadBibleData();
        $search = strtolower(trim($search));
        $bestMatch = null;
        $bestDistance = PHP_INT_MAX;
        $threshold = 4; // Maximum distance to consider a match
        
        foreach (array_keys($data) as $book) {
            $distance = levenshtein($search, strtolower($book));
            if ($distance < $bestDistance && $distance <= $threshold) {
                $bestDistance = $distance;
                $bestMatch = $book;
            }
        }
        
        return $bestMatch;
    }
    
    /**
     * Get suggestions for similar book names (for typos)
     */
    public static function getSimilarBooks($search, $limit = 5)
    {
        $books = self::getAllBooks();
        $search = strtolower($search);
        
        $matches = [];
        foreach ($books as $book) {
            $bookLower = strtolower($book);
            if (strpos($bookLower, $search) !== false) {
                $matches[] = $book;
            }
        }
        
        return array_slice($matches, 0, $limit);
    }
}
