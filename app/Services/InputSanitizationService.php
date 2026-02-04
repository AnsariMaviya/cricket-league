<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InputSanitizationService
{
    /**
     * Sanitize and validate input data
     */
    public static function sanitizeAndValidate(Request $request, array $rules, array $customMessages = [])
    {
        // Sanitize input first
        $sanitizedData = self::sanitizeInput($request->all());
        
        // Replace request data with sanitized data
        $request->merge($sanitizedData);
        
        // Validate sanitized data
        $validator = Validator::make($sanitizedData, $rules, $customMessages);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $sanitizedData;
    }

    /**
     * Sanitize input data
     */
    public static function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            $sanitized[$key] = self::sanitizeValue($value, $key);
        }
        
        return $sanitized;
    }

    /**
     * Sanitize individual value
     */
    public static function sanitizeValue($value, string $key = '')
    {
        if (is_array($value)) {
            return array_map(function ($item) use ($key) {
                return self::sanitizeValue($item, $key);
            }, $value);
        }
        
        if (is_string($value)) {
            return self::sanitizeString($value, $key);
        }
        
        return $value;
    }

    /**
     * Sanitize string values
     */
    public static function sanitizeString(string $value, string $key = ''): string
    {
        // Remove potential XSS attacks
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Apply specific sanitization based on field type
        $value = self::applyFieldSpecificSanitization($value, $key);
        
        return $value;
    }

    /**
     * Apply field-specific sanitization
     */
    private static function applyFieldSpecificSanitization(string $value, string $key): string
    {
        // Email sanitization
        if (str_contains($key, 'email')) {
            return strtolower(filter_var($value, FILTER_SANITIZE_EMAIL));
        }
        
        // URL sanitization
        if (str_contains($key, 'url') || str_contains($key, 'website')) {
            return filter_var($value, FILTER_SANITIZE_URL);
        }
        
        // Phone number sanitization
        if (str_contains($key, 'phone') || str_contains($key, 'mobile')) {
            return preg_replace('/[^0-9+\s\-\(\)]/', '', $value);
        }
        
        // Numeric fields
        if (str_contains($key, 'number') || str_contains($key, 'count') || str_contains($key, 'amount')) {
            return preg_replace('/[^0-9.]/', '', $value);
        }
        
        // Name fields
        if (str_contains($key, 'name') || str_contains($key, 'title')) {
            // Allow only letters, spaces, hyphens, and apostrophes
            return preg_replace('/[^a-zA-Z\s\-\']/', '', $value);
        }
        
        // Description/Content fields
        if (str_contains($key, 'description') || str_contains($key, 'content') || str_contains($key, 'commentary')) {
            // Allow more characters but still sanitize
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }

    /**
     * Validate and sanitize player data
     */
    public static function validatePlayerData(Request $request): array
    {
        $rules = [
            'player_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'team_id' => 'required|integer|exists:teams,team_id',
            'role' => 'required|string|in:Batsman,Bowler,All-rounder,Wicket Keeper',
            'batting_style' => 'nullable|string|in:Right-handed,Left-handed',
            'bowling_style' => 'nullable|string|in:Right-arm fast,Left-arm fast,Right-arm medium,Left-arm medium,Right-arm spin,Left-arm spin,Off spin,Leg spin',
            'jersey_number' => 'nullable|integer|min:1|max:99',
            'is_active' => 'boolean',
            'date_of_birth' => 'nullable|date|before:today',
            'height' => 'nullable|numeric|min:100|max:250',
            'weight' => 'nullable|numeric|min:40|max:150',
        ];

        $customMessages = [
            'player_name.regex' => 'Player name can only contain letters, spaces, hyphens, and apostrophes.',
            'role.in' => 'Role must be one of: Batsman, Bowler, All-rounder, Wicket Keeper.',
            'batting_style.in' => 'Batting style must be either Right-handed or Left-handed.',
            'bowling_style.in' => 'Invalid bowling style.',
            'jersey_number.min' => 'Jersey number must be between 1 and 99.',
            'jersey_number.max' => 'Jersey number must be between 1 and 99.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'height.min' => 'Height must be between 100 and 250 cm.',
            'height.max' => 'Height must be between 100 and 250 cm.',
            'weight.min' => 'Weight must be between 40 and 150 kg.',
            'weight.max' => 'Weight must be between 40 and 150 kg.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize team data
     */
    public static function validateTeamData(Request $request): array
    {
        $rules = [
            'team_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\&\']+$/',
            'country_id' => 'required|integer|exists:countries,country_id',
            'logo' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-\_\.\/]+$/',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'home_venue' => 'nullable|string|max:255',
            'coach_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'captain_id' => 'nullable|integer|exists:players,player_id',
        ];

        $customMessages = [
            'team_name.regex' => 'Team name can only contain letters, numbers, spaces, hyphens, ampersands, and apostrophes.',
            'logo.regex' => 'Logo filename contains invalid characters.',
            'founded_year.min' => 'Founded year must be after 1800.',
            'founded_year.max' => 'Founded year cannot be in the future.',
            'coach_name.regex' => 'Coach name can only contain letters, spaces, hyphens, and apostrophes.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize match data
     */
    public static function validateMatchData(Request $request): array
    {
        $rules = [
            'venue_id' => 'required|integer|exists:venues,venue_id',
            'first_team_id' => 'required|integer|exists:teams,team_id|different:second_team_id',
            'second_team_id' => 'required|integer|exists:teams,team_id|different:first_team_id',
            'tournament_id' => 'nullable|integer|exists:tournaments,tournament_id',
            'stage_id' => 'nullable|integer|exists:tournament_stages,stage_id',
            'match_number' => 'nullable|integer|min:1',
            'match_type' => 'required|string|in:T20,ODI,Test,T10',
            'overs' => 'required|integer|min:1|max:50',
            'match_date' => 'required|date|after:today',
            'is_knockout' => 'boolean',
        ];

        $customMessages = [
            'first_team_id.different' => 'First team and second team must be different.',
            'second_team_id.different' => 'First team and second team must be different.',
            'match_type.in' => 'Match type must be one of: T20, ODI, Test, T10.',
            'overs.min' => 'Overs must be at least 1.',
            'overs.max' => 'Overs cannot exceed 50.',
            'match_date.after' => 'Match date must be in the future.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize venue data
     */
    public static function validateVenueData(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\,\.\']+$/',
            'city' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'country' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'capacity' => 'required|integer|min:1000|max:200000',
            'latitude' => 'nullable|numeric|min:-90|max:90',
            'longitude' => 'nullable|numeric|min:-180|max:180',
            'timezone' => 'nullable|string|max:50',
        ];

        $customMessages = [
            'name.regex' => 'Venue name contains invalid characters.',
            'city.regex' => 'City name can only contain letters, spaces, and hyphens.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'capacity.min' => 'Capacity must be at least 1,000.',
            'capacity.max' => 'Capacity cannot exceed 200,000.',
            'latitude.min' => 'Latitude must be between -90 and 90.',
            'latitude.max' => 'Latitude must be between -90 and 90.',
            'longitude.min' => 'Longitude must be between -180 and 180.',
            'longitude.max' => 'Longitude must be between -180 and 180.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize country data
     */
    public static function validateCountryData(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'code' => 'required|string|max:3|regex:/^[A-Z]{3}$/|unique:countries,code',
            'flag' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'language' => 'nullable|string|max:50',
        ];

        $customMessages = [
            'name.regex' => 'Country name can only contain letters, spaces, hyphens, and apostrophes.',
            'code.regex' => 'Country code must be exactly 3 uppercase letters.',
            'code.unique' => 'Country code already exists.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize user input for predictions
     */
    public static function validatePredictionData(Request $request): array
    {
        $rules = [
            'match_id' => 'required|integer|exists:matches,match_id',
            'predicted_winner' => 'required|integer|exists:teams,team_id',
            'predicted_margin' => 'nullable|integer|min:1|max:200',
            'confidence_score' => 'nullable|integer|min:1|max:100',
        ];

        $customMessages = [
            'predicted_winner.exists' => 'Selected team does not exist.',
            'predicted_margin.min' => 'Predicted margin must be at least 1.',
            'predicted_margin.max' => 'Predicted margin cannot exceed 200.',
            'confidence_score.min' => 'Confidence score must be between 1 and 100.',
            'confidence_score.max' => 'Confidence score must be between 1 and 100.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize search parameters
     */
    public static function validateSearchParams(Request $request): array
    {
        $rules = [
            'query' => 'required|string|max:255',
            'type' => 'nullable|string|in:player,team,match,venue,country',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];

        $customMessages = [
            'query.required' => 'Search query is required.',
            'query.max' => 'Search query cannot exceed 255 characters.',
            'type.in' => 'Search type must be one of: player, team, match, venue, country.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'page.min' => 'Page must be at least 1.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize pagination parameters
     */
    public static function validatePaginationParams(Request $request): array
    {
        $rules = [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string|in:asc,desc',
            'order_by' => 'nullable|string|max:255',
        ];

        $customMessages = [
            'page.min' => 'Page must be at least 1.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
            'sort.in' => 'Sort direction must be either asc or desc.',
        ];

        return self::sanitizeAndValidate($request, $rules, $customMessages);
    }

    /**
     * Validate and sanitize API key
     */
    public static function validateApiKey(string $apiKey): bool
    {
        // Check if API key format is valid
        if (!preg_match('/^[a-zA-Z0-9]{32,64}$/', $apiKey)) {
            return false;
        }
        
        // Additional validation can be added here
        return true;
    }

    /**
     * Sanitize file upload name
     */
    public static function sanitizeFileName(string $fileName): string
    {
        // Remove path information
        $fileName = basename($fileName);
        
        // Remove special characters except dots, hyphens, and underscores
        $fileName = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $fileName);
        
        // Ensure filename is not empty
        if (empty($fileName)) {
            $fileName = 'file_' . time();
        }
        
        return $fileName;
    }

    /**
     * Validate and sanitize JSON input
     */
    public static function validateJsonInput(string $json): array
    {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON format: ' . json_last_error_msg());
        }
        
        return self::sanitizeInput($data);
    }
}
