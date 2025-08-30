<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Script;
use App\Models\ScriptFeedback;
use App\Services\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScriptController extends Controller
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Generate a new script
     */
    public function generate(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:500|min:5',
            'keyPoints' => 'nullable|string|max:1000',
            'tone' => 'required|string|in:enthusiastic,comedy,educational,storytelling,professional',
            'language' => 'nullable|string|in:ar,en',
            'duration' => 'nullable|integer|min:30|max:180',
            'enhancement_level' => 'nullable|string|in:basic,intelligent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['language'] = $data['language'] ?? 'ar';
        $data['duration'] = $data['duration'] ?? 60;
        $data['enhancement_level'] = $data['enhancement_level'] ?? 'intelligent';

        try {
            // Use enhanced service for intelligent generation
            if ($data['enhancement_level'] === 'intelligent') {
                $enhancedService = new \App\Services\EnhancedOpenAIService();
                
                $result = $enhancedService->generateIntelligentScript(
                    $data['topic'],
                    $data['keyPoints'] ?? null,
                    $data['tone'],
                    $data['language'],
                    $data['duration']
                );
            } else {
                // Fallback to basic service
                $result = $this->openAIService->generateScript(
                    $data['topic'],
                    $data['keyPoints'] ?? null,
                    $data['tone'],
                    $data['language']
                );
            }

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'error_code' => $result['error_code'] ?? 'UNKNOWN_ERROR',
                ], 500);
            }

            // Save to database with enhanced metadata
            $script = Script::create([
                'topic' => $data['topic'],
                'key_points' => $data['keyPoints'],
                'tone' => $data['tone'],
                'language' => $data['language'],
                'generated_script' => $result['script'],
                'word_count' => $result['word_count'],
                'estimated_duration' => $result['estimated_duration'],
                'quality_score' => $result['quality_analysis']['overall_score'] ?? ($result['quality_score'] ?? 75),
                'engagement_score' => $result['quality_analysis']['engagement_prediction'] ?? ($result['engagement_score'] ?? 75),
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'generation_time' => $result['generation_metadata']['processing_time'] ?? ($result['generation_time'] ?? 2.0),
                'metadata' => json_encode([
                    'enhancement_level' => $data['enhancement_level'],
                    'insights_used' => $result['insights_used'] ?? null,
                    'hooks_generated' => $result['hooks_generated'] ?? null,
                    'statistics_used' => $result['statistics_used'] ?? null,
                    'quality_analysis' => $result['quality_analysis'] ?? null,
                    'generation_metadata' => $result['generation_metadata'] ?? null,
                ])
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $script->id,
                    'script' => $result['script'],
                    'word_count' => $result['word_count'],
                    'estimated_duration' => $result['estimated_duration'],
                    'quality_score' => $result['quality_analysis']['overall_score'] ?? ($result['quality_score'] ?? 75),
                    'engagement_prediction' => $result['quality_analysis']['engagement_prediction'] ?? 'medium',
                    'confidence_score' => $result['quality_analysis']['confidence_score'] ?? 0.85,
                    'generation_time' => $result['generation_metadata']['processing_time'] ?? ($result['generation_time'] ?? 2.0),
                    'enhancement_level' => $data['enhancement_level'],
                    'created_at' => $script->created_at->toISOString(),
                ],
                'meta' => [
                    'tokens_used' => $result['tokens_used'] ?? 0,
                    'tone' => $data['tone'],
                    'language' => $data['language'],
                    'insights_summary' => [
                        'hooks_generated' => count($result['hooks_generated'] ?? []),
                        'statistics_used' => count($result['statistics_used'] ?? []),
                        'stages_completed' => $result['generation_metadata']['stages_completed'] ?? 1,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Script generation failed', [
                'error' => $e->getMessage(),
                'topic' => $data['topic'],
                'tone' => $data['tone'],
                'enhancement_level' => $data['enhancement_level'],
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في إنشاء الاسكربت. يرجى المحاولة مرة أخرى.',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Get script by ID
     */
    public function show(int $id): JsonResponse
    {
        $script = Script::find($id);

        if (!$script) {
            return response()->json([
                'success' => false,
                'error' => 'الاسكربت غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $script->toApiArray(),
        ]);
    }

    /**
     * Submit feedback for a script
     */
    public function feedback(Request $request, int $id): JsonResponse
    {
        $script = Script::find($id);

        if (!$script) {
            return response()->json([
                'success' => false,
                'error' => 'الاسكربت غير موجود',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'usefulness' => 'nullable|integer|min:1|max:5',
            'clarity' => 'nullable|integer|min:1|max:5',
            'engagement' => 'nullable|integer|min:1|max:5',
            'feedback_text' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $feedback = ScriptFeedback::create([
            'script_id' => $script->id,
            'rating' => $request->rating,
            'usefulness' => $request->usefulness,
            'clarity' => $request->clarity,
            'engagement' => $request->engagement,
            'feedback_text' => $request->feedback_text,
            'user_ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'شكراً لك على تقييمك!',
            'data' => $feedback->toApiArray(),
        ]);
    }

    /**
     * Get analytics data
     */
    public function analytics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_scripts' => Script::count(),
                'popular_topics' => Script::getPopularTopics(5),
                'tone_statistics' => Script::getToneStatistics(),
                'language_statistics' => Script::getLanguageStatistics(),
                'daily_stats' => Script::getDailyStats(7),
                'average_ratings' => ScriptFeedback::getAverageRatings(),
                'quality_metrics' => [
                    'average_quality_score' => Script::avg('quality_score') ?? 0,
                    'average_engagement_score' => Script::avg('engagement_score') ?? 0,
                    'average_duration' => Script::avg('estimated_duration') ?? 0,
                ],
            ]
        ]);
    }

    /**
     * Get tone suggestions for a topic
     */
    public function suggestTone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $suggestions = $this->openAIService->suggestTone($request->topic);

        return response()->json([
            'success' => true,
            'data' => [
                'suggested_tones' => $suggestions,
                'topic' => $request->topic,
            ]
        ]);
    }
}
