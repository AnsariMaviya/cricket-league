<?php

namespace App\Services;

class EventMLCommentaryGenerator
{
    protected $eventTemplates;
    protected $contextWeights;
    protected $shotTypes;
    protected $shotDirections;
    protected $deliveryDescriptions;

    public function __construct()
    {
        $this->initializeShotData();
        $this->initializeEventTemplates();
        $this->contextWeights = $this->loadContextWeights();
    }
    
    protected function initializeShotData()
    {
        $this->shotTypes = [
            'defensive' => ['forward defense', 'back foot defense', 'solid block', 'watchful leave'],
            'attacking' => ['cover drive', 'straight drive', 'pull shot', 'cut shot', 'hook shot', 'sweep', 'reverse sweep', 'lofted drive', 'slog sweep'],
            'running' => ['flick', 'tuck', 'nudge', 'push', 'dab', 'guide'],
        ];
        
        $this->shotDirections = [
            'covers', 'point', 'third man', 'fine leg', 'square leg', 'mid-wicket', 
            'long-on', 'long-off', 'extra cover', 'backward point', 'deep mid-wicket'
        ];
        
        $this->deliveryDescriptions = [
            'good length', 'full delivery', 'short ball', 'yorker', 'slower ball', 
            'bouncer', 'full toss', 'half volley', 'off cutter', 'leg cutter'
        ];
    }

    /**
     * Generate commentary using event-based ML hybrid approach
     */
    public function generate(array $context): string
    {
        $event = $this->classifyEvent($context);
        $templates = $this->eventTemplates[$event] ?? $this->eventTemplates['default'];
        
        // Select template based on context weights (learned patterns)
        $template = $this->selectWeightedTemplate($templates, $context);
        
        // Apply context replacements
        return $this->applyContext($template, $context);
    }

    /**
     * Classify the ball event
     */
    protected function classifyEvent(array $context): string
    {
        if ($context['is_wicket']) {
            return 'wicket_' . strtolower(str_replace(' ', '_', $context['wicket_type'] ?? 'out'));
        }
        
        if ($context['is_six']) {
            return 'six';
        }
        
        if ($context['is_four']) {
            return 'four';
        }
        
        if ($context['extra_type'] !== 'none') {
            return 'extra_' . $context['extra_type'];
        }
        
        switch ($context['runs']) {
            case 0:
                return 'dot';
            case 1:
                return 'single';
            case 2:
                return 'double';
            case 3:
                return 'triple';
            default:
                return 'default';
        }
    }

    /**
     * Select template using weighted probability
     */
    protected function selectWeightedTemplate(array $templates, array $context): string
    {
        // For now, random selection
        // TODO: Implement ML-based weight selection based on match situation
        return $templates[array_rand($templates)];
    }

