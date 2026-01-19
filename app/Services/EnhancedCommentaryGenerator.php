<?php

namespace App\Services;

use App\Models\BallByBall;
use App\Models\Player;

class EnhancedCommentaryGenerator
{
    protected $shotTypes = [
        'defensive' => ['forward defense', 'back foot defense', 'solid block', 'watchful leave'],
        'attacking' => ['cover drive', 'straight drive', 'pull shot', 'cut shot', 'hook shot', 'sweep', 'reverse sweep', 'lofted drive', 'slog sweep'],
        'running' => ['flick', 'tuck', 'nudge', 'push', 'dab', 'guide'],
    ];

    protected $shotDirections = [
        'covers', 'point', 'third man', 'fine leg', 'square leg', 'mid-wicket', 
        'long-on', 'long-off', 'extra cover', 'backward point', 'deep mid-wicket'
    ];

    protected $deliveryDescriptions = [
        'good length', 'full delivery', 'short ball', 'yorker', 'slower ball', 
        'bouncer', 'full toss', 'half volley', 'off cutter', 'leg cutter', 
        'in-swinger', 'out-swinger', 'doosra', 'googly', 'leg break', 'off break'
    ];

    protected $fieldingActions = [
        'diving stop', 'sliding effort', 'fumble', 'misfield', 'brilliant fielding', 
        'lazy effort', 'athletic dive', 'quick throw', 'relay throw'
    ];

    public function generate(BallByBall $ball, Player $batsman, Player $bowler)
    {
        $delivery = $this->deliveryDescriptions[array_rand($this->deliveryDescriptions)];
        $direction = $this->shotDirections[array_rand($this->shotDirections)];
        $fielding = $this->fieldingActions[array_rand($this->fieldingActions)];

        if ($ball->is_wicket) {
            return $this->generateWicketCommentary($ball, $batsman, $bowler, $delivery);
        } elseif ($ball->is_six) {
            return $this->generateSixCommentary($batsman, $bowler, $direction, $delivery);
        } elseif ($ball->is_four) {
            return $this->generateBoundaryCommentary($batsman, $bowler, $direction, $delivery);
        } elseif ($ball->runs_scored > 0) {
            return $this->generateRunCommentary($ball, $batsman, $bowler, $direction, $fielding, $delivery);
        } else {
            return $this->generateDotBallCommentary($batsman, $bowler, $delivery);
        }
    }

    protected function generateWicketCommentary(BallByBall $ball, Player $batsman, Player $bowler, $delivery)
    {
        $wicketType = $ball->wicket_type;
        
        $templates = [
            'bowled' => [
                "BOWLED! {$delivery} from {$bowler->name}, crashes through the gate! {$batsman->name} had no answer to that one! Departs for {$ball->runs_scored}. The off stump goes cartwheeling!",
                "TIMBER! What a peach of a delivery! {$bowler->name} gets the breakthrough! {$batsman->name} played all over that {$delivery}. Stumps shattered!",
                "BOWLED HIM! {$bowler->name} gets it to nip back sharply! {$batsman->name} beaten for pace and movement. The furniture is disturbed! Out for {$ball->runs_scored}.",
            ],
            'caught' => [
                "OUT! Edged and taken! {$batsman->name} goes for a {$delivery}, gets a thick edge and it's safely pouched at slip! Excellent catch! Departs for {$ball->runs_scored}.",
                "CAUGHT! {$batsman->name} looks to attack the {$delivery}, doesn't quite get hold of it. High in the air... and taken! The fielder didn't have to move an inch. Out for {$ball->runs_scored}!",
                "He's gone! {$batsman->name} tried to play the lofted shot over mid-off but didn't get the elevation. Straight to the fielder! Soft dismissal. Out for {$ball->runs_scored}.",
                "CAUGHT BEHIND! {$bowler->name} gets the outside edge! Beautiful {$delivery}, just kissed the edge on its way through. The keeper makes no mistake! {$batsman->name} walks back for {$ball->runs_scored}.",
            ],
            'lbw' => [
                "LBW! That looks absolutely plumb! {$batsman->name} trapped right in front. Huge appeal from {$bowler->name} and the finger goes up! That was hitting middle and leg for sure. No review taken. Out for {$ball->runs_scored}!",
                "OUT! {$delivery} traps {$batsman->name} on the pads! The umpire has no hesitation. That was dead in front. {$batsman->name} has a chat with his partner but decides against the review. Smart decision, that was hitting middle stump!",
                "LBW! {$bowler->name} strikes! {$batsman->name} missed the flick, struck on the pads. Massive appeal and UP goes the finger! Ball tracking shows three reds - would have crashed into middle stump!",
            ],
            'run out' => [
                "RUN OUT! What a disaster! Confusion in the middle! {$batsman->name} is sent back but it's too late! Direct hit at the striker's end and {$batsman->name} is well short! The third umpire has a quick look - OUT by a mile!",
                "BRILLIANT FIELDING! {$batsman->name} called for a risky single, but the fielder swoops in, picks up and throws down the stumps in one motion! {$batsman->name} is caught short! Going upstairs... RED LIGHT! OUT!",
                "RUN OUT! Oh no! Terrible mix-up between the batsmen! {$batsman->name} is stranded halfway down the pitch! Easy run out. Both batsmen were at the same end for a moment. Poor communication!",
            ],
            'stumped' => [
                "STUMPED! {$batsman->name} came down the track to the {$delivery}, {$bowler->name} saw him coming and fired it wide! The keeper whips off the bails in a flash! {$batsman->name} is miles out! Third umpire confirms - OUT!",
                "OUT! Lightning quick glovework from the keeper! {$batsman->name} was lured out of his crease by the flight, missed the ball completely and the keeper did the rest. Beautifully bowled, brilliantly kept!",
                "STUMPED! {$batsman->name} was done in by the flight and turn! Went for the big shot, beaten by the {$delivery}, and the keeper makes no mistake. Textbook stumping!",
            ],
            'caught & bowled' => [
                "CAUGHT AND BOWLED! What reflexes from {$bowler->name}! {$batsman->name} hit that hard back at the bowler but {$bowler->name} stuck out both hands and plucked it out of thin air! Brilliant catch off his own bowling!",
                "OUT! Return catch! {$batsman->name} drove that firmly back at {$bowler->name} who took a sharp catch in his follow through! That was traveling but {$bowler->name} held on. Great take!",
                "CAUGHT AND BOWLED! {$bowler->name} gets his reward! {$batsman->name} chipped it back, {$bowler->name} dived forward and completed a stunning catch! All happening here!",
            ],
        ];

        $commentaries = $templates[$wicketType] ?? [
            "OUT! {$batsman->name} has to go! {$bowler->name} gets the breakthrough! That's wicket number " . ($ball->is_wicket ? "X" : "") . "! Out for {$ball->runs_scored}."
        ];

        return $commentaries[array_rand($commentaries)];
    }

