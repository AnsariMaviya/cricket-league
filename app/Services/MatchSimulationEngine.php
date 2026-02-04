<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\MatchInnings;
use App\Models\BallByBall;
use App\Models\PlayerMatchStats;
use App\Models\Player;
use App\Models\MatchCommentary;
use Illuminate\Support\Facades\Log;
use App\Models\Partnership;
use App\Models\FallOfWicket;
use App\Events\MatchUpdated;
use Illuminate\Support\Facades\DB;

class MatchSimulationEngine
{
    public $match;
    public $currentInnings;
    public $batsmen = [];
    public $bowler;
    protected $commentaryService;
    protected $liveScoreboardService;
    protected $currentPartnership;

    public function __construct()
    {
        $this->commentaryService = new CommentaryService();
        $this->liveScoreboardService = new LiveScoreboardService();
    }

    public function startMatch(CricketMatch $match)
    {
        $this->match = $match;
        
        DB::transaction(function () {
            $this->match->status = 'live';
            $this->match->started_at = now();
            $this->match->current_innings = 1;
            $this->match->current_over = 0;
            $this->match->save();

            $this->simulateToss();
            $this->initializeInnings();
            $this->selectPlayers();
        });

        event(new MatchUpdated($this->match->fresh()));
        
        return $this->match;
    }

    protected function simulateToss()
    {
        $tossWinner = rand(0, 1) === 0 ? $this->match->firstTeam : $this->match->secondTeam;
        $decision = rand(0, 1) === 0 ? 'bat' : 'bowl';
        
        $this->match->toss_winner = $tossWinner->team_name;
        $this->match->toss_decision = $decision;
        $this->match->save();

        $battingFirst = $decision === 'bat' ? $tossWinner : 
            ($tossWinner->team_id === $this->match->first_team_id ? $this->match->secondTeam : $this->match->firstTeam);
        $bowlingFirst = $battingFirst->team_id === $this->match->first_team_id ? $this->match->secondTeam : $this->match->firstTeam;

        $this->currentInnings = MatchInnings::create([
            'match_id' => $this->match->match_id,
            'batting_team_id' => $battingFirst->team_id,
            'bowling_team_id' => $bowlingFirst->team_id,
            'innings_number' => 1,
            'status' => 'in_progress',
        ]);
        
        // Add toss commentary
        $tossCommentary = $this->generateTossCommentary($tossWinner->team_name, $decision, $battingFirst->team_name, $bowlingFirst->team_name);
        \App\Models\MatchCommentary::create([
            'match_id' => $this->match->match_id,
            'over_number' => 0,
            'commentary_text' => $tossCommentary,
            'type' => 'toss',
        ]);
    }
    
    protected function generateTossCommentary($tossWinner, $decision, $battingTeam, $bowlingTeam)
    {
        $templates = [
            "🎯 TOSS: {$tossWinner} have won the toss and elected to {$decision}! {$battingTeam} will bat first, {$bowlingTeam} will bowl. The pitch looks good for batting. Should be an exciting contest!",
            "🏏 TOSS UPDATE: Captain of {$tossWinner} wins the toss and chooses to {$decision} first! {$battingTeam} openers are making their way to the middle. {$bowlingTeam} will be looking for early breakthroughs!",
            "⚡ TOSS: {$tossWinner} win the toss! Decision is to {$decision}. {$battingTeam} will set the target. The weather is perfect for cricket. Let's get this match underway!",
        ];
        return $templates[array_rand($templates)];
    }
    
    protected function startNewPartnership()
    {
        if ($this->batsmen[0] && $this->batsmen[1]) {
            $this->currentPartnership = Partnership::create([
                'match_id' => $this->match->match_id,
                'innings_number' => $this->match->current_innings,
                'batsman1_id' => $this->batsmen[0]->player_id,
                'batsman2_id' => $this->batsmen[1]->player_id,
                'wicket_number' => $this->currentInnings->wickets + 1,
                'start_over' => $this->match->current_over,
                'runs' => 0,
                'balls' => 0
            ]);
        }
    }
    
