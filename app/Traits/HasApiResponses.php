<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasApiResponses
{
    protected function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        // If data is a ResourceCollection, merge pagination metadata at root level
        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            $resourceData = $data->response()->getData(true);
            $response['data'] = $resourceData['data'];
            
            // Add pagination metadata if present
            if (isset($resourceData['meta'])) {
                $response = array_merge($response, [
                    'current_page' => $resourceData['meta']['current_page'] ?? 1,
                    'last_page' => $resourceData['meta']['last_page'] ?? 1,
                    'per_page' => $resourceData['meta']['per_page'] ?? 15,
                    'total' => $resourceData['meta']['total'] ?? 0,
                    'from' => $resourceData['meta']['from'] ?? null,
                    'to' => $resourceData['meta']['to'] ?? null,
                ]);
            }
        } else {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString()
        ], $status);
    }

    protected function validationErrorResponse($errors): JsonResponse
    {
        return $this->errorResponse('Validation failed', 422, $errors);
    }

    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse("{$resource} not found", 404);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }
}
