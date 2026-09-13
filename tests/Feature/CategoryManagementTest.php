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

test('カテゴリ一覧が表示される', function () {
    Category::factory()->create(['name' => '数学']);
    Category::factory()->create(['name' => '英語']);

    $response = $this->actingAs($this->user)->get(route('categories.index'));

    $response->assertOk();
    $response->assertSee('数学');
    $response->assertSee('英語');
});

test('カテゴリを作成できる', function () {
    $response = $this->actingAs($this->user)->post(route('categories.store'), [
        'name' => '物理',
    ]);

    $category = Category::sole();
    $response->assertRedirect(route('categories.show', $category));
    expect($category->name)->toBe('物理');
});

test('カテゴリ名が空だと作成できない', function () {
    $response = $this->actingAs($this->user)->post(route('categories.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
    expect(Category::count())->toBe(0);
});

test('カテゴリ詳細が表示される', function () {
    $category = Category::factory()->create(['name' => '数学']);

    $response = $this->actingAs($this->user)->get(route('categories.show', $category));

    $response->assertOk();
    $response->assertSee('数学');
});

test('カテゴリを編集できる', function () {
    $category = Category::factory()->create(['name' => '数学']);

    $response = $this->actingAs($this->user)->put(route('categories.update', $category), [
        'name' => '数学(改題)',
    ]);

    $response->assertRedirect(route('categories.show', $category));
    expect($category->fresh()->name)->toBe('数学(改題)');
});

test('カテゴリを削除すると配下のセクション・問題・履歴・採点結果も連動して削除される', function () {
    $category = Category::factory()->create();
    $section = Section::factory()->for($category)->create();
    $question = Question::factory()->for($this->user)->for($section)->create();
    $attempt = Attempt::factory()->for($section)->for($this->user)->create();
    $answer = Answer::factory()->for($question)->for($this->user)->for($attempt)->create();
    $score = Score::factory()->for($answer)->create();

    $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    expect(Category::find($category->id))->toBeNull();
    expect(Section::find($section->id))->toBeNull();
    expect(Question::find($question->id))->toBeNull();
    expect(Attempt::find($attempt->id))->toBeNull();
    expect(Answer::find($answer->id))->toBeNull();
    expect(Score::find($score->id))->toBeNull();
});

test('未ログインではカテゴリ管理画面にアクセスできない', function () {
    $category = Category::factory()->create();

    $this->get(route('categories.index'))->assertRedirect('/login');
    $this->get(route('categories.show', $category))->assertRedirect('/login');
});
