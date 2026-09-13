<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 「home」という名前のルートを持たないため、guestミドルウェアがログイン済みの人を
        // /loginから追い返す先を明示的にcategories.indexにしておく。
        // (指定しないと'/'に飛ばされ、'/'は常に/loginへ戻すルートなので無限ループになる)
        RedirectIfAuthenticated::redirectUsing(fn () => route('categories.index'));
    }
}
