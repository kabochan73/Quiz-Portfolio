<x-layout :title="$category->name" :wide="true">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('categories.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900">← カテゴリ一覧</a>
            <h1 class="mt-1 text-xl font-semibold tracking-tight">{{ $category->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('categories.edit', $category) }}"
                class="flex min-h-11 items-center rounded-lg border border-zinc-300 px-4 text-sm transition-colors hover:bg-zinc-50">
                編集
            </a>
            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                onsubmit="return confirm('このカテゴリを削除すると、配下のセクション・問題・回答履歴もすべて削除されます。よろしいですか?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="flex min-h-11 items-center rounded-lg border border-red-200 px-4 text-sm text-red-600 transition-colors hover:bg-red-50">
                    削除
                </button>
            </form>
        </div>
    </div>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-medium tracking-tight">セクション</h2>
        {{-- sections機能はこの後のステップで実装するため、ルートが揃うまではボタンを出さない --}}
        @if (Route::has('sections.create'))
            <a href="{{ route('sections.create', $category) }}"
                class="flex min-h-11 items-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-700">
                + セクションを追加
            </a>
        @endif
    </div>

    @if ($category->sections->isEmpty())
        <p class="text-sm text-zinc-500">まだセクションがありません。</p>
    @else
        <div class="divide-y divide-zinc-200 rounded-xl border border-zinc-200 bg-white">
            @foreach ($category->sections as $section)
                <a href="{{ Route::has('sections.show') ? route('sections.show', $section) : '#' }}"
                    class="flex items-center justify-between p-4 transition-colors hover:bg-zinc-50">
                    <span>{{ $section->name }}</span>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
