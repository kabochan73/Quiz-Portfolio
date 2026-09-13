<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attemptは「セクションの全問に一括回答した1回分」を表すレコード(要件定義3.3)。
     * Udemyのクイズ結果のように、この1レコードの下にAnswerが複数ぶら下がる。
     * 採点レベルは回答単位ではなくAttempt単位で1つ持つ(同じ挑戦内の全問に共通のため)。
     */
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('grading_level', ['easy', 'normal', 'hard']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
