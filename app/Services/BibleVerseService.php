<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BibleVerseService
{
    /**
     * Get today's Bible verse
     * Uses multiple free APIs with fallback options
     */
    public function getDailyVerse(): array
    {
        $cacheKey = 'daily_bible_verse_' . date('Y-m-d');
        
        // Cache for 24 hours
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            // Try Discovery Bible Study API first (simple, no API key needed)
            try {
                $response = Http::timeout(5)->get('https://discoverybiblestudy.org/daily/get-api/');
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['verse']) && isset($data['reference'])) {
                        return [
                            'verse' => $data['verse'],
                            'reference' => $data['reference'],
                            'source' => 'discoverybiblestudy.org',
                            'date' => $data['date'] ?? date('Y-m-d'),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::info('Discovery Bible Study API failed: ' . $e->getMessage());
            }

            // Fallback to Bible-API.com (random verse)
            try {
                $books = ['John', 'Romans', 'Philippians', 'Psalms', 'Proverbs', 'Isaiah', 'Matthew', 'Ephesians'];
                $book = $books[array_rand($books)];
                $chapter = rand(1, 50);
                
                $response = Http::timeout(5)->get("https://bible-api.com/{$book}:{$chapter}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['verses']) && count($data['verses']) > 0) {
                        $randomVerse = $data['verses'][array_rand($data['verses'])];
                        return [
                            'verse' => $randomVerse['text'],
                            'reference' => $data['reference'] ?? "{$book} {$chapter}:{$randomVerse['verse']}",
                            'source' => 'bible-api.com',
                            'date' => date('Y-m-d'),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::info('Bible-API.com failed: ' . $e->getMessage());
            }

            // Final fallback - inspirational verse (use a random one for variety)
            $fallbackVerse = $this->getRandomVerse();
            return [
                'verse' => $fallbackVerse['verse'],
                'reference' => $fallbackVerse['reference'],
                'source' => 'fallback',
                'date' => date('Y-m-d'),
            ];
        });
    }

    /**
     * Get a random verse from a popular passage
     */
    public function getRandomVerse(): array
    {
        $verses = [
            [
                'verse' => 'Trust in the LORD with all your heart and lean not on your own understanding.',
                'reference' => 'Proverbs 3:5',
            ],
            [
                'verse' => 'I can do all this through him who gives me strength.',
                'reference' => 'Philippians 4:13',
            ],
            [
                'verse' => 'For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.',
                'reference' => 'John 3:16',
            ],
            [
                'verse' => 'The LORD is my shepherd, I lack nothing.',
                'reference' => 'Psalm 23:1',
            ],
            [
                'verse' => 'Be strong and courageous. Do not be afraid; do not be discouraged, for the LORD your God will be with you wherever you go.',
                'reference' => 'Joshua 1:9',
            ],
            [
                'verse' => 'But seek first his kingdom and his righteousness, and all these things will be given to you as well.',
                'reference' => 'Matthew 6:33',
            ],
        ];

        return $verses[array_rand($verses)];
    }

    /**
     * Get verse for a specific date (for caching)
     */
    public function getVerseForDate(string $date): array
    {
        $cacheKey = 'daily_bible_verse_' . $date;
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        return $this->getDailyVerse();
    }
}
