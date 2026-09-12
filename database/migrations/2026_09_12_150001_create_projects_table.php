<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('track')->default('Pathways Core'); // e.g. Pathways Core, Presentation Mastery, etc.
            $table->unsignedTinyInteger('level')->nullable(); // Level 1 to 5
            $table->unsignedSmallInteger('min_minutes')->default(5);
            $table->unsignedSmallInteger('max_minutes')->default(7);
            $table->string('default_duration')->default('5-7 mins');
            $table->text('overview')->nullable();
            $table->text('objectives')->nullable();
            $table->text('evaluator_notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('track');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
