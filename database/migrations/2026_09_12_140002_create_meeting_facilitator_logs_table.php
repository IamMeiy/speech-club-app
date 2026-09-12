<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ah-Counter tracking table
        Schema::create('meeting_ah_counter_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ah_count')->default(0);
            $table->unsignedSmallInteger('um_count')->default(0);
            $table->unsignedSmallInteger('er_count')->default(0);
            $table->unsignedSmallInteger('like_count')->default(0);
            $table->unsignedSmallInteger('you_know_count')->default(0);
            $table->unsignedSmallInteger('so_count')->default(0);
            $table->unsignedSmallInteger('repeats_count')->default(0);
            $table->unsignedSmallInteger('other_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
            $table->index('meeting_id');
        });

        // Grammarian tracking table
        Schema::create('meeting_grammarian_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('word_of_day_count')->default(0);
            $table->text('good_phrases')->nullable();
            $table->text('awkward_phrases')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
            $table->index('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_grammarian_logs');
        Schema::dropIfExists('meeting_ah_counter_logs');
    }
};
