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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->time('timing');
            $table->string('title', 255);
            $table->string('body', 255);
            $table->unsignedBigInteger('notifiable_id');
            $table->unsignedTinyInteger('notifiable_type');
            $table->timestamp('read_at');
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
