@php
    $max = config('quiz.max_questions_per_section');
    $count = $section->questions->count();
@endphp
<x-layout :title="$section->name" :wide="true">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('categories.show', $section->category) }}" class="text-sm text-zinc-500 hover:text-zinc-900">
                ← {{ $section->category->name }}
            </a>
            <h1 class="mt-1 text-xl font-semibold tracking-tight">{{ $section->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sections.edit', $section) }}"
                class="flex min-h-11 items-center rounded-lg border border-zinc-300 px-4 text-sm transition-colors hover:bg-zinc-50">
                編集
            </a>
            <form method="POST" action="{{ route('sections.destroy', $section) }}"
                onsubmit="return confirm('このセクションを削除すると、配下の問題・回答履歴もすべて削除されます。よろしいですか?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="flex min-h-11 items-center rounded-lg border border-red-200 px-4 text-sm text-red-600 transition-colors hover:bg-red-50">
                    削除
                </button>
            </form>
        </div>
    </div>

    {{-- 1セクション最大{{ $max }}問という上限(要件定義3.2)に対する進捗表示 --}}
    <div class="mb-6">
        <div class="mb-1 flex justify-between text-sm text-zinc-500">
            <span>問題数</span>
            <span>{{ $count }} / {{ $max }}</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-zinc-200">
            <div class="h-1.5 rounded-full bg-brand-600" style="width: {{ min(100, $count / $max * 100) }}%"></div>
        </div>
    </div>

    {{-- 回答・履歴機能は次のステップ以降で実装するため、ルートが揃うまでは表示しない --}}
    <div class="mb-8 flex flex-wrap gap-3">
        @if (Route::has('questions.create'))
            <a href="{{ route('questions.create', $section) }}"
                class="flex min-h-11 items-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-700">
                + 問題を追加
            </a>
        @endif
        @if (Route::has('answers.create') && $count > 0)
            <a href="{{ route('answers.create', $section) }}"
                class="flex min-h-11 items-center rounded-lg border border-brand-600 px-4 text-sm font-medium text-brand-700 transition-colors hover:bg-brand-50">
                全問({{ $count }}問)に回答する
            </a>
        @endif
        @if (Route::has('history.index'))
            <a href="{{ route('history.index', $section) }}"
                class="flex min-h-11 items-center rounded-lg border border-zinc-300 px-4 text-sm transition-colors hover:bg-zinc-50">
                履歴を見る
            </a>
        @endif
    </div>

    <h2 class="mb-4 font-medium tracking-tight">問題一覧</h2>

    @if ($section->questions->isEmpty())
        <p class="text-sm text-zinc-500">まだ問題がありません。</p>
    @else
        <div class="divide-y divide-zinc-200 rounded-xl border border-zinc-200 bg-white">
            @foreach ($section->questions as $question)
                <a href="{{ Route::has('questions.show') ? route('questions.show', $question) : '#' }}"
                    class="block p-4 transition-colors hover:bg-zinc-50">
                    {{ $question->excerpt() }}
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