    protected function updatePartnership($runs, $balls = 1)
    {
        if ($this->currentPartnership) {
            $this->currentPartnership->runs += $runs;
            $this->currentPartnership->balls += $balls;
            $this->currentPartnership->save();
        }
    }
    
    protected function endPartnership()
    {
        if ($this->currentPartnership) {
            $this->currentPartnership->end_over = $this->match->current_over;
            $this->currentPartnership->save();
            $this->currentPartnership = null;
        }
    }

    protected function initializeInnings()
    {
        $battingTeamPlayers = Player::where('team_id', $this->currentInnings->batting_team_id)->get();
        $bowlingTeamPlayers = Player::where('team_id', $this->currentInnings->bowling_team_id)->get();

        foreach ($battingTeamPlayers as $player) {
            PlayerMatchStats::firstOrCreate(
                [
                    'match_id' => $this->match->match_id,
                    'player_id' => $player->player_id,
                ],
                [
                    'team_id' => $player->team_id,
                ]
            );
        }

        foreach ($bowlingTeamPlayers as $player) {
            PlayerMatchStats::firstOrCreate(
                [
                    'match_id' => $this->match->match_id,
                    'player_id' => $player->player_id,
                ],
                [
                    'team_id' => $player->team_id,
                ]
            );
        }
    }

    protected function selectPlayers()
    {
        // Get any players for batting (prefer batsmen, but accept any role)
        $battingPlayers = Player::where('team_id', $this->currentInnings->batting_team_id)
            ->whereIn('role', ['Batsman', 'All-rounder', 'Wicket-keeper'])
            ->take(2)
            ->get();
        
        // If not enough players with preferred roles, get any players
        if ($battingPlayers->count() < 2) {
            $battingPlayers = Player::where('team_id', $this->currentInnings->batting_team_id)
                ->take(2)
                ->get();
        }
        
        if ($battingPlayers->count() < 2) {
            throw new \Exception("Not enough players in batting team (Team ID: {$this->currentInnings->batting_team_id}). Need at least 2 players.");
        }

        $this->batsmen = [
            $battingPlayers[0] ?? null,
            $battingPlayers[1] ?? null,
        ];

        // Get a bowler (prefer bowlers/all-rounders, but accept any player)
        $this->bowler = Player::where('team_id', $this->currentInnings->bowling_team_id)
            ->whereIn('role', ['Bowler', 'All-rounder'])
            ->first();
        
        // If no bowler found, get any player
        if (!$this->bowler) {
            $this->bowler = Player::where('team_id', $this->currentInnings->bowling_team_id)
                ->first();
        }
        
        if (!$this->bowler) {
            throw new \Exception("Not enough players in bowling team (Team ID: {$this->currentInnings->bowling_team_id}). Need at least 1 player.");
        }

        $this->match->current_batsman_1 = $this->batsmen[0]->player_id;
        $this->match->current_batsman_2 = $this->batsmen[1]->player_id;
        $this->match->current_bowler = $this->bowler->player_id;
        $this->match->save();
    }

    protected function loadMatchState()
    {
        if (!$this->match) {
            return;
        }

        // Load current innings if not already loaded
        if (!$this->currentInnings) {
            $this->currentInnings = MatchInnings::where('match_id', $this->match->match_id)
                ->where('status', 'in_progress')
                ->first();
        }

        // Load batsmen if not already loaded
        if (empty($this->batsmen)) {
            if ($this->match->current_batsman_1) {
                $this->batsmen[0] = Player::find($this->match->current_batsman_1);
            }
            if ($this->match->current_batsman_2) {
                $this->batsmen[1] = Player::find($this->match->current_batsman_2);
            }
        }

        // Load bowler if not already loaded
        if (!$this->bowler && $this->match->current_bowler) {
            $this->bowler = Player::find($this->match->current_bowler);
        }
    }

