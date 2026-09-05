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
       Schema::create('attendance_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('participation_id')->constrained('conference_participation')->cascadeOnDelete();
    $table->timestamp('check_in')->useCurrent();
    $table->timestamp('check_out')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
