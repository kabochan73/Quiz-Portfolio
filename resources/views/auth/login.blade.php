<x-layout title="ログイン">
    <div class="rounded-lg border border-stone-200 bg-white p-6 sm:p-8">
        <h1 class="mb-6 text-xl font-semibold">ログイン</h1>

        {{-- バリデーションエラー・認証失敗メッセージをまとめて表示 --}}
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm text-stone-600">メールアドレス</label>
                {{-- text-baseで16px以上を保ち、iOS Safariでのフォーカス時ズームインを防ぐ --}}
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm text-stone-600">パスワード</label>
                <input type="password" id="password" name="password" required
                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">
            </div>

            {{-- min-h-11でタップ領域を確保 --}}
            <button type="submit"
                class="min-h-11 w-full rounded-md bg-emerald-700 px-4 text-sm font-medium text-white hover:bg-emerald-800">
                ログイン
            </button>
        </form>
    </div>
</x-layout>