    public function simulateBall()
    {
        // Ensure match state is loaded
        $this->loadMatchState();

        if (!$this->currentInnings || $this->currentInnings->status !== 'in_progress') {
            return null;
        }

        // Validate we have players
        if (empty($this->batsmen[0]) || !$this->bowler) {
            throw new \Exception('Match state invalid: missing batsmen or bowler');
        }

        $currentOver = floor($this->currentInnings->overs);
        $ballNumber = (($this->currentInnings->overs - $currentOver) * 10) + 1;

        $outcome = $this->generateBallOutcome();

        // Calculate proper over.ball format (e.g., 13.3 for 3rd ball of 13th over)
        $overNumber = $currentOver + ($ballNumber / 10);

        $ball = BallByBall::create([
            'innings_id' => $this->currentInnings->innings_id,
            'match_id' => $this->match->match_id,
            'batsman_id' => $this->batsmen[0]->player_id,
            'bowler_id' => $this->bowler->player_id,
            'over_number' => $overNumber,
            'ball_number' => $ballNumber,
            'runs_scored' => $outcome['runs'],
            'is_wicket' => $outcome['is_wicket'],
            'wicket_type' => $outcome['wicket_type'] ?? null,
            'extra_type' => $outcome['extra_type'] ?? 'none',
            'extra_runs' => $outcome['extra_runs'] ?? 0,
            'is_four' => $outcome['is_four'] ?? false,
            'is_six' => $outcome['is_six'] ?? false,
        ]);

        $commentaryText = $this->commentaryService->generate($ball, $this->batsmen[0], $this->bowler);
        $ball->commentary = $commentaryText;
        $ball->save();

        // Save to match_commentary table for live feed
        $commentaryType = 'ball';
        if ($ball->is_wicket) {
            $commentaryType = 'wicket';
        } elseif ($ball->is_six || $ball->is_four) {
            $commentaryType = 'boundary';
        }
        
        MatchCommentary::create([
            'match_id' => $this->match->match_id,
            'ball_id' => $ball->ball_id,
            'over_number' => $ball->over_number,
            'commentary_text' => $commentaryText,
            'type' => $commentaryType,
        ]);

        $this->updateStats($ball);
        $this->updateInnings($ball);

        if ($outcome['is_wicket']) {
            $this->handleWicket($ball);
        } elseif ($outcome['runs'] % 2 !== 0) {
            $this->rotateBatsmen();
        }

        if ($ballNumber >= 6 && ($outcome['extra_type'] ?? 'none') === 'none') {
            $this->completeOver();
        }

        $this->checkInningsComplete();

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget("match_scoreboard_{$this->match->match_id}");
        
        // Broadcast lightweight update with only essential data
        broadcast(new \App\Events\ScoreboardUpdated($this->match->match_id, [
            'ball' => [
                'over' => $ball->over_number,
                'runs' => $ball->runs_scored,
                'is_wicket' => $ball->is_wicket,
                'is_four' => $ball->is_four,
                'is_six' => $ball->is_six,
                'commentary' => $ball->commentary,
            ],
            'score' => [
                'runs' => $this->currentInnings->total_runs,
                'wickets' => $this->currentInnings->wickets,
                'overs' => $this->currentInnings->overs,
                'current_over' => $this->match->current_over,
            ],
            'status' => $this->match->status,
            'current_batsmen' => $this->getCurrentBatsmen(),
            'current_bowler' => $this->getCurrentBowler(),
            // Send only current player stats, not all stats
            'batting_stats' => collect($this->getCurrentBatsmen())->map(function($batsman) {
                return [
                    'stat_id' => $batsman['stat_id'],
                    'player_id' => $batsman['player_id'],
                    'runs_scored' => $batsman['runs_scored'],
                    'balls_faced' => $batsman['balls_faced'],
                    'fours' => $batsman['fours'],
                    'sixes' => $batsman['sixes'],
                    'strike_rate' => $batsman['strike_rate'],
                    'dismissal_text' => $batsman['dismissal_text']
                ];
            }),
            'bowling_stats' => [$this->getCurrentBowler()],
        ]))->toOthers();

        return $ball;
    }

