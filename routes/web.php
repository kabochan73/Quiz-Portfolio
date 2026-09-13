<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

// アプリの起点はログイン画面(要件定義5章)。ルートは常にログインへ流す。
Route::get('/', fn () => redirect()->route('login'));

// 未ログインのときだけ通れるルート。ログイン済みならcategoriesへ流す(guestミドルウェア)。
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    // ログイン試行を1分あたり6回までに制限し、パスワードの総当たりを抑える。
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
});

// ログイン必須のルート。会員登録は行わないので、ここに入れるのは常に唯一の管理者ユーザー。
// categories/sections/questions/answers/history のルートは、各機能を実装するタイミングで
// このグループ内に追加していく。
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // カテゴリ管理。ログイン後の着地点でもあり、アプリの起点になる。
    Route::resource('categories', CategoryController::class);

    // セクションは必ずカテゴリに紐づくため、作成はカテゴリ配下のURLにする。
    Route::get('/categories/{category}/sections/create', [SectionController::class, 'create'])->name('sections.create');
    Route::post('/categories/{category}/sections', [SectionController::class, 'store'])->name('sections.store');

    // 詳細・編集・削除はセクションIDだけで一意に決まるのでフラットなURL。
    Route::get('/sections/{section}', [SectionController::class, 'show'])->name('sections.show');
    Route::get('/sections/{section}/edit', [SectionController::class, 'edit'])->name('sections.edit');
    Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    // 問題は必ずセクションに紐づくため、作成はセクション配下のURLにする(選択の必要がない)。
    Route::get('/sections/{section}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/sections/{section}/questions', [QuestionController::class, 'store'])->name('questions.store');

    // 詳細プレビュー・編集・削除は問題IDだけで一意に決まるのでフラットなURLのまま。
    Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // answers/history のルートは、各機能を実装するタイミングでこのグループ内に追加していく。
});
