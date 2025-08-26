<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScriptFeedback extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'script_feedback';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'script_id',
        'rating',
        'usefulness',
        'clarity',
        'engagement',
        'feedback_text',
        'user_ip',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'script_id' => 'integer',
        'rating' => 'integer',
        'usefulness' => 'integer',
        'clarity' => 'integer',
        'engagement' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the script that owns the feedback.
     */
    public function script(): BelongsTo
    {
        return $this->belongsTo(Script::class);
    }

    /**
     * Get the overall score (average of all metrics).
     */
    public function getOverallScoreAttribute(): float
    {
        $scores = array_filter([
            $this->rating,
            $this->usefulness,
            $this->clarity,
            $this->engagement,
        ]);

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }

    /**
     * Scope a query to only include feedback with a minimum rating.
     */
    public function scopeMinRating($query, int $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope a query to only include feedback with text.
     */
    public function scopeWithText($query)
    {
        return $query->whereNotNull('feedback_text')
                    ->where('feedback_text', '!=', '');
    }

    /**
     * Get average ratings for all feedback.
     */
    public static function getAverageRatings(): array
    {
        return [
            'overall_rating' => self::avg('rating') ?? 0,
            'usefulness' => self::avg('usefulness') ?? 0,
            'clarity' => self::avg('clarity') ?? 0,
            'engagement' => self::avg('engagement') ?? 0,
        ];
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'script_id' => $this->script_id,
            'rating' => $this->rating,
            'usefulness' => $this->usefulness,
            'clarity' => $this->clarity,
            'engagement' => $this->engagement,
            'feedback_text' => $this->feedback_text,
            'overall_score' => $this->overall_score,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
