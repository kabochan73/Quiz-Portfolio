<?php

use App\Enums\GradingLevel;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Score;
use App\Models\Section;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('セクションの回答履歴が新しい順に一覧表示され、平均点も表示される', function () {
    $section = Section::factory()->create();
    $question = Question::factory()->for($this->user)->for($section)->create();

    $older = Attempt::factory()->for($section)->for($this->user)->create([
        'grading_level' => GradingLevel::Easy,
        'created_at' => now()->subDay(),
    ]);
    $answer1 = Answer::factory()->for($question)->for($this->user)->for($older)->create();
    Score::factory()->for($answer1)->create(['score' => 60]);

    $newer = Attempt::factory()->for($section)->for($this->user)->create([
        'grading_level' => GradingLevel::Hard,
        'created_at' => now(),
    ]);
    $answer2 = Answer::factory()->for($question)->for($this->user)->for($newer)->create();
    Score::factory()->for($answer2)->create(['score' => 90]);

    $response = $this->actingAs($this->user)->get(route('history.index', $section));

    $response->assertOk();
    // 新しい順なので、先に90点(newer)、後に60点(older)が現れるはず
    $response->assertSeeInOrder(['平均 90点', '平均 60点']);
});

test('履歴詳細で1回分の挑戦の全問題・回答・点数・フィードバックが表示される', function () {
    $section = Section::factory()->create();
    $question = Question::factory()->for($this->user)->for($section)->create(['body' => 'これはテスト問題です']);
    $attempt = Attempt::factory()->for($section)->for($this->user)->create();
    $answer = Answer::factory()->for($question)->for($this->user)->for($attempt)->create(['body' => 'これはテスト回答です']);
    Score::factory()->for($answer)->create(['score' => 75, 'feedback' => 'よくできています']);

    $response = $this->actingAs($this->user)->get(route('history.show', [$section, $attempt]));

    $response->assertOk();
    $response->assertSee('これはテスト問題です');
    $response->assertSee('これはテスト回答です');
    $response->assertSee('75点');
    $response->assertSee('よくできています');
});

test('他のセクション・他ユーザーの挑戦の履歴詳細は見られない', function () {
    $section = Section::factory()->create();
    $otherSection = Section::factory()->create();
    $otherUser = User::factory()->create();

    $attemptOfOtherSection = Attempt::factory()->for($otherSection)->for($this->user)->create();
    $attemptOfOtherUser = Attempt::factory()->for($section)->for($otherUser)->create();

    $this->actingAs($this->user)->get(route('history.show', [$section, $attemptOfOtherSection]))->assertNotFound();
    $this->actingAs($this->user)->get(route('history.show', [$section, $attemptOfOtherUser]))->assertNotFound();
});

test('未ログインでは履歴画面にアクセスできない', function () {
    $section = Section::factory()->create();

    $this->get(route('history.index', $section))->assertRedirect('/login');
});
