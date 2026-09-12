<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('start_time', 20)->nullable()->after('meeting_date'); // e.g. "19:00" or "07:00 PM"
            $table->string('end_time', 20)->nullable()->after('start_time');     // e.g. "21:00" or "09:00 PM"
            $table->string('word_of_the_day')->nullable()->after('venue');
            $table->string('word_part_of_speech', 50)->nullable()->after('word_of_the_day'); // e.g. "Noun", "Adjective"
            $table->text('word_definition')->nullable()->after('word_part_of_speech');
            $table->text('word_example_sentence')->nullable()->after('word_definition');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn([
                'start_time',
                'end_time',
                'word_of_the_day',
                'word_part_of_speech',
                'word_definition',
                'word_example_sentence',
            ]);
        });
    }
};
