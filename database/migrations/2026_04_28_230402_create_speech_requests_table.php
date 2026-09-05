<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('speech_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('participation_id')->constrained('conference_participation')->cascadeOnDelete();
    $table->foreignId('conference_id')->constrained()->cascadeOnDelete();

    $table->timestamp('request_time')->useCurrent();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speech_requests');
    }
};