    /**
     * Apply context to template
     */
    protected function applyContext(string $template, array $context): string
    {
        $direction = $this->shotDirections[array_rand($this->shotDirections)];
        $delivery = $this->deliveryDescriptions[array_rand($this->deliveryDescriptions)];
        $distance = rand(75, 110);
        $ballSpeed = rand(130, 150);
        
        $replacements = [
            '{batsman}' => $context['batsman']->name ?? 'the batsman',
            '{bowler}' => $context['bowler']->name ?? 'the bowler',
            '{runs}' => $context['runs'],
            '{wicket_type}' => $context['wicket_type'] ?? 'out',
            '{over}' => number_format($context['over_number'], 1),
            '{direction}' => $direction,
            '{delivery}' => $delivery,
            '{distance}' => $distance,
            '{ballSpeed}' => $ballSpeed,
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Initialize event-based templates
     */
    protected function initializeEventTemplates(): void
    {
        $this->eventTemplates = [
            'wicket_bowled' => [
                'BOWLED! {delivery} from {bowler}, crashes through the gate! {batsman} had no answer to that one! The off stump goes cartwheeling!',
                'TIMBER! What a peach of a delivery! {bowler} gets the breakthrough! {batsman} played all over that {delivery}. Stumps shattered!',
                'BOWLED HIM! {bowler} gets it to nip back sharply! {batsman} beaten for pace and movement. The furniture is disturbed!',
                '{bowler} delivers a {delivery}, {batsman} misses completely! BOWLED! The stumps are in disarray!',
            ],
            'wicket_caught' => [
                'OUT! Edged and taken! {batsman} goes for the {delivery}, gets a thick edge and it\'s safely pouched at slip! Excellent catch!',
                'CAUGHT! {batsman} looks to attack, doesn\'t quite get hold of it. High in the air... and TAKEN! The fielder didn\'t have to move an inch!',
                'IN THE AIR... CAUGHT! {batsman} tried to clear {direction} but the fielder was perfectly positioned! What a grab!',
                'CAUGHT BEHIND! {bowler} gets the outside edge! Beautiful {delivery}, just kissed the edge on its way through. The keeper makes no mistake!',
                'DROPPED! NO WAIT! The fielder at {direction} juggles it once, twice... and HOLDS ON! {batsman} has to go! What drama!',
            ],
            'wicket_lbw' => [
                'LBW! That looks absolutely plumb! {batsman} trapped right in front. Huge appeal from {bowler} and the finger goes up! That was hitting middle and leg for sure!',
                'OUT! {delivery} traps {batsman} on the pads! The umpire has no hesitation. REVIEW TAKEN... Ball tracking shows THREE REDS! Crashing into the stumps!',
                'LBW! {bowler} strikes! {batsman} missed the flick, struck on the pads. Going to the third umpire... RED LIGHT! That\'s OUT!',
            ],
            'wicket_run_out' => [
                'RUN OUT! What a disaster! Confusion in the middle! {batsman} is sent back but it\'s too late! DIRECT HIT at the striker\'s end! Third umpire called... {batsman} is well short! OUT by a mile!',
                'BRILLIANT FIELDING! {batsman} called for a risky single, but the fielder swoops in, picks up and throws down the stumps in one motion! Going upstairs... RED LIGHT! OUT!',
                'RUN OUT! Oh no! Terrible mix-up! {batsman} is stranded halfway down the pitch! Easy run out. SAFE or OUT? Third umpire has a look... That\'s OUT!',
            ],
            'wicket_stumped' => [
                'STUMPED! {batsman} came down the track to the {delivery}, {bowler} saw him coming and fired it wide! The keeper whips off the bails in a flash! {batsman} is miles out! Third umpire confirms - OUT!',
                'OUT! Lightning quick glovework! {batsman} was lured out by the flight, missed completely and the keeper did the rest. STUMPED! Beautifully bowled!',
            ],
            'wicket_caught_&_bowled' => [
                'CAUGHT AND BOWLED! What reflexes from {bowler}! {batsman} hit that hard back but {bowler} stuck out both hands and plucked it out of thin air! Brilliant catch!',
            ],
            'six' => [
                'SIX! MASSIVE! {batsman} gets under the {delivery} and dispatches it into the stands over {direction}! That\'s traveled {distance} meters! Ball speed: {ballSpeed} km/h! The ball boys are searching for it!',
                'INTO THE CROWD! {batsman} launches {bowler} high and handsome over {direction}! Sailed {distance} meters over the boundary! MAXIMUM!',
                'BANG! {batsman} absolutely smokes this! Short ball, swiveled pull shot and it\'s gone all the way! {distance} METERS into the {direction} stands!',
                'OUT OF THE PARK! {batsman} connects perfectly! Launches it {distance} meters over {direction}! Massive six! The crowd goes wild!',
                'MONSTER HIT! {batsman} clears {direction} with ease! {distance}m - one of the biggest sixes of the match! That disappeared!',
            ],
            'four' => [
                'FOUR! Glorious shot! {batsman} leans into the {delivery} and caresses it through {direction}! Textbook stuff! Timed to perfection!',
                'BOUNDARY! Width on offer from {bowler}, and {batsman} cashes in! Cut away crisply through {direction}! That raced away!',
                'FOUR! {batsman} finds the gap at {direction}! Beautiful placement! The fielder dived but couldn\'t get a hand to it. Superb timing!',
                'CRACKING SHOT! {batsman} rocks back and pulls to the {direction} boundary! Hit hard and in the gap!',
                'FOUR! Delicate touch! {batsman} opens the face and guides the {delivery} past the fielder at {direction}! Used the pace beautifully!',
            ],
            'dot' => [
                'Dot ball! {delivery} on a good length from {bowler}. {batsman} defends it solidly. Textbook forward defense.',
                '{batsman} shoulders arms to the {delivery} outside off stump. Left alone. Good leave, that was moving away.',
                'Watchful from {batsman}. Gets behind the line and blocks it dead. No run. Building pressure!',
                '{bowler} beats {batsman}! Beautiful {delivery}, zipped past the outside edge! Lucky not to edge that one!',
                'APPEAL for LBW but that\'s missing leg! {batsman} tried to flick, hit on the pads. Not out. Good decision!',
                'IN THE AIR... but SAFE! {batsman} miscued that completely! Chipped it towards {direction} but fell just short! Close call! Lucky escape!',
            ],
            'single' => [
                '{batsman} nudges the {delivery} towards {direction} and scampers through for a quick single! Good running!',
                'Gentle tap to {direction}. Easy single. {batsman} keeps the strike rotating.',
                '{batsman} works it off his pads to {direction}. Quick single taken. Good placement!',
                'Pushed to {direction} for a single. Sensible cricket from {batsman}.',
            ],
            'double' => [
                'TWO RUNS! {batsman} places it in the gap at {direction}! They push hard for the second and make it easily! Excellent running!',
                '{batsman} works the {delivery} into the gap. The fielder had some ground to cover. They take two comfortably!',
                'Couple of runs! {batsman} finds the gap at {direction}. Called for two straightaway. Good running between the wickets!',
            ],
            'triple' => [
                'THREE RUNS! Excellent running! {batsman} pushes it into the gap at {direction}, they take one, turn and squeeze in the third! Superb fitness!',
                'They\'re running three! {batsman} placed it perfectly. The fielder was slow. Athletic running! Pressure on the fielding side!',
            ],
            'extra_wide' => [
                'WIDE! Pressure telling on {bowler}. That was miles down the leg side!',
                'Wide ball! {bowler} loses his line. An extra run gifted away.',
            ],
            'default' => [
                '{batsman} plays the {delivery} from {bowler}, {runs} run(s) taken.',
            ],
        ];
    }

    /**
     * Load context weights (ML patterns)
     */
    protected function loadContextWeights(): array
    {
        // TODO: Load from database/cache based on historical data
        // For now, return empty array
        return [];
    }

    /**
     * Train the model with new commentary data
     * Called periodically via scheduled job
     */
    public function train(): void
    {
        // TODO: Analyze ball_by_ball data and match_commentary
        // to build better context weights
    }
}
