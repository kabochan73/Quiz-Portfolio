@props(['title' => null, 'wide' => false])
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
    {{-- ログイン済みの画面にだけナビゲーションを出す。カテゴリ一覧が実質的なホーム。 --}}
    @auth
        <nav class="flex items-center justify-between border-b border-zinc-200 bg-white px-4 py-3 sm:px-6">
            <a href="{{ route('categories.index') }}" class="text-sm font-semibold tracking-tight text-zinc-900">
                {{ config('app.name') }}
            </a>
            {{-- ミニマルさを出すため、ボーダー付きボタンではなくテキストリンク風にする --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex min-h-11 items-center px-3 text-sm text-zinc-500 transition-colors hover:text-zinc-900">
                    ログアウト
                </button>
            </form>
        </nav>
    @endauth

    {{-- 一覧・フォーム系の通常画面はmax-w-lg、一覧+一括操作系の画面は$wideでmax-w-4xlに広げる --}}
    <main class="mx-auto w-full {{ $wide ? 'max-w-4xl' : 'max-w-lg' }} px-4 py-10 sm:px-6">
        {{-- 直前の操作結果(成功メッセージ)をセッションから受け取って表示する --}}
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-700">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
