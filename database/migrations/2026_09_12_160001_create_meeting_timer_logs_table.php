<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_timer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('speaker_type'); // 'prepared_speaker', 'evaluator', 'ttm_speaker'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of speaker/evaluation/ttm record
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('allotted_time')->nullable(); // e.g. "5-7 mins", "2-3 mins", "1-2 mins"
            $table->string('time_taken')->nullable();    // e.g. "06:15", "02:45"
            $table->string('status')->default('within_time'); // 'within_time', 'over_time', 'under_time', 'disqualified'
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['meeting_id', 'speaker_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_timer_logs');
    }
};
