<?php

namespace App\Http\Requests;

use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:4000'],
        ];
    }

    /**
     * 1セクションにつき最大config('quiz.max_questions_per_section')問という上限(要件定義3.2)を、
     * バリデーション時点でチェックする。同時に複数リクエストが来ても上限を超えないよう、
     * トランザクション内で対象セクションの行ロックを取ってから数える。
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Section $section */
            $section = $this->route('section');

            $isFull = DB::transaction(function () use ($section) {
                return Section::whereKey($section->id)->lockForUpdate()->first()
                    ->questions()->count() >= config('quiz.max_questions_per_section');
            });

            if ($isFull) {
                $validator->errors()->add(
                    'body',
                    '1セクションにつき問題は最大'.config('quiz.max_questions_per_section').'問までです。'
                );
            }
        });
    }
}