    protected function generateBallOutcome()
    {
        $rand = rand(1, 100);

        if ($rand <= 5) {
            return [
                'runs' => 0,
                'is_wicket' => true,
                'wicket_type' => $this->getRandomWicketType(),
            ];
        } elseif ($rand <= 10) {
            return [
                'runs' => 6,
                'is_wicket' => false,
                'is_six' => true,
            ];
        } elseif ($rand <= 20) {
            return [
                'runs' => 4,
                'is_wicket' => false,
                'is_four' => true,
            ];
        } elseif ($rand <= 25) {
            return [
                'runs' => 0,
                'is_wicket' => false,
                'extra_type' => 'wide',
                'extra_runs' => 1,
            ];
        } elseif ($rand <= 40) {
            return ['runs' => 1, 'is_wicket' => false];
        } elseif ($rand <= 55) {
            return ['runs' => 2, 'is_wicket' => false];
        } elseif ($rand <= 65) {
            return ['runs' => 3, 'is_wicket' => false];
        } else {
            return ['runs' => 0, 'is_wicket' => false];
        }
    }

    protected function getRandomWicketType()
    {
        $types = ['bowled', 'caught', 'lbw', 'run out', 'stumped', 'caught & bowled'];
        return $types[array_rand($types)];
    }

    protected function updateStats(BallByBall $ball)
    {
        $batsmanStats = PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('player_id', $ball->batsman_id)
            ->first();

        if ($batsmanStats) {
            $batsmanStats->runs_scored += $ball->runs_scored;
            $batsmanStats->balls_faced += 1;
            if ($ball->is_four) $batsmanStats->fours += 1;
            if ($ball->is_six) $batsmanStats->sixes += 1;
            $batsmanStats->calculateStrikeRate();
            $batsmanStats->save();
        }

        $bowlerStats = PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('player_id', $ball->bowler_id)
            ->first();

        if ($bowlerStats) {
            // Only count legal deliveries (no wides/no-balls)
            if ($ball->extra_type === 'none' || $ball->extra_type === 'bye' || $ball->extra_type === 'leg_bye') {
                $bowlerStats->balls_bowled += 1;
            }
            $bowlerStats->runs_conceded += $ball->runs_scored + $ball->extra_runs;
            if ($ball->is_wicket) $bowlerStats->wickets_taken += 1;
            $bowlerStats->overs_bowled = $bowlerStats->balls_bowled / 6;
            $bowlerStats->calculateEconomy();
            $bowlerStats->recalculateOvers();
            $bowlerStats->save();
        }
    }

    protected function updateInnings(BallByBall $ball)
    {
        $this->currentInnings->total_runs += $ball->runs_scored + $ball->extra_runs;
        
        // Only count legal deliveries (no wides/no-balls)
        if ($ball->extra_type === 'none' || $ball->extra_type === 'bye' || $ball->extra_type === 'leg_bye') {
            $this->currentInnings->balls_in_innings += 1;
            // Calculate overs from ball count (no floating point errors)
            $completedOvers = floor($this->currentInnings->balls_in_innings / 6);
            $ballsInCurrentOver = $this->currentInnings->balls_in_innings % 6;
            $this->currentInnings->overs = $completedOvers + ($ballsInCurrentOver / 10);
        }

        if ($ball->is_wicket) {
            $this->currentInnings->wickets += 1;
        }

        $this->currentInnings->save();

        $this->match->current_over = $this->currentInnings->overs;
        if ($this->match->current_innings === 1) {
            $this->match->first_team_score = $this->currentInnings->total_runs . '/' . $this->currentInnings->wickets;
        } else {
            $this->match->second_team_score = $this->currentInnings->total_runs . '/' . $this->currentInnings->wickets;
        }
        $this->match->save();
    }

