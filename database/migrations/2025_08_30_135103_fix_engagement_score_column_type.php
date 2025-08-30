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
        Schema::table('scripts', function (Blueprint $table) {
            // Change engagement_score from numeric to string to store 'high', 'medium', 'low'
            $table->string('engagement_score', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scripts', function (Blueprint $table) {
            // Revert back to numeric if needed
            $table->decimal('engagement_score', 5, 2)->change();
        });
    }
};
