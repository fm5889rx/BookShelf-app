<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Advanced:
     * 通知notificationsテーブル定義
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('timing', 50)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('body', 255)->nullable();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
