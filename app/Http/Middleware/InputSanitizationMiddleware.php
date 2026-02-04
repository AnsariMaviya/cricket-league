<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\InputSanitizationService;
use Illuminate\Validation\ValidationException;

class InputSanitizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'general')
    {
        try {
            switch ($type) {
                case 'player':
                    InputSanitizationService::validatePlayerData($request);
                    break;
                case 'team':
                    InputSanitizationService::validateTeamData($request);
                    break;
                case 'match':
                    InputSanitizationService::validateMatchData($request);
                    break;
                case 'venue':
                    InputSanitizationService::validateVenueData($request);
                    break;
                case 'country':
                    InputSanitizationService::validateCountryData($request);
                    break;
                case 'prediction':
                    InputSanitizationService::validatePredictionData($request);
                    break;
                case 'search':
                    InputSanitizationService::validateSearchParams($request);
                    break;
                case 'pagination':
                    InputSanitizationService::validatePaginationParams($request);
                    break;
                default:
                    // General sanitization
                    $sanitized = InputSanitizationService::sanitizeInput($request->all());
                    $request->merge($sanitized);
                    break;
            }
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => 'Input validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Input Processing Error',
                'message' => 'Failed to process input data',
                'details' => $e->getMessage(),
            ], 400);
        }

        return $next($request);
    }
}
