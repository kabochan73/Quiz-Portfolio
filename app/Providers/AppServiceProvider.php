<?php

namespace App\Providers;

use App\Services\Grading\ClaudeGradingService;
use App\Services\Grading\FakeGradingService;
use App\Services\Grading\GradingService;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AI採点はconfig('services.grading.driver')で切り替える。
        // .env.testingでfakeに固定しているため、テストスイートは絶対にClaude APIを叩かない。
        $this->app->bind(GradingService::class, fn () => match (config('services.grading.driver')) {
            'fake' => new FakeGradingService,
            default => new ClaudeGradingService,
        });
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
