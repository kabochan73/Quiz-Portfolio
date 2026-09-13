<?php

namespace App\Http\Requests;

use App\Enums\GradingLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAnswerBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * セクション内の全問題への一括回答+採点レベルを検証する(要件定義3.3)。
     * 送信されたquestion_idが本当に自分の・このセクションの問題かどうかは、
     * ここではなくコントローラ側でDBを引いて検証する(403で弾く)。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grading_level' => ['required', new Enum(GradingLevel::class)],
            'answers' => ['required', 'array', 'min:1', 'max:'.config('quiz.max_questions_per_section')],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.body' => ['required', 'string', 'max:8000'],
        ];
    }
}
