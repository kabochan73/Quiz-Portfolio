@php
    $average = round($answers->avg(fn ($answer) => $answer->score->score), 1);
@endphp
<x-layout title="採点結果" :wide="true">
    <a href="{{ route('sections.show', $section) }}" class="text-sm text-stone-500 hover:underline">
        ← {{ $section->name }}
    </a>
    <div class="mb-6 mt-1 flex items-baseline justify-between">
        <h1 class="text-xl font-semibold">採点結果</h1>
        <p class="text-sm text-stone-500">平均 {{ $average }}点</p>
    </div>

    <div class="space-y-4">
        @foreach ($answers as $index => $answer)
            @php
                $score = $answer->score->score;
                // 点数に応じて色分け(80以上は緑、50未満は赤、それ以外はグレー)
                $scoreColor = match (true) {
                    $score >= 80 => 'text-emerald-600',
                    $score < 50 => 'text-red-600',
                    default => 'text-stone-600',
                };
            @endphp
            <div class="rounded-lg border border-stone-200 bg-white p-5">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm text-stone-500">問題 {{ $index + 1 }}</p>
                    <p class="text-2xl font-semibold {{ $scoreColor }}">{{ $score }}点</p>
                </div>

                <p class="mb-3 whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $answer->question->body }}</p>

                <div class="mb-3 rounded-md bg-stone-50 p-3">
                    <p class="mb-1 text-xs text-stone-500">回答</p>
                    <p class="whitespace-pre-wrap text-sm">{{ $answer->body }}</p>
                </div>

                <div class="rounded-md bg-emerald-50 p-3">
                    <p class="mb-1 text-xs text-emerald-700">フィードバック</p>
                    <p class="whitespace-pre-wrap text-sm text-emerald-800">{{ $answer->score->feedback }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-layout>
