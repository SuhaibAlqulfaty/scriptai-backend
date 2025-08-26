<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * Generate a script using OpenAI based on the content creation guide
     */
    public function generateScript(string $topic, ?string $keyPoints = null, string $tone = 'educational', string $language = 'ar'): array
    {
        $startTime = microtime(true);
        
        try {
            // Build the optimized prompt based on the content guide
            $prompt = $this->buildPrompt($topic, $keyPoints, $tone, $language);
            
            // Call OpenAI API
            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model', 'gpt-4'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt($tone, $language)
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => config('services.openai.max_tokens', 2000),
                'temperature' => config('services.openai.temperature', 0.7),
            ]);

            $generatedScript = $response->choices[0]->message->content;
            $generationTime = microtime(true) - $startTime;
            
            // Calculate metrics
            $wordCount = str_word_count($generatedScript);
            $estimatedDuration = $this->calculateDuration($wordCount, $language);
            $qualityScore = $this->calculateQualityScore($generatedScript, $tone, $language);
            $engagementScore = $this->calculateEngagementScore($generatedScript, $tone);

            return [
                'success' => true,
                'script' => $generatedScript,
                'word_count' => $wordCount,
                'estimated_duration' => $estimatedDuration,
                'quality_score' => $qualityScore,
                'engagement_score' => $engagementScore,
                'generation_time' => round($generationTime, 2),
                'tokens_used' => $response->usage->totalTokens ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Script Generation Failed', [
                'topic' => $topic,
                'tone' => $tone,
                'language' => $language,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'فشل في إنشاء الاسكربت. يرجى المحاولة مرة أخرى.',
                'error_code' => 'GENERATION_FAILED',
                'generation_time' => microtime(true) - $startTime,
            ];
        }
    }

    /**
     * Build optimized prompt based on content creation guide
     */
    private function buildPrompt(string $topic, ?string $keyPoints, string $tone, string $language): string
    {
        $toneInstructions = $this->getToneInstructions($tone, $language);
        
        $prompt = "أنشئ اسكربت فيديو قصير احترافي عن: {$topic}\n\n";
        
        if ($keyPoints) {
            $prompt .= "النقاط الرئيسية المطلوب التركيز عليها:\n{$keyPoints}\n\n";
        }
        
        $prompt .= "متطلبات الاسكربت (بناءً على دليل صناعة المحتوى المتخصص):\n";
        $prompt .= "- النبرة والأسلوب: {$toneInstructions}\n";
        $prompt .= "- المدة المثلى: 30-60 ثانية (150-300 كلمة بالضبط)\n";
        $prompt .= "- ابدأ بخطاف قوي يجذب الانتباه في أول 3 ثوانٍ (5-10 كلمات فقط)\n";
        $prompt .= "- استخدم لغة واضحة ومنظمة (38% تفاعل أعلى حسب الدراسات)\n";
        $prompt .= "- أضف دعوة قوية للعمل في النهاية\n";
        $prompt .= "- اجعل المحتوى قابل للمشاركة ومثير للاهتمام\n";
        $prompt .= "- استخدم تقنيات السرد المناسبة للنبرة المختارة\n\n";
        
        $prompt .= "الهيكل المطلوب (5 أجزاء محددة):\n";
        $prompt .= "1. الخطاف (Hook) - 5-10 كلمات بالضبط، يجب أن يكون:\n";
        $prompt .= $this->getHookInstructions($tone) . "\n";
        $prompt .= "2. المقدمة السريعة - تعريف مباشر بالموضوع (20-30 كلمة)\n";
        $prompt .= "3. المحتوى الرئيسي - النقاط الأساسية مع أمثلة (100-200 كلمة)\n";
        $prompt .= "4. الخاتمة المركزة - ملخص سريع للفائدة (20-30 كلمة)\n";
        $prompt .= "5. دعوة للعمل - تفاعل محدد ومباشر (10-20 كلمة)\n\n";
        
        $prompt .= $this->getVoiceCharacteristics($tone) . "\n\n";
        
        $prompt .= "يرجى كتابة الاسكربت باللغة العربية بشكل طبيعي ومتدفق، مع مراعاة:\n";
        $prompt .= "- وضوح النطق والتنويع في السرعة والنبرة\n";
        $prompt .= "- التركيز على الكلمات المفتاحية\n";
        $prompt .= "- استخدام تعبيرات الوجه وحركات اليد المناسبة\n";
        $prompt .= "- الحفاظ على طاقة حيوية متوسطة تناسب طبيعة المحتوى\n";
        
        return $prompt;
    }

    /**
     * Get hook instructions based on tone
     */
    private function getHookInstructions(string $tone): string
    {
        return match($tone) {
            'enthusiastic' => '   - سؤال مثير أو إحصائية مفاجئة أو تصريح جريء',
            'comedy' => '   - موقف طريف أو مقارنة مضحكة أو سؤال ساخر',
            'educational' => '   - سؤال يثير الفضول أو "هل تعلم أن..." أو "ماذا لو..."',
            'storytelling' => '   - بداية قصة مشوقة أو موقف شخصي أو "كان هناك..."',
            'professional' => '   - إحصائية مهمة أو حقيقة صادمة أو سؤال استراتيجي',
            default => '   - سؤال يثير الاهتمام أو معلومة مفيدة'
        };
    }

    /**
     * Get voice characteristics based on tone
     */
    private function getVoiceCharacteristics(string $tone): string
    {
        $base = "خصائص الصوت والأداء المطلوبة:";
        
        return $base . match($tone) {
            'enthusiastic' => "\n- صوت حماسي وطاقة عالية\n- سرعة متوسطة مع تأكيد على الكلمات المهمة\n- تعبيرات وجه متفائلة ونظرات مباشرة للكاميرا\n- حركات يد تدعم الكلام",
            'comedy' => "\n- صوت مرح وطاقة لعوبة\n- تنويع في السرعة والنبرة للتأثير الكوميدي\n- تعبيرات وجه متنوعة وحركات سريعة\n- أسلوب تمثيلي خفيف",
            'educational' => "\n- صوت واضح ومنظم مثل المدرس\n- سرعة متوسطة مع وقفات استراتيجية\n- تعبيرات وجه هادئة وثقة\n- حركات يد توضيحية بسيطة",
            'storytelling' => "\n- صوت هادئ وجذاب\n- تنويع في النبرة لخلق التشويق\n- تعبيرات وجه تعكس أحداث القصة\n- حركات طبيعية تدعم السرد",
            'professional' => "\n- صوت موثوق ومهني\n- سرعة ثابتة ووضوح في النطق\n- تعبيرات وجه جدية وواثقة\n- حركات محدودة ومدروسة",
            default => "\n- صوت واضح وطبيعي\n- سرعة متوسطة ونبرة مريحة"
        };
    }

    /**
     * Get system prompt based on tone and language
     */
    private function getSystemPrompt(string $tone, string $language): string
    {
        $basePrompt = "أنت خبير في كتابة اسكربتات الفيديوهات القصيرة للمحتوى العربي. ";
        $basePrompt .= "تتخصص في إنشاء محتوى جذاب ومؤثر يناسب منصات التواصل الاجتماعي. ";
        $basePrompt .= "لديك خبرة واسعة في فهم الجمهور العربي وما يجذب انتباههم. ";
        
        $toneSpecific = match($tone) {
            'enthusiastic' => "اكتب بنبرة حماسية ومتحمسة، استخدم كلمات تحفيزية وطاقة إيجابية عالية.",
            'comedy' => "اكتب بنبرة كوميدية مرحة، استخدم الفكاهة المناسبة والتشبيهات الطريفة.",
            'educational' => "اكتب بنبرة تعليمية واضحة، ركز على تبسيط المعلومات وجعلها سهلة الفهم.",
            'storytelling' => "اكتب بنبرة قصصية شيقة، استخدم تقنيات السرد والتشويق.",
            'professional' => "اكتب بنبرة مهنية وموثوقة، استخدم لغة رسمية ومعلومات دقيقة.",
            default => "اكتب بنبرة تعليمية واضحة ومفيدة."
        };
        
        return $basePrompt . $toneSpecific;
    }

    /**
     * Get tone-specific instructions
     */
    private function getToneInstructions(string $tone, string $language): string
    {
        return match($tone) {
            'enthusiastic' => 'حماسية ومتحمسة مع طاقة إيجابية عالية',
            'comedy' => 'كوميدية مرحة مع لمسة من الفكاهة المناسبة',
            'educational' => 'تعليمية واضحة مع تبسيط المعلومات',
            'storytelling' => 'قصصية شيقة مع عناصر التشويق والسرد',
            'professional' => 'مهنية وموثوقة مع معلومات دقيقة',
            default => 'تعليمية ومفيدة'
        };
    }

    /**
     * Calculate estimated duration in seconds
     */
    private function calculateDuration(int $wordCount, string $language): int
    {
        // Arabic speaking rate: approximately 2.5-3 words per second
        // English speaking rate: approximately 2-2.5 words per second
        $wordsPerSecond = $language === 'ar' ? 2.7 : 2.3;
        
        return (int) ceil($wordCount / $wordsPerSecond);
    }

    /**
     * Calculate quality score based on content analysis
     */
    private function calculateQualityScore(string $script, string $tone, string $language): float
    {
        $score = 70; // Base score
        
        // Check for hook (strong opening)
        if (preg_match('/^.{1,50}[!?؟]/', $script)) {
            $score += 10;
        }
        
        // Check for call to action
        if (preg_match('/(شارك|اتبع|اشترك|علق|لايك|تابع)/u', $script)) {
            $score += 10;
        }
        
        // Check word count (optimal range)
        $wordCount = str_word_count($script);
        if ($wordCount >= 150 && $wordCount <= 300) {
            $score += 10;
        }
        
        // Tone-specific scoring
        switch ($tone) {
            case 'enthusiastic':
                if (preg_match('/(رائع|مذهل|ممتاز|إبداع|قوي)/u', $script)) {
                    $score += 5;
                }
                break;
            case 'educational':
                if (preg_match('/(تعلم|اكتشف|فهم|معرفة|خطوات)/u', $script)) {
                    $score += 5;
                }
                break;
        }
        
        return min(100, max(0, $score));
    }

    /**
     * Calculate engagement score based on content elements
     */
    private function calculateEngagementScore(string $script, string $tone): float
    {
        $score = 60; // Base score
        
        // Check for questions
        if (preg_match_all('/[؟?]/', $script) >= 1) {
            $score += 15;
        }
        
        // Check for emotional words
        $emotionalWords = ['مذهل', 'رائع', 'لا تصدق', 'سر', 'اكتشف', 'تخيل'];
        foreach ($emotionalWords as $word) {
            if (strpos($script, $word) !== false) {
                $score += 3;
            }
        }
        
        // Check for numbers/statistics
        if (preg_match('/\d+/', $script)) {
            $score += 10;
        }
        
        // Tone-specific engagement factors
        switch ($tone) {
            case 'comedy':
                if (preg_match('/(😂|😄|🤣|هههه|ضحك)/u', $script)) {
                    $score += 10;
                }
                break;
            case 'storytelling':
                if (preg_match('/(كان|حدث|قصة|يحكى)/u', $script)) {
                    $score += 10;
                }
                break;
        }
        
        return min(100, max(0, $score));
    }

    /**
     * Get tone suggestions based on topic
     */
    public function suggestTone(string $topic): array
    {
        $suggestions = [];
        
        // Educational topics
        if (preg_match('/(تعلم|كيف|طريقة|خطوات|دليل)/u', $topic)) {
            $suggestions[] = 'educational';
        }
        
        // Entertainment topics
        if (preg_match('/(مضحك|طريف|فكاهة|نكت)/u', $topic)) {
            $suggestions[] = 'comedy';
        }
        
        // Motivational topics
        if (preg_match('/(نجاح|تحفيز|إنجاز|هدف)/u', $topic)) {
            $suggestions[] = 'enthusiastic';
        }
        
        // Story topics
        if (preg_match('/(قصة|حكاية|تجربة|رحلة)/u', $topic)) {
            $suggestions[] = 'storytelling';
        }
        
        // Business topics
        if (preg_match('/(عمل|شركة|استثمار|مال)/u', $topic)) {
            $suggestions[] = 'professional';
        }
        
        // Default suggestion
        if (empty($suggestions)) {
            $suggestions[] = 'educational';
        }
        
        return $suggestions;
    }
}

