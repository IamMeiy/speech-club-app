<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            // References the prepared speaker being evaluated
            $table->foreignId('speaker_id')->constrained('meeting_speakers')->cascadeOnDelete();
            $table->foreignId('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('meeting_id');
            $table->index('speaker_id');
            $table->index('evaluator_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_evaluations');
    }
};
