<?php

namespace App\Http\Requests;

use App\Models\Question;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 編集時は所属セクションの変更も許可する(要件定義3.2)。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:4000'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ];
    }

    /**
     * 別セクションへ移動する場合のみ、移動先が上限に達していないかチェックする
     * (同じセクション内に留まる場合は、この問題自身が既にカウントに含まれているのでチェック不要)。
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Question $question */
            $question = $this->route('question');
            $newSectionId = (int) $this->input('section_id');

            if ($newSectionId === $question->section_id) {
                return;
            }

            $isFull = DB::transaction(function () use ($newSectionId) {
                return Section::whereKey($newSectionId)->lockForUpdate()->first()
                    ?->questions()->count() >= config('quiz.max_questions_per_section');
            });

            if ($isFull) {
                $validator->errors()->add(
                    'section_id',
                    '移動先のセクションは既に問題が最大'.config('quiz.max_questions_per_section').'問に達しています。'
                );
            }
        });
    }
}