    protected function handleWicket(BallByBall $ball)
    {
        // Generate dismissal text based on wicket type
        $dismissalText = $this->generateDismissalText($ball);
        
        // Update batsman's stats with dismissal info
        $batsmanStats = PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('player_id', $ball->batsman_id)
            ->first();
        
        if ($batsmanStats) {
            $batsmanStats->dismissal_text = $dismissalText;
            $batsmanStats->save();
        }
        
        // Find which batsman got out (striker or non-striker)
        $outBatsmanIndex = null;
        if ($this->batsmen[0] && $this->batsmen[0]->player_id == $ball->batsman_id) {
            $outBatsmanIndex = 0;
        } elseif ($this->batsmen[1] && $this->batsmen[1]->player_id == $ball->batsman_id) {
            $outBatsmanIndex = 1;
        }
        
        // Get next batsman who hasn't batted yet
        $nextBatsman = Player::where('team_id', $this->currentInnings->batting_team_id)
            ->whereNotIn('player_id', PlayerMatchStats::where('match_id', $this->match->match_id)
                ->where('team_id', $this->currentInnings->batting_team_id)
                ->where('balls_faced', '>', 0)
                ->pluck('player_id'))
            ->inRandomOrder()
            ->first();

        if ($nextBatsman && $outBatsmanIndex !== null) {
            // Replace the out batsman with new batsman
            $this->batsmen[$outBatsmanIndex] = $nextBatsman;
            
            // Update current batsmen in match
            $this->match->current_batsman_1 = $this->batsmen[0]?->player_id;
            $this->match->current_batsman_2 = $this->batsmen[1]?->player_id;
            $this->match->save();
            
            // Initialize stats for new batsman
            PlayerMatchStats::updateOrCreate(
                ['match_id' => $this->match->match_id, 'player_id' => $nextBatsman->player_id],
                [
                    'team_id' => $this->currentInnings->batting_team_id,
                    'runs_scored' => 0,
                    'balls_faced' => 0,
                    'fours' => 0,
                    'sixes' => 0,
                    'wickets_taken' => 0,
                    'overs_bowled' => 0,
                    'balls_bowled' => 0,
                    'runs_conceded' => 0,
                    'maidens' => 0,
                    'economy' => 0.00,
                    'catches' => 0,
                    'stumpings' => 0,
                    'run_outs' => 0,
                ]
            );
        }
    }
    
    protected function generateDismissalText(BallByBall $ball): string
    {
        $wicketType = $ball->wicket_type;
        $bowler = Player::find($ball->bowler_id);
        $bowlerName = $bowler ? $bowler->name : 'Unknown';
        
        switch ($wicketType) {
            case 'bowled':
                return "b {$bowlerName}";
                
            case 'caught':
                // Get a random fielder from the bowling team
                $fielder = $this->getRandomFielder();
                return "c {$fielder} b {$bowlerName}";
                
            case 'caught & bowled':
                return "c & b {$bowlerName}";
                
            case 'lbw':
                return "lbw b {$bowlerName}";
                
            case 'stumped':
                $keeper = $this->getWicketKeeper();
                return "st {$keeper} b {$bowlerName}";
                
            case 'run out':
                $fielder = $this->getRandomFielder();
                return "run out ({$fielder})";
                
            default:
                return "b {$bowlerName}";
        }
    }
    
    protected function getRandomFielder(): string
    {
        $fielders = Player::where('team_id', $this->currentInnings->bowling_team_id)
            ->where('player_id', '!=', $this->bowler->player_id)
            ->inRandomOrder()
            ->limit(1)
            ->first();
        
        return $fielders ? $fielders->name : 'Fielder';
    }
    
    protected function getWicketKeeper(): string
    {
        $keeper = Player::where('team_id', $this->currentInnings->bowling_team_id)
            ->where('role', 'LIKE', '%Wicket-keeper%')
            ->first();
        
        if (!$keeper) {
            $keeper = Player::where('team_id', $this->currentInnings->bowling_team_id)
                ->inRandomOrder()
                ->first();
        }
        
        return $keeper ? $keeper->name : 'Keeper';
    }

    protected function rotateBatsmen()
    {
        $temp = $this->batsmen[0];
        $this->batsmen[0] = $this->batsmen[1];
        $this->batsmen[1] = $temp;

        $this->match->current_batsman_1 = $this->batsmen[0]?->player_id;
        $this->match->current_batsman_2 = $this->batsmen[1]?->player_id;
        $this->match->save();
    }

