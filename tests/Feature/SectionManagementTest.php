<?php

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\Question;
use App\Models\Score;
use App\Models\Section;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('カテゴリ配下にセクションを作成できる', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->user)->post(route('sections.store', $category), [
        'name' => '第1章',
    ]);

    $section = Section::sole();
    $response->assertRedirect(route('sections.show', $section));
    expect($section->category_id)->toBe($category->id);
    expect($section->name)->toBe('第1章');
});

test('セクション名が空だと作成できない', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->user)->post(route('sections.store', $category), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
    expect(Section::count())->toBe(0);
});

test('セクション詳細に問題一覧が表示される', function () {
    $section = Section::factory()->create(['name' => '第1章']);
    $question = Question::factory()->for($this->user)->for($section)->create(['body' => 'これはテスト問題です']);

    $response = $this->actingAs($this->user)->get(route('sections.show', $section));

    $response->assertOk();
    $response->assertSee('第1章');
    $response->assertSee($question->excerpt());
});

test('セクション名と所属カテゴリを編集できる', function () {
    $originalCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();
    $section = Section::factory()->for($originalCategory)->create();

    $response = $this->actingAs($this->user)->put(route('sections.update', $section), [
        'name' => '改訂版',
        'category_id' => $newCategory->id,
    ]);

    $response->assertRedirect(route('sections.show', $section));
    expect($section->fresh()->name)->toBe('改訂版');
    expect($section->fresh()->category_id)->toBe($newCategory->id);
});

test('セクションを削除すると配下の問題・履歴・採点結果も連動して削除される', function () {
    $section = Section::factory()->create();
    $question = Question::factory()->for($this->user)->for($section)->create();
    $attempt = Attempt::factory()->for($section)->for($this->user)->create();
    $answer = Answer::factory()->for($question)->for($this->user)->for($attempt)->create();
    $score = Score::factory()->for($answer)->create();

    $response = $this->actingAs($this->user)->delete(route('sections.destroy', $section));

    $response->assertRedirect(route('categories.show', $section->category));
    expect(Section::find($section->id))->toBeNull();
    expect(Question::find($question->id))->toBeNull();
    expect(Attempt::find($attempt->id))->toBeNull();
    expect(Answer::find($answer->id))->toBeNull();
    expect(Score::find($score->id))->toBeNull();
});

test('未ログインではセクション管理画面にアクセスできない', function () {
    $section = Section::factory()->create();

    $this->get(route('sections.show', $section))->assertRedirect('/login');
});
