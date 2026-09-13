<?php

use App\Enums\GradingLevel;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Score;
use App\Models\Section;
use App\Models\User;
use App\Services\Grading\GradingService;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('セクション内の全問に一括回答すると採点されて保存される', function () {
    $section = Section::factory()->create();
    $questions = Question::factory()->for($this->user)->for($section)->count(2)->create();

    $response = $this->actingAs($this->user)->post(route('answers.store', $section), [
        'grading_level' => 'normal',
        'answers' => $questions->map(fn ($q) => ['question_id' => $q->id, 'body' => "回答{$q->id}"])->all(),
    ]);

    $response->assertOk();

    $attempt = Attempt::sole();
    expect($attempt->section_id)->toBe($section->id);
    expect($attempt->grading_level)->toBe(GradingLevel::Normal);

    $answers = Answer::with('score')->get();
    expect($answers)->toHaveCount(2);
    $answers->each(fn (Answer $answer) => expect($answer->score->score)->toBe(80));
});

test('採点レベルが不正、または回答が空だとバリデーションエラーになる', function () {
    $section = Section::factory()->create();
    $question = Question::factory()->for($this->user)->for($section)->create();

    $response = $this->actingAs($this->user)->post(route('answers.store', $section), [
        'grading_level' => 'invalid-level',
        'answers' => [['question_id' => $question->id, 'body' => '']],
    ]);

    $response->assertSessionHasErrors(['grading_level', 'answers.0.body']);
    expect(Attempt::count())->toBe(0);
});

test('他セクション・他ユーザーの問題を混ぜて送信すると拒否され、何も保存されない', function () {
    $section = Section::factory()->create();
    $ownQuestion = Question::factory()->for($this->user)->for($section)->create();
    $otherUser = User::factory()->create();
    $otherQuestion = Question::factory()->for($otherUser)->create();

    $response = $this->actingAs($this->user)->post(route('answers.store', $section), [
        'grading_level' => 'normal',
        'answers' => [
            ['question_id' => $ownQuestion->id, 'body' => '回答1'],
            ['question_id' => $otherQuestion->id, 'body' => '回答2'],
        ],
    ]);

    $response->assertForbidden();
    expect(Attempt::count())->toBe(0);
    expect(Answer::count())->toBe(0);
});

test('AI採点が失敗した場合はエラーを表示し、何も保存しない', function () {
    $this->mock(GradingService::class, function ($mock) {
        $mock->shouldReceive('grade')->once()->andThrow(new RuntimeException('Claude API error'));
    });

    $section = Section::factory()->create();
    $question = Question::factory()->for($this->user)->for($section)->create();

    $response = $this->actingAs($this->user)->post(route('answers.store', $section), [
        'grading_level' => 'normal',
        'answers' => [['question_id' => $question->id, 'body' => '回答']],
    ]);

    $response->assertSessionHasErrors('grading');
    expect(Attempt::count())->toBe(0);
    expect(Answer::count())->toBe(0);
    expect(Score::count())->toBe(0);
});

test('問題が0件のセクションで回答フォームを開くとセクション詳細へ戻される', function () {
    $section = Section::factory()->create();

    $response = $this->actingAs($this->user)->get(route('answers.create', $section));

    $response->assertRedirect(route('sections.show', $section));
});

test('未ログインでは回答フォームにアクセスできない', function () {
    $section = Section::factory()->create();

    $this->get(route('answers.create', $section))->assertRedirect('/login');
});
