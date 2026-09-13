<x-layout title="一括回答" :wide="true">
    <a href="{{ route('sections.show', $section) }}" class="text-sm text-zinc-500 hover:text-zinc-900">
        ← {{ $section->name }}
    </a>
    <h1 class="mb-6 mt-1 text-xl font-semibold tracking-tight">全問に回答する({{ $questions->count() }}問)</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Alpineでsubmitting状態を持たせ、二重送信防止+送信中の表示を行う(Claude APIの採点は数秒〜数十秒かかりうる) --}}
    <form method="POST" action="{{ route('answers.store', $section) }}" class="space-y-6"
        x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="rounded-xl border border-zinc-200 bg-white p-5">
            <p class="mb-2 text-sm font-medium text-zinc-700">採点レベル</p>
            {{-- スマホ幅でも3択が潰れないようgrid-cols-3で固定幅にする --}}
            <div class="grid grid-cols-3 gap-2">
                @foreach ($gradingLevels as $level)
                    <label
                        class="flex min-h-11 cursor-pointer items-center justify-center rounded-lg border border-zinc-300 text-sm transition-colors has-checked:border-brand-600 has-checked:bg-brand-50 has-checked:text-brand-700">
                        <input type="radio" name="grading_level" value="{{ $level->value }}" class="sr-only"
                            @checked(old('grading_level', 'normal') === $level->value) required>
                        {{ $level->label() }}
                    </label>
                @endforeach
            </div>
        </div>

        @foreach ($questions as $index => $question)
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <p class="mb-3 text-sm text-zinc-500">問題 {{ $index + 1 }}</p>
                <p class="mb-4 whitespace-pre-wrap text-sm leading-relaxed">{{ $question->body }}</p>

                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                <textarea name="answers[{{ $index }}][body]" rows="6" required
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-base transition-colors focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                    placeholder="回答を入力してください">{{ old("answers.$index.body") }}</textarea>
            </div>
        @endforeach

        <button type="submit" :disabled="submitting"
            class="min-h-11 w-full rounded-lg bg-brand-600 text-sm font-medium text-white transition-colors hover:bg-brand-700 disabled:opacity-60">
            <span x-show="!submitting">採点する</span>
            <span x-show="submitting" x-cloak>採点中です。しばらくお待ちください…</span>
        </button>
    </form>
</x-layout>
