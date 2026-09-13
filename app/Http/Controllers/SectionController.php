<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\Category;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    /**
     * セクション作成フォーム。カテゴリ詳細ページの「+セクションを追加」から遷移するため、
     * カテゴリはURLで固定済み(選択の必要がない)。
     */
    public function create(Category $category): View
    {
        return view('sections.create', compact('category'));
    }

    public function store(StoreSectionRequest $request, Category $category): RedirectResponse
    {
        $section = $category->sections()->create($request->validated());

        return redirect()->route('sections.show', $section)->with('status', 'セクションを作成しました。');
    }

    /**
     * セクション詳細。問題一覧+「+問題を追加」「全問に回答する」「履歴を見る」を表示する(要件定義3.2)。
     * questions/answers/history機能は次のステップ以降で実装するため、
     * ビュー側ではRoute::has()でルートの存在を確認してからボタンを出す。
     */
    public function show(Section $section): View
    {
        $section->load('category', 'questions');

        return view('sections.show', compact('section'));
    }

    public function edit(Section $section): View
    {
        // 編集画面では所属カテゴリの変更も可能にするため、選択肢としてカテゴリ一覧を渡す
        $categories = Category::orderBy('name')->get();

        return view('sections.edit', compact('section', 'categories'));
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return redirect()->route('sections.show', $section)->with('status', 'セクションを更新しました。');
    }

    /**
     * セクションを削除する。配下の問題・回答履歴・採点結果はDBの外部キーcascadeで連動削除される
     * (要件定義3.2)。削除後は所属していたカテゴリの詳細画面へ戻す。
     */
    public function destroy(Section $section): RedirectResponse
    {
        $category = $section->category;

        $section->delete();

        return redirect()->route('categories.show', $category)->with('status', 'セクションを削除しました。');
    }
}