    protected function completeOver()
    {
        // Generate over summary commentary
        $overNumber = floor($this->currentInnings->overs);
        $balls = \App\Models\BallByBall::where('match_id', $this->match->match_id)
            ->where('innings_id', $this->currentInnings->innings_id)
            ->whereBetween('over_number', [$overNumber, $overNumber + 0.9])
            ->get();
        
        $overRuns = $balls->sum('runs_scored');
        $overWickets = $balls->where('is_wicket', true)->count();
        
        $enhancedGenerator = new \App\Services\EnhancedCommentaryGenerator();
        $overSummary = $enhancedGenerator->generateOverSummary($overNumber, $overRuns, $overWickets, $this->bowler->name);
        
        \App\Models\MatchCommentary::create([
            'match_id' => $this->match->match_id,
            'over_number' => $overNumber,
            'commentary_text' => $overSummary,
            'type' => 'over_summary',
        ]);
        
        // MUST rotate bowler - no consecutive overs by same bowler
        // CRITICAL: Check if current bowler has reached 10-over limit BEFORE allowing next over
        $currentBowlerStats = PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('player_id', $this->bowler->player_id)
            ->first();
        
        Log::info("Bowler rotation check - Current bowler: {$this->bowler->player_id}, Overs: " . ($currentBowlerStats ? $currentBowlerStats->overs_bowled : 0));
        
        // If current bowler has reached 10 overs, MUST rotate
        if ($currentBowlerStats && $currentBowlerStats->overs_bowled >= 10) {
            Log::warning("Current bowler {$this->bowler->player_id} has reached 10 overs, forcing rotation");
            $needsNewBowler = true;
        } else {
            $needsNewBowler = true; // Still rotate to prevent consecutive overs
        }
        
        // Debug: Check all bowlers in the team
        $allBowlers = Player::where('team_id', $this->currentInnings->bowling_team_id)
            ->where(function($query) {
                $query->where('role', 'LIKE', '%Bowler%')
                      ->orWhere('role', 'LIKE', '%All-rounder%');
            })
            ->get();
        Log::info("All bowlers in team: " . $allBowlers->pluck('player_id')->implode(', '));
        
        // Get available bowlers who haven't reached their limit (10 overs for ODI)
        $availableBowlers = Player::where('team_id', $this->currentInnings->bowling_team_id)
            ->where('player_id', '!=', $this->bowler->player_id) // Exclude current bowler
            ->where(function($query) {
                $query->where('role', 'LIKE', '%Bowler%')
                      ->orWhere('role', 'LIKE', '%All-rounder%');
            })
            ->where(function($query) {
                $query->whereHas('matchStats', function($q) {
                    $q->where('match_id', $this->match->match_id)
                      ->where('overs_bowled', '<', 10); // Haven't reached ODI limit
                })->orWhereDoesntHave('matchStats'); // Include bowlers with no stats yet
            })
            ->inRandomOrder()
            ->first();

        // Debug: Log available bowlers
        Log::info("Available bowlers found: " . ($availableBowlers ? $availableBowlers->player_id : 'None'));
        
        // Debug: Check why no bowlers were found
        if (!$availableBowlers) {
            $allOtherBowlers = Player::where('team_id', $this->currentInnings->bowling_team_id)
                ->where('player_id', '!=', $this->bowler->player_id)
                ->where(function($query) {
                    $query->where('role', 'LIKE', '%Bowler%')
                          ->orWhere('role', 'LIKE', '%All-rounder%');
                })
                ->get();
            
            Log::info("Other bowlers (excluding current): " . $allOtherBowlers->pluck('player_id')->implode(', '));
            
            foreach ($allOtherBowlers as $bowler) {
                $stats = PlayerMatchStats::where('match_id', $this->match->match_id)
                    ->where('player_id', $bowler->player_id)
                    ->first();
                $overs = $stats ? $stats->overs_bowled : 0;
                Log::info("Bowler {$bowler->player_id} has {$overs} overs bowled");
            }
        }

        // If no available bowlers found (all reached limit), check if we can continue with current bowler
        if (!$availableBowlers) {
            if ($currentBowlerStats && $currentBowlerStats->overs_bowled >= 10) {
                // Current bowler also reached limit - this is a problem!
                Log::error("All bowlers have reached 10-over limit! Match cannot continue.");
                // For now, allow current bowler to continue (this shouldn't happen in real cricket)
                $needsNewBowler = false;
            } else {
                $needsNewBowler = false;
                Log::warning("No other bowlers available, current bowler continues");
            }
        }

        if ($needsNewBowler && $availableBowlers) {
            Log::info("Rotating bowler from {$this->bowler->player_id} to {$availableBowlers->player_id}");
            $this->bowler = $availableBowlers;
            $this->match->current_bowler = $availableBowlers->player_id;
            $this->match->save();
        }

        $this->rotateBatsmen();
    }

