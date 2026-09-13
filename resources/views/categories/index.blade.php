<x-layout title="カテゴリ一覧" :wide="true">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-xl font-semibold tracking-tight">カテゴリ一覧</h1>
        <a href="{{ route('categories.create') }}"
            class="flex min-h-11 items-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-700">
            + 新規カテゴリ作成
        </a>
    </div>

    @if ($categories->isEmpty())
        <p class="text-sm text-zinc-500">まだカテゴリがありません。「+ 新規カテゴリ作成」から作成してください。</p>
    @else
        {{-- スマホでは1カラム、sm以上で2〜3カラムに広げる(レスポンシブ方針) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                    class="block rounded-xl border border-zinc-200 bg-white p-5 transition-colors hover:border-brand-300">
                    <h2 class="font-medium tracking-tight">{{ $category->name }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ $category->sections_count }}セクション</p>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