    protected function generateSixCommentary(Player $batsman, Player $bowler, $direction, $delivery)
    {
        $templates = [
            "SIX! MASSIVE! {$batsman->name} gets under the {$delivery} and dispatches it into the stands over {$direction}! That's gone out of the ground! What a strike! The ball boys are searching for the ball in the crowd!",
            "INTO THE CROWD! {$batsman->name} dances down the track and lofts {$bowler->name} high and handsome over {$direction}! Sailed over the boundary! Maximum! That went flat and fast!",
            "BANG! {$batsman->name} absolutely smokes this! Short ball, swiveled pull shot and it's gone all the way! Deposited into the {$direction} stands! Clean strike! 95 meters!",
            "OUT OF THE PARK! {$batsman->name} connects perfectly with this one! In the slot from {$bowler->name}, and {$batsman->name} launches it miles over {$direction}! Massive six! The crowd goes wild!",
            "DEMOLISHED! {$batsman->name} gets to the pitch of the {$delivery} and sends it sailing over {$direction} for a HUGE six! That's disappeared! Bowler under pressure now!",
            "BOOM! {$batsman->name} picks the bones out of that one! Right in the arc, and he's smashed it over {$direction}! Clean as a whistle! That'll hurt the bowler's figures!",
        ];

        return $templates[array_rand($templates)];
    }

    protected function generateBoundaryCommentary(Player $batsman, Player $bowler, $direction, $delivery)
    {
        $templates = [
            "FOUR! Glorious shot! {$batsman->name} leans into the {$delivery} and caresses it through {$direction}! Textbook stuff! The fielders gave chase but had no chance. Timed to perfection!",
            "BOUNDARY! Width on offer from {$bowler->name}, and {$batsman->name} cashes in! Cut away crisply through {$direction}! That raced away to the fence! Poor delivery, punished!",
            "FOUR! {$batsman->name} finds the gap at {$direction}! Beautiful placement! The fielder dived but couldn't get a hand to it. Superb timing! That's the shot of the day so far!",
            "CRACKING SHOT! {$batsman->name} rocks back and pulls the short ball authoritatively to the {$direction} boundary! Hit hard and in the gap! No chance for the fielder!",
            "FOUR! Delicate touch from {$batsman->name}! Opens the face of the bat and guides the {$delivery} past the fielder at {$direction}! Used the pace beautifully! Runs away to the boundary!",
            "DISPATCHED! {$batsman->name} transfers weight onto the front foot and drives {$bowler->name} straight back past the bowler! Mid-off had no chance! Pure class! Straight bat and full face!",
        ];

        return $templates[array_rand($templates)];
    }