    protected function checkInningsComplete()
    {
        $isComplete = false;

        if ($this->currentInnings->wickets >= 10) {
            $isComplete = true;
        }

        if ($this->currentInnings->overs >= $this->match->overs) {
            $isComplete = true;
        }

        if ($this->match->current_innings === 2 && $this->match->target_score) {
            if ($this->currentInnings->total_runs > $this->match->target_score) {
                $isComplete = true;
            }
        }

        if ($isComplete) {
            $this->completeInnings();
        }
    }

    protected function completeInnings()
    {
        $this->currentInnings->status = 'completed';
        $this->currentInnings->save();

        if ($this->match->current_innings === 1) {
            $this->startSecondInnings();
        } else {
            $this->completeMatch();
        }
    }

    protected function startSecondInnings()
    {
        // Preserve first innings score before starting second innings
        $firstInningsScore = $this->currentInnings->total_runs . '/' . $this->currentInnings->wickets;
        
        $this->match->current_innings = 2;
        $this->match->current_over = 0;
        $this->match->target_score = $this->currentInnings->total_runs + 1;
        
        // Set first team score permanently
        if ($this->currentInnings->batting_team_id === $this->match->first_team_id) {
            $this->match->first_team_score = $firstInningsScore;
        } else {
            $this->match->second_team_score = $firstInningsScore;
        }
        
        $this->match->save();

        $firstInningsBattingTeam = $this->currentInnings->batting_team_id;
        $secondInningsBattingTeam = $firstInningsBattingTeam === $this->match->first_team_id 
            ? $this->match->second_team_id 
            : $this->match->first_team_id;

        $this->currentInnings = MatchInnings::create([
            'match_id' => $this->match->match_id,
            'batting_team_id' => $secondInningsBattingTeam,
            'bowling_team_id' => $firstInningsBattingTeam,
            'innings_number' => 2,
            'status' => 'in_progress',
        ]);

        Log::info("🔄 Second innings started", [
            'first_innings_score' => $firstInningsScore,
            'target_score' => $this->match->target_score,
            'batting_team_id' => $secondInningsBattingTeam,
            'bowling_team_id' => $firstInningsBattingTeam,
            'first_team_score' => $this->match->first_team_score,
            'second_team_score' => $this->match->second_team_score,
        ]);

        $this->selectPlayers();
    }

    protected function completeMatch()
    {
        $this->match->status = 'completed';
        $this->match->ended_at = now();
        
        $this->determineWinner();
        
        $this->match->save();
        
        // Add match completion commentary
        $this->addMatchCompletionCommentary();
        
        event(new MatchUpdated($this->match));
    }

    protected function addMatchCompletionCommentary()
    {
        if ($this->match->outcome) {
            MatchCommentary::create([
                'match_id' => $this->match->match_id,
                'over_number' => $this->match->current_over,
                'commentary_text' => "MATCH OVER! " . $this->match->outcome . " What a thrilling encounter! Both teams gave it their all. Thank you for joining us!",
                'type' => 'milestone'
            ]);
        }
    }

