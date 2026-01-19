<?php

namespace App\Console\Commands;

use App\Services\CommentaryService;
use App\Services\GeminiCommentaryGenerator;
use App\Services\EventMLCommentaryGenerator;
use App\Services\MarkovCommentaryGenerator;
use Illuminate\Console\Command;

class TestCommentarySystem extends Command
{
    protected $signature = 'commentary:test {--tier=all : Which tier to test (gemini|eventml|markov|all)}';
    protected $description = 'Test the 3-tier AI commentary system';

    public function handle()
    {
        $this->info('🎯 Testing 3-Tier AI Commentary System');
        $this->newLine();

        $tier = $this->option('tier');

        // Test context
        $context = [
            'ball' => (object)[
                'runs_scored' => 6,
                'is_wicket' => false,
                'is_six' => true,
                'is_four' => false,
                'extra_type' => 'none',
                'over_number' => 13.3,
            ],
            'batsman' => (object)['name' => 'Virat Kohli'],
            'bowler' => (object)['name' => 'Pat Cummins'],
            'runs' => 6,
            'is_wicket' => false,
            'is_six' => true,
            'is_four' => false,
            'extra_type' => 'none',
            'over_number' => 13.3,
        ];

        if ($tier === 'all' || $tier === 'gemini') {
            $this->testGemini($context);
        }

        if ($tier === 'all' || $tier === 'eventml') {
            $this->testEventML($context);
        }

        if ($tier === 'all' || $tier === 'markov') {
            $this->testMarkov($context);
        }

        if ($tier === 'all') {
            $this->testFullSystem($context);
            $this->showQuotaStats();
        }

        $this->newLine();
        $this->info('✅ Testing complete!');
    }

    protected function testGemini(array $context)
    {
        $this->line('🟢 <fg=green>Testing Tier 1: Gemini AI</>', 'comment');
        
        try {
            $generator = app(GeminiCommentaryGenerator::class);
            $commentary = $generator->generate($context);
            
            if ($commentary) {
                $this->info("   Output: {$commentary}");
            } else {
                $this->warn('   No commentary returned (check logs for details)');
            }
        } catch (\Exception $e) {
            $this->error("   Error: {$e->getMessage()}");
        }
        
        $this->newLine();
    }

    protected function testEventML(array $context)
    {
        $this->line('🔵 <fg=cyan>Testing Tier 2: Event ML Hybrid</>', 'comment');
        
        try {
            $generator = app(EventMLCommentaryGenerator::class);
            $commentary = $generator->generate($context);
            $this->info("   Output: {$commentary}");
        } catch (\Exception $e) {
            $this->error("   Error: {$e->getMessage()}");
        }
        
        $this->newLine();
    }

    protected function testMarkov(array $context)
    {
        $this->line('⚪ <fg=white>Testing Tier 3: Markov Chain</>', 'comment');
        
        try {
            $generator = app(MarkovCommentaryGenerator::class);
            $commentary = $generator->generate($context);
            $this->info("   Output: {$commentary}");
        } catch (\Exception $e) {
            $this->error("   Error: {$e->getMessage()}");
        }
        
        $this->newLine();
    }

    protected function testFullSystem(array $context)
    {
        $this->line('🎯 <fg=yellow>Testing Full System (Auto-Fallback)</>', 'comment');
        
        try {
            $service = app(CommentaryService::class);
            $commentary = $service->generate(
                $context['ball'],
                $context['batsman'],
                $context['bowler']
            );
            $this->info("   Output: {$commentary}");
        } catch (\Exception $e) {
            $this->error("   Error: {$e->getMessage()}");
        }
        
        $this->newLine();
    }

    protected function showQuotaStats()
    {
        $this->line('📊 <fg=magenta>Quota Statistics</>', 'comment');
        
        try {
            $service = app(CommentaryService::class);
            $stats = $service->getQuotaStats();
            
            $this->table(
                ['Tier', 'Daily Used/Limit', 'RPM Used/Limit', 'Available'],
                [
                    [
                        'Gemini AI',
                        sprintf('%d/%d', 
                            $stats['gemini']['daily_used'] ?? 0,
                            $stats['gemini']['daily_limit'] ?? 1500
                        ),
                        sprintf('%d/%d', 
                            $stats['gemini']['rpm_used'] ?? 0,
                            $stats['gemini']['rpm_limit'] ?? 15
                        ),
                        $stats['gemini']['available'] ? '✅ Yes' : '❌ No',
                    ],
                    [
                        'Event ML',
                        'Unlimited',
                        'Unlimited',
                        '✅ Yes',
                    ],
                    [
                        'Markov',
                        'Unlimited',
                        'Unlimited',
                        '✅ Yes',
                    ],
                ]
            );
        } catch (\Exception $e) {
            $this->error("   Error: {$e->getMessage()}");
        }
    }
}

