<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'club_id']);
            $table->index('user_id');
            $table->index('club_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_clubs');
    }
};
