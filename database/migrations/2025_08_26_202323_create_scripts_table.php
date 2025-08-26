<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scripts', function (Blueprint $table) {
            $table->id();
            
            // Input fields
            $table->string('topic', 500);
            $table->text('key_points')->nullable();
            $table->string('tone', 50)->default('educational');
            $table->string('language', 10)->default('ar');
            
            // Generated content
            $table->longText('generated_script');
            $table->integer('word_count')->nullable();
            $table->integer('estimated_duration')->nullable(); // in seconds
            
            // Quality metrics
            $table->float('quality_score')->nullable(); // 0-100
            $table->float('engagement_score')->nullable(); // 0-100
            
            // Metadata
            $table->string('user_ip', 45)->nullable(); // IPv6 support
            $table->string('user_agent', 500)->nullable();
            $table->float('generation_time')->nullable(); // in seconds
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['tone', 'language']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scripts');
    }
};
