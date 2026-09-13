<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 問題は必ず1つのセクションに属し(section_idはNOT NULL)、未分類は存在しない(要件定義3.2)。
     * 1セクションにつき最大10問という上限はアプリ層(FormRequest+トランザクション)で担保するため、
     * ここではDB上の行数制約は設けない。
     *
     * user_idは現状ログインできる管理者1名しか使わないが、将来のマルチユーザー化に備えて残す
     * (要件定義6章のコメント通り)。
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
