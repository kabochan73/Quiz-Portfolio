<x-layout title="問題編集">
    <div class="rounded-lg border border-stone-200 bg-white p-6 sm:p-8">
        <h1 class="mb-6 text-xl font-semibold">問題編集</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('questions.update', $question) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="body" class="mb-1 block text-sm text-stone-600">問題文</label>
                <textarea id="body" name="body" rows="6" required autofocus
                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">{{ old('body', $question->body) }}</textarea>
            </div>

            <div>
                <label for="section_id" class="mb-1 block text-sm text-stone-600">所属セクション</label>
                <select id="section_id" name="section_id"
                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}"
                            @selected(old('section_id', $question->section_id) == $section->id)>
                            {{ $section->category->name }} / {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('questions.show', $question) }}"
                    class="flex min-h-11 flex-1 items-center justify-center rounded-md border border-stone-300 text-sm">
                    キャンセル
                </a>
                <button type="submit"
                    class="min-h-11 flex-1 rounded-md bg-emerald-700 text-sm font-medium text-white hover:bg-emerald-800">
                    更新する
                </button>
            </div>
        </form>
    </div>
</x-layout>