    protected function generateRunCommentary(BallByBall $ball, Player $batsman, Player $bowler, $direction, $fielding, $delivery)
    {
        $runs = $ball->runs_scored;
        
        if ($runs === 1) {
            $templates = [
                "{$batsman->name} nudges the {$delivery} towards {$direction} and scampers through for a quick single! Good running between the wickets!",
                "Gentle tap to {$direction} by {$batsman->name}. Easy single. Keeps the strike rotating.",
                "{$batsman->name} works it off his pads to {$direction}. Quick single taken. Good placement!",
                "Pushed to {$direction} for a single. {$batsman->name} gets off strike. Sensible cricket.",
                "{$batsman->name} dabs it towards {$direction} with soft hands. Quick single. The fielder was a bit deep.",
            ];
        } elseif ($runs === 2) {
            $templates = [
                "TWO RUNS! {$batsman->name} places it in the gap at {$direction}! They push hard for the second and make it easily! Good running!",
                "{$batsman->name} works the {$delivery} into the gap. The fielder had some ground to cover. They take two comfortably. Excellent placement!",
                "Two runs! {$batsman->name} tucks it off his pads. Chases the second immediately. {$fielding} from the fielder but they get back safely!",
                "Couple of runs! {$batsman->name} finds the gap at {$direction}. Called for two straightaway. Good running between the wickets!",
            ];
        } else {
            $templates = [
                "THREE RUNS! Excellent running! {$batsman->name} pushes it into the gap at {$direction}, they take one, turn around and come back for two, and then squeeze in the third! Superb fitness!",
                "They're running three! {$batsman->name} placed it perfectly in the gap. The fielder was slow to get there. Athletic running between the wickets! Pressure on the fielding side!",
            ];
        }

        return $templates[array_rand($templates)];
    }

    protected function generateDotBallCommentary(Player $batsman, Player $bowler, $delivery)
    {
        $templates = [
            "Dot ball! {$delivery} on a good length from {$bowler->name}. {$batsman->name} defends it solidly back down the pitch. Textbook forward defense.",
            "{$batsman->name} shoulders arms to the {$delivery} outside off stump. Left alone. Good leave, that was moving away.",
            "Watchful from {$batsman->name}. Gets behind the line of the {$delivery} and blocks it dead. No run there. Building pressure!",
            "{$bowler->name} beats {$batsman->name}! Beautiful {$delivery}, zipped past the outside edge! Lucky not to edge that one!",
            "Appeal for LBW but that's missing leg stump! {$batsman->name} tried to flick the {$delivery}, hit on the pads. Close call but not out. Good decision from the umpire.",
            "Dot ball. {$batsman->name} taps it gently towards cover. Looks for a single but sent back. No run there. Good call!",
            "{$bowler->name} on target with the {$delivery}. {$batsman->name} defends it watchfully to the off side. Dot ball. Maiden over building?",
            "IN THE AIR... but safe! {$batsman->name} miscued that completely! Chipped it up towards mid-off but fell just short of the fielder! Close call! That could have been OUT! Lucky escape!",
        ];

        return $templates[array_rand($templates)];
    }

    public function generateMilestone($type, $data = [])
    {
        switch ($type) {
            case 'fifty':
                $balls = $data['balls'] ?? 'X';
                $strikeRate = $data['strike_rate'] ?? 'X';
                return "FIFTY FOR {$data['player']}! What a superb knock! Raises the bat to all corners of the ground! The crowd is on its feet! That's {$balls} balls faced. Strike rate of {$strikeRate}!";
            case 'century':
                $balls = $data['balls'] ?? 'X';
                return "CENTURY! HUNDRED FOR {$data['player']}! Absolutely magnificent innings! Takes off the helmet, kisses the badge, raises the bat! Standing ovation from the entire stadium! What a player! That's {$balls} balls! PHENOMENAL!";
            case 'duck':
                return "Oh no! {$data['player']} departs without scoring! Golden duck! That's a disappointing start.";
            case 'team_50':
                $overs = $data['overs'] ?? 'X';
                return "50 UP for {$data['team']}! Good start in these conditions! The foundation is being laid here. {$overs} overs gone.";
            case 'team_100':
                $runRate = $data['run_rate'] ?? 'X';
                return "100 on the board for {$data['team']}! Solid batting display! Building a competitive total here. Run rate looking good at {$runRate}.";
            case 'team_200':
                return "200 UP! {$data['team']} are in a commanding position! Big total on the cards! The bowlers will need something special to pull this back!";
            case 'partnership_50':
                return "50-RUN PARTNERSHIP! Excellent understanding between these two! This partnership has shifted the momentum. The bowling side needs to break through here!";
            case 'partnership_100':
                $runs = $data['runs'] ?? '100';
                return "CENTURY PARTNERSHIP! What a stand! These two have taken the game away! {$runs} runs added without loss! The bowlers are under serious pressure!";
            case 'hat_trick':
                return "HAT-TRICK! UNBELIEVABLE! {$data['bowler']} HAS TAKEN A HAT-TRICK! Three wickets in three balls! The entire team is mobbing him! What a moment! This is special! The crowd is going absolutely berserk!";
            case 'five_wickets':
                return "FIVE-WICKET HAUL for {$data['bowler']}! Magnificent bowling spell! Ripped through the batting lineup! The ball is being handed to {$data['bowler']} - what a performance!";
            default:
                return "Milestone reached!";
        }
    }

