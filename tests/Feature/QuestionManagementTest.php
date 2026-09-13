<?php

use App\Models\Question;
use App\Models\Section;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('セクション配下に問題を作成できる', function () {
    $section = Section::factory()->create();

    $response = $this->actingAs($this->user)->post(route('questions.store', $section), [
        'body' => 'これはテスト問題です',
    ]);

    $question = Question::sole();
    $response->assertRedirect(route('questions.show', $question));
    expect($question->section_id)->toBe($section->id);
    expect($question->user_id)->toBe($this->user->id);
});

test('問題文が空だと作成できない', function () {
    $section = Section::factory()->create();

    $response = $this->actingAs($this->user)->post(route('questions.store', $section), [
        'body' => '',
    ]);

    $response->assertSessionHasErrors('body');
    expect(Question::count())->toBe(0);
});

test('1セクションにつき上限数までは問題を作成できるが、それを超えると拒否される', function () {
    config(['quiz.max_questions_per_section' => 2]);
    $section = Section::factory()->create();

    Question::factory()->for($this->user)->for($section)->create();
    $this->actingAs($this->user)->post(route('questions.store', $section), ['body' => '2問目'])
        ->assertRedirect();
    expect($section->questions()->count())->toBe(2);

    $response = $this->actingAs($this->user)->post(route('questions.store', $section), ['body' => '3問目']);

    $response->assertSessionHasErrors('body');
    expect($section->questions()->count())->toBe(2);
});

test('問題詳細プレビューが表示される', function () {
    $question = Question::factory()->for($this->user)->create(['body' => 'これはテスト問題です']);

    $response = $this->actingAs($this->user)->get(route('questions.show', $question));

    $response->assertOk();
    $response->assertSee('これはテスト問題です');
});

test('自分の問題は編集できる', function () {
    $question = Question::factory()->for($this->user)->create();
    $newSection = Section::factory()->create();

    $response = $this->actingAs($this->user)->put(route('questions.update', $question), [
        'body' => '改訂後の問題文',
        'section_id' => $newSection->id,
    ]);

    $response->assertRedirect(route('questions.show', $question));
    expect($question->fresh()->body)->toBe('改訂後の問題文');
    expect($question->fresh()->section_id)->toBe($newSection->id);
});

test('移動先のセクションが上限に達している場合は問題を移動できない', function () {
    config(['quiz.max_questions_per_section' => 1]);
    $question = Question::factory()->for($this->user)->create();
    $fullSection = Section::factory()->create();
    Question::factory()->for($fullSection)->create();

    $response = $this->actingAs($this->user)->put(route('questions.update', $question), [
        'body' => $question->body,
        'section_id' => $fullSection->id,
    ]);

    $response->assertSessionHasErrors('section_id');
    expect($question->fresh()->section_id)->not->toBe($fullSection->id);
});

test('自分の問題は削除できる', function () {
    $question = Question::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->delete(route('questions.destroy', $question));

    $response->assertRedirect(route('sections.show', $question->section));
    expect(Question::find($question->id))->toBeNull();
});

test('他ユーザーの問題は編集・削除できない', function () {
    $owner = User::factory()->create();
    $question = Question::factory()->for($owner)->create();

    $this->actingAs($this->user)->get(route('questions.edit', $question))->assertForbidden();
    $this->actingAs($this->user)->put(route('questions.update', $question), ['body' => 'x', 'section_id' => $question->section_id])
        ->assertForbidden();
    $this->actingAs($this->user)->delete(route('questions.destroy', $question))->assertForbidden();
    expect(Question::find($question->id))->not->toBeNull();
});

test('未ログインでは問題管理画面にアクセスできない', function () {
    $question = Question::factory()->create();

    $this->get(route('questions.show', $question))->assertRedirect('/login');
});
