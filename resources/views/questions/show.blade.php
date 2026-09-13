<x-layout title="問題プレビュー">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 sm:p-8">
        <a href="{{ route('sections.show', $question->section) }}" class="text-sm text-zinc-500 hover:text-zinc-900">
            ← {{ $question->section->name }}
        </a>
        <h1 class="mb-6 mt-1 text-xl font-semibold tracking-tight">問題プレビュー</h1>

        <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ $question->body }}</p>

        {{-- 自分の問題のときだけ編集・削除ボタンを出す(QuestionPolicy) --}}
        @can('update', $question)
            <div class="mt-6 flex gap-2">
                <a href="{{ route('questions.edit', $question) }}"
                    class="flex min-h-11 items-center rounded-lg border border-zinc-300 px-4 text-sm transition-colors hover:bg-zinc-50">
                    編集
                </a>
                <form method="POST" action="{{ route('questions.destroy', $question) }}"
                    onsubmit="return confirm('この問題を削除すると、回答履歴・採点結果もすべて削除されます。よろしいですか?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="flex min-h-11 items-center rounded-lg border border-red-200 px-4 text-sm text-red-600 transition-colors hover:bg-red-50">
                        削除
                    </button>
                </form>
            </div>
        @endcan
    </div>
</x-layout>
