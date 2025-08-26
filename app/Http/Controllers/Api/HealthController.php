<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Health check endpoint
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'ScriptAI Backend',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'database' => $this->checkDatabase(),
            'openai' => $this->checkOpenAI(),
        ]);
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            return [
                'status' => 'connected',
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'disconnected',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check OpenAI configuration
     */
    private function checkOpenAI(): array
    {
        $apiKey = config('services.openai.api_key');
        
        return [
            'configured' => !empty($apiKey),
            'model' => config('services.openai.model', 'gpt-4'),
            'max_tokens' => config('services.openai.max_tokens', 2000),
        ];
    }
}
