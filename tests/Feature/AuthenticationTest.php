<?php

use App\Models\User;

test('未ログインでもログイン画面は表示できる', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('ログイン');
});

test('誤った認証情報ではログインできず、エラーメッセージが表示される', function () {
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'correct-password',
    ]);

    // back()withErrors()は直前のRefererヘッダーに戻すため、from()で/loginから来たことにする
    $response = $this->from('/login')->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('正しい認証情報でログインするとカテゴリ一覧へリダイレクトされる', function () {
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'correct-password',
    ]);

    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'correct-password',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertAuthenticated();
});

test('ログイン済みで/loginにアクセスするとカテゴリ一覧へリダイレクトされる', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('categories.index'));
});

test('未入力でログインしようとするとバリデーションエラーになる', function () {
    $response = $this->post('/login', [
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['email', 'password']);
});

test('ログイン済みユーザーはログアウトできる', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('未ログインで保護されたルートにアクセスするとログイン画面へリダイレクトされる', function () {
    $response = $this->get(route('categories.index'));

    $response->assertRedirect('/login');
});
