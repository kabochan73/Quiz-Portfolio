<x-layout :title="$category->name" :wide="true">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('categories.index') }}" class="text-sm text-stone-500 hover:underline">← カテゴリ一覧</a>
            <h1 class="mt-1 text-xl font-semibold">{{ $category->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('categories.edit', $category) }}"
                class="flex min-h-11 items-center rounded-md border border-stone-300 px-4 text-sm">
                編集
            </a>
            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                onsubmit="return confirm('このカテゴリを削除すると、配下のセクション・問題・回答履歴もすべて削除されます。よろしいですか?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="flex min-h-11 items-center rounded-md border border-red-300 px-4 text-sm text-red-700">
                    削除
                </button>
            </form>
        </div>
    </div>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-medium">セクション</h2>
        {{-- sections機能はこの後のステップで実装するため、ルートが揃うまではボタンを出さない --}}
        @if (Route::has('sections.create'))
            <a href="{{ route('sections.create', $category) }}"
                class="flex min-h-11 items-center rounded-md bg-emerald-700 px-4 text-sm font-medium text-white hover:bg-emerald-800">
                + セクションを追加
            </a>
        @endif
    </div>

    @if ($category->sections->isEmpty())
        <p class="text-sm text-stone-500">まだセクションがありません。</p>
    @else
        <div class="divide-y divide-stone-200 rounded-lg border border-stone-200 bg-white">
            @foreach ($category->sections as $section)
                <a href="{{ Route::has('sections.show') ? route('sections.show', $section) : '#' }}"
                    class="flex items-center justify-between p-4 hover:bg-stone-50">
                    <span>{{ $section->name }}</span>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
