<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/health',
        summary: 'Health check',
        description: 'Returns the health status of the API',
        operationId: 'healthCheck',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API is healthy',
                content: new OA\JsonContent(ref: '#/components/schemas/HealthResponse')
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
