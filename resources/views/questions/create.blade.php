@php
    $isFull = $section->isFull();
@endphp
<x-layout title="問題作成">
    <div class="rounded-lg border border-stone-200 bg-white p-6 sm:p-8">
        <a href="{{ route('sections.show', $section) }}" class="text-sm text-stone-500 hover:underline">
            ← {{ $section->name }}
        </a>
        <h1 class="mb-6 mt-1 text-xl font-semibold">問題作成</h1>

        @if ($isFull)
            {{-- 要件定義3.2: 1セクション最大{{ config('quiz.max_questions_per_section') }}問。
                 上限に達している場合はフォームを出さず警告のみ表示する --}}
            <p class="rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-700">
                このセクションは既に問題が最大{{ config('quiz.max_questions_per_section') }}問に達しているため、
                これ以上追加できません。
            </p>
        @else
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('questions.store', $section) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="body" class="mb-1 block text-sm text-stone-600">問題文</label>
                    <textarea id="body" name="body" rows="6" required autofocus
                        class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">{{ old('body') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('sections.show', $section) }}"
                        class="flex min-h-11 flex-1 items-center justify-center rounded-md border border-stone-300 text-sm">
                        キャンセル
                    </a>
                    <button type="submit"
                        class="min-h-11 flex-1 rounded-md bg-emerald-700 text-sm font-medium text-white hover:bg-emerald-800">
                        作成する
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-layout>
