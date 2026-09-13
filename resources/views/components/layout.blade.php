@props(['title' => null, 'wide' => false])
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900">
    {{-- ログイン済みの画面にだけナビゲーションを出す。カテゴリ一覧が実質的なホーム。 --}}
    @auth
        <nav class="flex items-center justify-between border-b border-stone-200 bg-white px-4 py-3 sm:px-6">
            <a href="{{ route('categories.index') }}" class="font-semibold text-emerald-700">
                {{ config('app.name') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="min-h-11 rounded-md border border-stone-300 px-4 text-sm text-stone-600">
                    ログアウト
                </button>
            </form>
        </nav>
    @endauth

    {{-- 一覧・フォーム系の通常画面はmax-w-lg、一覧+一括操作系の画面は$wideでmax-w-4xlに広げる --}}
    <main class="mx-auto w-full {{ $wide ? 'max-w-4xl' : 'max-w-lg' }} px-4 py-8 sm:px-6">
        {{-- 直前の操作結果(成功メッセージ)をセッションから受け取って表示する --}}
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
