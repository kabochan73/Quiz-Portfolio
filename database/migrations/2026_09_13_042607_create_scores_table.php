<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scoreは1つのAnswerに対する採点結果(1:1)。answer_idをuniqueにすることで1:1を担保する。
     *
     * 点数は0〜100点(要件定義3.3)。アプリ側(ClaudeGradingService)でもクランプするが、
     * LaravelのBlueprintにはcheck制約を追加する標準メソッドが無いため、
     * DB::statement()でPostgresのCHECK制約を直接追加し、DBレベルでも範囲外の値を弾けるようにする。
     */
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('feedback');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE scores ADD CONSTRAINT scores_score_between_0_and_100 CHECK (score >= 0 AND score <= 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
