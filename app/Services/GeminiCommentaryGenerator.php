<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCommentaryGenerator
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        // Using gemini-2.0-flash with v1beta endpoint
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    }

    /**
     * Generate commentary using Gemini AI
     */
    public function generate(array $context): ?string
    {
        if (!$this->apiKey || empty(trim($this->apiKey))) {
            Log::warning('Gemini API key not configured or empty', ['key_length' => strlen($this->apiKey ?? '')]);
            return null;
        }

        Log::info('Gemini API call starting', [
            'key_configured' => true,
            'key_length' => strlen($this->apiKey)
        ]);

        $prompt = $this->buildPrompt($context);
        
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.9,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 100,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Gemini API success', ['has_content' => isset($data['candidates'][0]['content']['parts'][0]['text'])]);
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($data['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            Log::warning('Gemini API returned no content', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build prompt for Gemini AI
     */
    protected function buildPrompt(array $context): string
    {
        $ball = $context['ball'];
        $batsman = $context['batsman']->name ?? 'the batsman';
        $bowler = $context['bowler']->name ?? 'the bowler';
        $runs = $context['runs'];
        
        $prompt = "Generate a single-line cricket commentary (max 20 words) for this ball:\n\n";
        $prompt .= "Batsman: {$batsman}\n";
        $prompt .= "Bowler: {$bowler}\n";
        $prompt .= "Over: {$ball->over_number}\n";
        
        if ($context['is_wicket']) {
            $prompt .= "Result: WICKET - {$context['wicket_type']}\n";
            $prompt .= "Make it dramatic and exciting!";
        } elseif ($context['is_six']) {
            $prompt .= "Result: SIX runs!\n";
            $prompt .= "Show excitement and describe the shot!";
        } elseif ($context['is_four']) {
            $prompt .= "Result: FOUR runs (boundary)\n";
            $prompt .= "Show excitement and describe the shot!";
        } elseif ($runs > 0) {
            $prompt .= "Result: {$runs} run(s)\n";
            $prompt .= "Keep it brief and informative.";
        } else {
            $prompt .= "Result: Dot ball (no runs)\n";
            $prompt .= "Describe the defense or bowler's skill.";
        }
        
        $prompt .= "\n\nCommentary:";
        
        return $prompt;
    }
}
