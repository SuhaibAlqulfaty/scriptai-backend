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
        Schema::create('script_feedback', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to scripts table
            $table->foreignId('script_id')->constrained('scripts')->onDelete('cascade');
            
            // Feedback metrics
            $table->integer('rating'); // 1-5 stars (required)
            $table->integer('usefulness')->nullable(); // 1-5
            $table->integer('clarity')->nullable(); // 1-5
            $table->integer('engagement')->nullable(); // 1-5
            
            // Optional feedback text
            $table->text('feedback_text')->nullable();
            
            // User info
            $table->string('user_ip', 45)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('script_id');
            $table->index('rating');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('script_feedback');
    }
};
