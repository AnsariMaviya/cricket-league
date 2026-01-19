<?php

namespace App\Services;

use App\Models\MatchCommentary;
use Illuminate\Support\Facades\Cache;

class MarkovCommentaryGenerator
{
    protected $order = 2; // Bigram model
    protected $chain;

    public function __construct()
    {
        $this->chain = $this->loadMarkovChain();
    }

    /**
     * Generate commentary using Markov chain
     */
    public function generate(array $context): string
    {
        if (empty($this->chain)) {
            $this->buildChain();
        }

        // Generate based on context
        $seed = $this->selectSeed($context);
        $generated = $this->generateFromSeed($seed, $context);
        
        return $generated ?: $this->getFallback($context);
    }

    /**
     * Select appropriate seed based on context
     */
    protected function selectSeed(array $context): string
    {
        if ($context['is_wicket']) {
            $seeds = ['WICKET', 'OUT', 'Bowled', 'Caught', 'Gone'];
        } elseif ($context['is_six']) {
            $seeds = ['SIX', 'Massive', 'Huge', 'BOOM'];
        } elseif ($context['is_four']) {
            $seeds = ['FOUR', 'Boundary', 'Beautiful'];
        } elseif ($context['runs'] == 0) {
            $seeds = ['Dot', 'Defense', 'Blocked'];
        } else {
            $seeds = ['Run', 'Single', 'Quick'];
        }
        
        return $seeds[array_rand($seeds)];
    }

    /**
     * Generate text from seed word
     */
    protected function generateFromSeed(string $seed, array $context, int $maxWords = 15): string
    {
        $words = [$seed];
        $currentWord = $seed;
        
        for ($i = 0; $i < $maxWords; $i++) {
            if (!isset($this->chain[$currentWord])) {
                break;
            }
            
            $nextWord = $this->selectNextWord($this->chain[$currentWord]);
            
            if ($nextWord === null || $nextWord === '.' || $nextWord === '!') {
                break;
            }
            
            $words[] = $nextWord;
            $currentWord = $nextWord;
        }
        
        $text = implode(' ', $words);
        
        // Add punctuation
        if (!in_array(substr($text, -1), ['.', '!', '?'])) {
            $text .= ($context['is_wicket'] ?? false) || ($context['is_six'] ?? false) ? '!' : '.';
        }
        
        return $text;
    }

    /**
     * Select next word using weighted probability
     */
    protected function selectNextWord(array $options): ?string
    {
        if (empty($options)) {
            return null;
        }
        
        $totalWeight = array_sum($options);
        $random = rand(0, $totalWeight - 1);
        
        $current = 0;
        foreach ($options as $word => $weight) {
            $current += $weight;
            if ($random < $current) {
                return $word;
            }
        }
        
        return array_key_first($options);
    }

    /**
     * Build Markov chain from existing commentary
     */
    public function buildChain(): void
    {
        $chain = [];
        
        // Get existing commentary from database
        $commentaries = MatchCommentary::select('commentary_text')
            ->whereNotNull('commentary_text')
            ->get()
            ->pluck('commentary_text');
        
        foreach ($commentaries as $text) {
            $words = explode(' ', $text);
            
            for ($i = 0; $i < count($words) - 1; $i++) {
                $currentWord = $words[$i];
                $nextWord = $words[$i + 1];
                
                if (!isset($chain[$currentWord])) {
                    $chain[$currentWord] = [];
                }
                
                if (!isset($chain[$currentWord][$nextWord])) {
                    $chain[$currentWord][$nextWord] = 0;
                }
                
                $chain[$currentWord][$nextWord]++;
            }
        }
        
        $this->chain = $chain;
        $this->cacheMarkovChain($chain);
    }

    /**
     * Load Markov chain from cache
     */
    protected function loadMarkovChain(): array
    {
        return Cache::get('markov_commentary_chain', []);
    }

    /**
     * Cache Markov chain
     */
    protected function cacheMarkovChain(array $chain): void
    {
        Cache::put('markov_commentary_chain', $chain, now()->addDays(7));
    }

    /**
     * Get fallback commentary with detailed templates
     */
    protected function getFallback(array $context): string
    {
        $batsman = $context['batsman']->name ?? 'the batsman';
        $bowler = $context['bowler']->name ?? 'the bowler';
        $distance = rand(75, 110);
        $ballSpeed = rand(130, 150);
        
        $directions = ['covers', 'point', 'third man', 'fine leg', 'square leg', 'mid-wicket', 'long-on', 'long-off'];
        $deliveries = ['good length', 'full delivery', 'short ball', 'yorker', 'slower ball', 'bouncer'];
        $direction = $directions[array_rand($directions)];
        $delivery = $deliveries[array_rand($deliveries)];
        
        if ($context['is_wicket']) {
            $templates = [
                "BOWLED! {$delivery} from {$bowler} crashes through! {$batsman} beaten completely! Stumps shattered!",
                "OUT! CAUGHT! {$batsman} edges the {$delivery} and it's taken! Excellent catch at {$direction}!",
                "LBW! {$batsman} trapped in front! Huge appeal and the finger goes up! That's plumb!",
                "RUN OUT! Direct hit! {$batsman} is short of the crease! Third umpire confirms - OUT!",
            ];
            return $templates[array_rand($templates)];
        }
        
        if ($context['is_six']) {
            $templates = [
                "SIX! MASSIVE! {$batsman} launches it {$distance} meters over {$direction}! Ball speed: {$ballSpeed} km/h! What a strike!",
                "INTO THE CROWD! {$batsman} smashes it {$distance}m into the stands! That's disappeared!",
                "BANG! {$batsman} clears {$direction} with ease! {$distance} meters! Huge six!",
            ];
            return $templates[array_rand($templates)];
        }
        
        if ($context['is_four']) {
            $templates = [
                "FOUR! Glorious shot! {$batsman} caresses the {$delivery} through {$direction}! Timed to perfection!",
                "BOUNDARY! {$batsman} finds the gap at {$direction}! That raced away to the fence!",
                "FOUR! Cracking shot! {$batsman} pulls the {$delivery} to the {$direction} boundary!",
            ];
            return $templates[array_rand($templates)];
        }
        
        if ($context['runs'] == 0) {
            $templates = [
                "Dot ball! {$batsman} defends the {$delivery} solidly. No run.",
                "{$bowler} beats {$batsman}! Beautiful {$delivery} past the outside edge!",
                "IN THE AIR... but SAFE! {$batsman} chips it towards {$direction} but falls short! Lucky escape!",
            ];
            return $templates[array_rand($templates)];
        }
        
        if ($context['runs'] == 1) {
            return "{$batsman} nudges the {$delivery} towards {$direction} for a quick single! Good running!";
        }
        
        if ($context['runs'] == 2) {
            return "TWO RUNS! {$batsman} places it in the gap at {$direction}! Excellent running between the wickets!";
        }
        
        return "{$batsman} plays the {$delivery}, {$context['runs']} run(s) taken.";
    }
}
