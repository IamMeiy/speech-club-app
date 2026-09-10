<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_role_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. "TMOD", "GE"
            $table->string('slug')->unique(); // e.g. "tmod", "ge"
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_role_types');
    }
};
