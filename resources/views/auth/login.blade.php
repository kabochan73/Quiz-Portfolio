<x-layout title="ログイン">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 sm:p-8">
        <h1 class="mb-6 text-xl font-semibold tracking-tight">ログイン</h1>

        {{-- バリデーションエラー・認証失敗メッセージをまとめて表示 --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm text-zinc-600">メールアドレス</label>
                {{-- text-baseで16px以上を保ち、iOS Safariでのフォーカス時ズームインを防ぐ --}}
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-base transition-colors focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm text-zinc-600">パスワード</label>
                <input type="password" id="password" name="password" required
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-base transition-colors focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>

            {{-- min-h-11でタップ領域を確保 --}}
            <button type="submit"
                class="min-h-11 w-full rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-700">
                ログイン
            </button>
        </form>
    </div>
</x-layout>
