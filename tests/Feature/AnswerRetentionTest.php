<?php

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\User;
use App\Services\Answers\AnswerRetentionService;

test('同一問題の回答は保持件数を超えると古いものから削除される', function () {
    $user = User::factory()->create();
    $question = Question::factory()->for($user)->create();

    // keep=3件に絞って、5件回答したうち古い2件が消えることを検証する
    $retention = new AnswerRetentionService(keep: 3);

    $answers = collect(range(1, 5))->map(
        fn () => Answer::factory()->for($question)->for($user)->create()
    );

    $retention->pruneOldAnswers($question, $user);

    $remaining = $question->answers()->orderByDesc('created_at')->orderByDesc('id')->get();
    expect($remaining)->toHaveCount(3);
    expect($remaining->pluck('id')->all())->toBe($answers->reverse()->take(3)->pluck('id')->all());
});

test('保持件数を超えて削除された結果、回答が0件になった挑戦(Attempt)も削除される', function () {
    $user = User::factory()->create();
    $question = Question::factory()->for($user)->create();
    $retention = new AnswerRetentionService(keep: 1);

    // 1問1答のattemptを2つ作る。古い方(attempt1)の回答が削除されればattempt自体も消えるはず
    $attempt1 = Attempt::factory()->for($question->section)->for($user)->create();
    Answer::factory()->for($question)->for($user)->for($attempt1)->create();

    $attempt2 = Attempt::factory()->for($question->section)->for($user)->create();
    Answer::factory()->for($question)->for($user)->for($attempt2)->create();

    $retention->pruneOldAnswers($question, $user);

    expect(Attempt::find($attempt1->id))->toBeNull();
    expect(Attempt::find($attempt2->id))->not->toBeNull();
});

test('同じ挑戦に他の問題の回答が残っていれば、挑戦自体は削除されない', function () {
    $user = User::factory()->create();
    $questionA = Question::factory()->for($user)->create();
    $questionB = Question::factory()->for($user)->for($questionA->section)->create();
    $retention = new AnswerRetentionService(keep: 1);

    // 1つのattemptにquestionAとquestionBの回答をそれぞれ2件ずつぶら下げる
    $attempt = Attempt::factory()->for($questionA->section)->for($user)->create();
    $staleAnswerA = Answer::factory()->for($questionA)->for($user)->for($attempt)->create();
    Answer::factory()->for($questionA)->for($user)->for($attempt)->create();
    Answer::factory()->for($questionB)->for($user)->for($attempt)->create();

    // questionAだけ保持件数を超えさせる(questionBの回答はattemptにまだ残る)
    $retention->pruneOldAnswers($questionA, $user);

    expect(Answer::find($staleAnswerA->id))->toBeNull();
    expect(Attempt::find($attempt->id))->not->toBeNull();
});
