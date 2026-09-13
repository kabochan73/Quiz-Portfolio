@php
    $average = round($answers->avg(fn ($answer) => $answer->score->score), 1);
@endphp
<x-layout title="採点結果" :wide="true">
    <a href="{{ route('sections.show', $section) }}" class="text-sm text-zinc-500 hover:text-zinc-900">
        ← {{ $section->name }}
    </a>
    <div class="mb-6 mt-1 flex items-baseline justify-between">
        <h1 class="text-xl font-semibold tracking-tight">採点結果</h1>
        <p class="text-sm text-zinc-500">平均 {{ $average }}点</p>
    </div>

    <div class="space-y-4">
        @foreach ($answers as $index => $answer)
            @php
                $score = $answer->score->score;
                // 点数の良し悪しを示す意味のある色なので、アクセントカラーとは別に緑/赤を維持する
                $scoreColor = match (true) {
                    $score >= 80 => 'text-emerald-600',
                    $score < 50 => 'text-red-600',
                    default => 'text-zinc-600',
                };
            @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm text-zinc-500">問題 {{ $index + 1 }}</p>
                    <p class="text-2xl font-semibold {{ $scoreColor }}">{{ $score }}点</p>
                </div>

                <p class="mb-3 whitespace-pre-wrap text-sm leading-relaxed text-zinc-700">{{ $answer->question->body }}</p>

                <div class="mb-3 rounded-lg bg-zinc-50 p-3">
                    <p class="mb-1 text-xs text-zinc-500">回答</p>
                    <p class="whitespace-pre-wrap text-sm">{{ $answer->body }}</p>
                </div>

                <div class="rounded-lg bg-brand-50 p-3">
                    <p class="mb-1 text-xs text-brand-700">フィードバック</p>
                    <p class="whitespace-pre-wrap text-sm text-brand-900">{{ $answer->score->feedback }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-layout>
