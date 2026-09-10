<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('slot')->default(1); // ordering
            $table->string('speech_type')->nullable(); // e.g. "Prepared", "Ice Breaker"
            $table->string('project')->nullable();    // speech project name
            $table->string('topic')->nullable();
            $table->string('duration')->nullable();   // e.g. "5-7 min"
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('meeting_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_speakers');
    }
};