    protected function determineWinner()
    {
        $innings1 = MatchInnings::where('match_id', $this->match->match_id)
            ->where('innings_number', 1)
            ->first();
        $innings2 = MatchInnings::where('match_id', $this->match->match_id)
            ->where('innings_number', 2)
            ->first();

        if ($innings1->total_runs > $innings2->total_runs) {
            $winner = $innings1->battingTeam;
            $margin = $innings1->total_runs - $innings2->total_runs;
            $this->match->outcome = "{$winner->team_name} won by {$margin} runs";
        } elseif ($innings2->total_runs > $innings1->total_runs) {
            $winner = $innings2->battingTeam;
            $wicketsLeft = 10 - $innings2->wickets;
            $this->match->outcome = "{$winner->team_name} won by {$wicketsLeft} wickets";
        } else {
            $this->match->outcome = "Match tied";
        }

        $this->match->save();
        event(new MatchUpdated($this->match->fresh()));
    }

    public function autoSimulate(CricketMatch $match, $delaySeconds = 3)
    {
        // Only start match if it's not already live
        if ($match->status !== 'live') {
            $this->startMatch($match);
        } else {
            // Match is already live, just load the state
            $this->match = $match;
            $this->loadMatchState();
        }

        while ($this->match->status === 'live') {
            $this->simulateBall();
            sleep($delaySeconds);
        }

        return $this->match->fresh();
    }

    protected function getCurrentBatsmen()
    {
        $batsmanIds = [$this->match->current_batsman_1];
        if ($this->match->current_batsman_2) {
            $batsmanIds[] = $this->match->current_batsman_2;
        }
        
        return PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('team_id', $this->currentInnings->batting_team_id)
            ->whereIn('player_id', $batsmanIds)
            ->with('player')
            ->get()
            ->map(function ($stat) {
                return [
                    'stat_id' => $stat->stat_id,
                    'player_id' => $stat->player_id,
                    'team_id' => $stat->team_id,
                    'runs_scored' => $stat->runs_scored,
                    'balls_faced' => $stat->balls_faced,
                    'fours' => $stat->fours,
                    'sixes' => $stat->sixes,
                    'strike_rate' => $stat->strike_rate,
                    'player' => $stat->player,
                    'dismissal_text' => $stat->dismissal_text
                ];
            });
    }

    protected function getCurrentBowler()
    {
        return PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('player_id', $this->match->current_bowler)
            ->with('player')
            ->first()
            ?->toArray();
    }

    protected function getBattingStats()
    {
        return PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('team_id', $this->currentInnings->batting_team_id)
            ->with('player')
            ->orderBy('runs_scored', 'desc')
            ->get()
            ->map(function ($stat) {
                return [
                    'stat_id' => $stat->stat_id,
                    'player_id' => $stat->player_id,
                    'team_id' => $stat->team_id,
                    'runs_scored' => $stat->runs_scored,
                    'balls_faced' => $stat->balls_faced,
                    'fours' => $stat->fours,
                    'sixes' => $stat->sixes,
                    'strike_rate' => $stat->strike_rate,
                    'player' => $stat->player,
                    'dismissal_text' => $stat->dismissal_text
                ];
            });
    }

    protected function getBowlingStats()
    {
        return PlayerMatchStats::where('match_id', $this->match->match_id)
            ->where('team_id', $this->currentInnings->bowling_team_id)
            ->with('player')
            ->orderBy('wickets_taken', 'desc')
            ->get()
            ->map(function ($stat) {
                return [
                    'stat_id' => $stat->stat_id,
                    'player_id' => $stat->player_id,
                    'team_id' => $stat->team_id,
                    'overs_bowled' => $stat->overs_bowled,
                    'balls_bowled' => $stat->balls_bowled,
                    'runs_conceded' => $stat->runs_conceded,
                    'wickets_taken' => $stat->wickets_taken,
                    'maidens' => $stat->maidens,
                    'economy' => $stat->economy,
                    'player' => $stat->player
                ];
            });
    }
}

