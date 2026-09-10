<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('meeting_number');
            $table->date('meeting_date');
            $table->string('theme')->nullable();
            $table->string('venue')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, completed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['club_id', 'meeting_number']);
            $table->index('club_id');
            $table->index('meeting_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
