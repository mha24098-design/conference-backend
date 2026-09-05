<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_participation', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('conference_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('device_id')
                ->nullable()
                ->constrained('devices')
                ->nullOnDelete();

            // حالة الدعوة
            $table->enum('participation_status', [
                'pending',
                'accepted',
                'rejected'
            ])->default('pending');

            // حالة الصوت داخل المؤتمر
            $table->enum('current_status', [
                'listening',
                'speaking'
            ])->default('listening');

            $table->boolean('is_muted')->default(false);

            $table->boolean('forced_muted_by_admin')->default(false);

            $table->boolean('is_streaming')->default(false);

            $table->timestamps();

            $table->unique([
                'user_id',
                'conference_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_participation');
    }
};
