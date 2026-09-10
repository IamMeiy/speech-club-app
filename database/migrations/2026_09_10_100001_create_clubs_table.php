<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // short code/slug e.g. "chola"
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->string('logo')->nullable();
            $table->string('meeting_day')->nullable(); // e.g. "Monday"
            $table->time('meeting_time')->nullable();
            $table->string('location')->nullable();
            $table->string('timezone')->nullable()->default('Asia/Kolkata');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
