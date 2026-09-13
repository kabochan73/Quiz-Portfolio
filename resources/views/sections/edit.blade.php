<x-layout title="セクション編集">
    <div class="rounded-lg border border-stone-200 bg-white p-6 sm:p-8">
        <h1 class="mb-6 text-xl font-semibold">セクション編集</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('sections.update', $section) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1 block text-sm text-stone-600">セクション名</label>
                <input type="text" id="name" name="name" value="{{ old('name', $section->name) }}" required autofocus
                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">
            </div>

            <div>
                <label for="category_id" class="mb-1 block text-sm text-stone-600">所属カテゴリ</label>
                <select id="category_id" name="category_id"
                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-base">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(old('category_id', $section->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('sections.show', $section) }}"
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
