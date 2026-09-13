<x-layout title="カテゴリ一覧" :wide="true">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">カテゴリ一覧</h1>
        <a href="{{ route('categories.create') }}"
            class="flex min-h-11 items-center rounded-md bg-emerald-700 px-4 text-sm font-medium text-white hover:bg-emerald-800">
            + 新規カテゴリ作成
        </a>
    </div>

    @if ($categories->isEmpty())
        <p class="text-sm text-stone-500">まだカテゴリがありません。「+ 新規カテゴリ作成」から作成してください。</p>
    @else
        {{-- スマホでは1カラム、sm以上で2〜3カラムに広げる(レスポンシブ方針) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                    class="block rounded-lg border border-stone-200 bg-white p-5 hover:border-emerald-300">
                    <h2 class="font-medium">{{ $category->name }}</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ $category->sections_count }}セクション</p>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