    public function generateInningsBreak($innings1Score, $team1, $team2, $target)
    {
        return "END OF FIRST INNINGS! {$team1} have posted {$innings1Score} on the board. {$team2} need {$target} runs to win from X overs. This should be a thrilling chase! The players are coming out for the break. We'll be back shortly!";
    }

    public function generateOverSummary($overNumber, $runs, $wickets, $bowler = null)
    {
        $summary = "End of over {$overNumber}";
        if ($bowler) {
            $summary .= " by {$bowler}";
        }
        $summary .= ": {$runs} runs";
        
        if ($wickets > 0) {
            $summary .= ", {$wickets} wicket" . ($wickets > 1 ? 's' : '') . "!";
            if ($wickets > 1) {
                $summary .= " Double strike!";
            }
        }
        
        if ($runs === 0 && $wickets === 0) {
            $summary .= " - MAIDEN OVER! Excellent bowling!";
        } elseif ($runs > 15) {
            $summary .= " - EXPENSIVE OVER! Bowler under pressure!";
        }
        
        return $summary;
    }

    public function generateReview($decision, $outcome)
    {
        if ($decision === 'out' && $outcome === 'not_out') {
            return "REVIEW TAKEN! The batsman is confident he didn't hit it... Going upstairs to the third umpire... Replays show no bat involved! Ultraedge confirms it! DECISION OVERTURNED! NOT OUT! That's a successful review! Excellent use of DRS!";
        } elseif ($decision === 'not_out' && $outcome === 'out') {
            return "REVIEW! The bowler is convinced that's OUT! Let's go upstairs... Ball tracking coming up... Pitching in line, impact in line, and it's hitting the stumps! THREE REDS! ORIGINAL DECISION OVERTURNED! That's OUT! Brilliant review!";
        } else {
            return "Review retained. Original decision stands. {$outcome}!";
        }
    }
    
    protected function getMatchSituation($matchContext)
    {
        if (!$matchContext || !isset($matchContext['innings']) || $matchContext['innings'] != 2) {
            return '';
        }
        
        $required = $matchContext['required_runs'] ?? 0;
        $balls = $matchContext['balls_remaining'] ?? 0;
        $runRate = $matchContext['required_run_rate'] ?? 0;
        
        if ($required > 0 && $balls > 0) {
            if ($required <= 10) {
                return " {$required} needed from {$balls} balls.";
            } elseif ($required <= 50) {
                return " {$required} runs needed from {$balls} balls. Required rate: {$runRate}.";
            }
        }
        
        return '';
    }
    
    public function checkMilestone($runs, $playerName)
    {
        if ($runs == 50) {
            return "FIFTY FOR {$playerName}! What a superb knock! Raises the bat to all corners of the ground! The crowd is on its feet! Well played!";
        } elseif ($runs == 100) {
            return "CENTURY! HUNDRED FOR {$playerName}! Absolutely magnificent innings! Takes off the helmet, kisses the badge, raises the bat! Standing ovation from the entire stadium! What a player! PHENOMENAL!";
        } elseif ($runs == 150) {
            return "150 FOR {$playerName}! What an incredible innings! The player is in beast mode! This is special cricket!";
        }
        return null;
    }
    
    public function checkBowlingMilestone($wickets, $bowlerName)
    {
        if ($wickets == 3) {
            return "{$bowlerName} picks up his THIRD WICKET! On a hat-trick of wickets today! Bowling beautifully!";
        } elseif ($wickets == 5) {
            return "FIVE-WICKET HAUL for {$bowlerName}! Magnificent bowling spell! Ripped through the batting lineup! The ball is being handed to {$bowlerName} - what a performance!";
        }
        return null;
    }
}
