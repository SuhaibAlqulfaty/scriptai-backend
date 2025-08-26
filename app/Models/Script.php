<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Script extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'topic',
        'key_points',
        'tone',
        'language',
        'generated_script',
        'word_count',
        'estimated_duration',
        'quality_score',
        'engagement_score',
        'user_ip',
        'user_agent',
        'generation_time',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'word_count' => 'integer',
        'estimated_duration' => 'integer',
        'quality_score' => 'float',
        'engagement_score' => 'float',
        'generation_time' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the feedback for the script.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ScriptFeedback::class);
    }

    /**
     * Get the average rating for this script.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->feedback()->avg('rating') ?? 0;
    }

    /**
     * Get the total feedback count for this script.
     */
    public function getFeedbackCountAttribute(): int
    {
        return $this->feedback()->count();
    }

    /**
     * Scope a query to only include scripts of a given tone.
     */
    public function scopeOfTone($query, string $tone)
    {
        return $query->where('tone', $tone);
    }

    /**
     * Scope a query to only include scripts of a given language.
     */
    public function scopeOfLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope a query to order by quality score.
     */
    public function scopeByQuality($query, string $direction = 'desc')
    {
        return $query->orderBy('quality_score', $direction);
    }

    /**
     * Scope a query to order by engagement score.
     */
    public function scopeByEngagement($query, string $direction = 'desc')
    {
        return $query->orderBy('engagement_score', $direction);
    }

    /**
     * Get popular topics with their counts.
     */
    public static function getPopularTopics(int $limit = 10): array
    {
        return self::selectRaw('topic, COUNT(*) as count')
            ->groupBy('topic')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get tone statistics.
     */
    public static function getToneStatistics(): array
    {
        return self::selectRaw('tone, COUNT(*) as count')
            ->groupBy('tone')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    /**
     * Get language statistics.
     */
    public static function getLanguageStatistics(): array
    {
        return self::selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    /**
     * Get daily generation statistics for the last N days.
     */
    public static function getDailyStats(int $days = 7): array
    {
        $stats = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = self::whereDate('created_at', $date)->count();
            $stats[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        return array_reverse($stats);
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'key_points' => $this->key_points,
            'tone' => $this->tone,
            'language' => $this->language,
            'generated_script' => $this->generated_script,
            'word_count' => $this->word_count,
            'estimated_duration' => $this->estimated_duration,
            'quality_score' => $this->quality_score,
            'engagement_score' => $this->engagement_score,
            'average_rating' => $this->average_rating,
            'feedback_count' => $this->feedback_count,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
