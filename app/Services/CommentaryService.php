<?php

namespace App\Services;

use App\Models\BallByBall;
use App\Models\Player;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CommentaryService
{
    protected $geminiGenerator;
    protected $eventMLGenerator;
    protected $markovGenerator;
    protected $fallbackGenerator; // Current EnhancedCommentaryGenerator

    public function __construct()
    {
        $this->geminiGenerator = new GeminiCommentaryGenerator();
        $this->eventMLGenerator = new EventMLCommentaryGenerator();
        $this->markovGenerator = new MarkovCommentaryGenerator();
        $this->fallbackGenerator = new EnhancedCommentaryGenerator();
    }

    /**
     * Generate commentary - Prioritizes AI-trained generators with detailed features
     * Order: Event ML Hybrid -> Markov -> Enhanced (as per training pipeline)
     */
    public function generate($ball, $batsman, $bowler): string
    {
        $context = $this->buildContext($ball, $batsman, $bowler);
        
        // Tier 1: Try Gemini AI (best quality)
        // if ($this->hasGeminiQuota()) {
        //     try {
        //         $commentary = $this->geminiGenerator->generate($context);
        //         if ($commentary) {
        //             $this->trackQuotaUsage('gemini');
        //             Log::info('Commentary generated via Gemini AI');
        //             return $commentary;
        //         }
        //     } catch (\Exception $e) {
        //         Log::warning('Gemini AI failed: ' . $e->getMessage());
        //     }
        // }
        // Primary: Event ML Hybrid (AI-trained with detailed commentary)
        try {
            $commentary = $this->eventMLGenerator->generate($context);
            if ($commentary) {
                return $commentary;
            }
        } catch (\Exception $e) {
            Log::warning('Event ML Hybrid failed: ' . $e->getMessage());
        }
        
        // Fallback 1: Markov Chain (statistical with detailed templates)
        try {
            $commentary = $this->markovGenerator->generate($context);
            if ($commentary) {
                return $commentary;
            }
        } catch (\Exception $e) {
            Log::warning('Markov generator failed: ' . $e->getMessage());
        }
        
        // Fallback 2: Enhanced generator (original detailed templates)
        try {
            $commentary = $this->fallbackGenerator->generate($ball, $batsman, $bowler);
            if ($commentary) {
                return $commentary;
            }
        } catch (\Exception $e) {
            Log::warning('Enhanced generator failed: ' . $e->getMessage());
        }
        
        // Last resort: Simple commentary
        return "No run. {$batsman->name} plays it carefully.";
    }

    /**
     * Build context array for generators
     */
    protected function buildContext($ball, $batsman, $bowler): array
    {
        return [
            'ball' => $ball,
            'batsman' => $batsman,
            'bowler' => $bowler,
            'runs' => $ball->runs_scored ?? $ball->runs ?? 0,
            'is_wicket' => $ball->is_wicket ?? false,
            'wicket_type' => $ball->wicket_type ?? null,
            'is_four' => $ball->is_four ?? false,
            'is_six' => $ball->is_six ?? false,
            'extra_type' => $ball->extra_type ?? 'none',
            'extra_runs' => $ball->extra_runs ?? 0,
            'over_number' => $ball->over_number ?? 0,
        ];
    }

    /**
     * Check if Gemini quota is available
     */
    protected function hasGeminiQuota(): bool
    {
        // Check daily limit (1500 requests per day)
        $today = now()->format('Y-m-d');
        $dailyQuotaKey = "gemini_quota_{$today}";
        $dailyUsed = Cache::get($dailyQuotaKey, 0);
        $dailyLimit = config('services.gemini.daily_limit', 1500);
        
        if ($dailyUsed >= $dailyLimit) {
            return false;
        }
        
        // Check RPM limit (15 requests per minute)
        $currentMinute = now()->format('Y-m-d-H-i');
        $rpmQuotaKey = "gemini_rpm_{$currentMinute}";
        $rpmUsed = Cache::get($rpmQuotaKey, 0);
        $rpmLimit = config('services.gemini.rpm_limit', 15);
        
        return $rpmUsed < $rpmLimit;
    }

    /**
     * Track API quota usage
     */
    protected function trackQuotaUsage(string $service): void
    {
        if ($service === 'gemini') {
            // Track daily quota
            $today = now()->format('Y-m-d');
            $dailyQuotaKey = "gemini_quota_{$today}";
            $dailyCurrent = Cache::get($dailyQuotaKey, 0);
            Cache::put($dailyQuotaKey, $dailyCurrent + 1, now()->endOfDay());
            
            // Track per-minute quota
            $currentMinute = now()->format('Y-m-d-H-i');
            $rpmQuotaKey = "gemini_rpm_{$currentMinute}";
            $rpmCurrent = Cache::get($rpmQuotaKey, 0);
            Cache::put($rpmQuotaKey, $rpmCurrent + 1, now()->addMinute());
        }
    }

    /**
     * Get quota statistics
     */
    public function getQuotaStats(): array
    {
        $today = now()->format('Y-m-d');
        $currentMinute = now()->format('Y-m-d-H-i');
        
        return [
            'gemini' => [
                'daily_used' => Cache::get("gemini_quota_{$today}", 0),
                'daily_limit' => config('services.gemini.daily_limit', 1500),
                'rpm_used' => Cache::get("gemini_rpm_{$currentMinute}", 0),
                'rpm_limit' => config('services.gemini.rpm_limit', 15),
                'available' => $this->hasGeminiQuota(),
            ],
            'event_ml' => [
                'status' => 'unlimited',
                'available' => true,
            ],
            'markov' => [
                'status' => 'unlimited',
                'available' => true,
            ],
        ];
    }
}
