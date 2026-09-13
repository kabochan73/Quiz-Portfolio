<?php

use App\Http\Controllers\Auth\LoginController;
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
});
