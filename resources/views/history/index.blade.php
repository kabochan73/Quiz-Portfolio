<x-layout title="回答履歴" :wide="true">
    <a href="{{ route('sections.show', $section) }}" class="text-sm text-zinc-500 hover:text-zinc-900">
        ← {{ $section->name }}
    </a>
    <h1 class="mb-6 mt-1 text-xl font-semibold tracking-tight">回答履歴</h1>

    @if ($attempts->isEmpty())
        <p class="text-sm text-zinc-500">まだ回答履歴がありません。</p>
    @else
        {{-- Udemy風に「1回の全問回答(挑戦)」単位で新しい順に一覧表示する(要件定義3.4) --}}
        <div class="divide-y divide-zinc-200 rounded-xl border border-zinc-200 bg-white">
            @foreach ($attempts as $attempt)
                <a href="{{ route('history.show', [$section, $attempt]) }}"
                    class="flex flex-col gap-1 p-4 transition-colors hover:bg-zinc-50 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm">{{ $attempt->created_at->format('Y/m/d H:i') }}</p>
                        <p class="text-xs text-zinc-500">
                            {{ $attempt->answers->count() }}問・採点レベル: {{ $attempt->grading_level->label() }}
                        </p>
                    </div>
                    <p class="text-lg font-semibold text-brand-700">平均 {{ $attempt->averageScore() }}点</p>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
