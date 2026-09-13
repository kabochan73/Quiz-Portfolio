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
    // 現時点でauthミドルウェア配下にあるのは/logoutのみ。
    // categories等のルートを実装した際に、そちらでも同様のテストを追加する。
    $response = $this->post('/logout');

    $response->assertRedirect('/login');
});
