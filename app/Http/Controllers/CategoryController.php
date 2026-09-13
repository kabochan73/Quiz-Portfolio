<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * カテゴリ一覧。ログイン後の着地点でありアプリの起点(要件定義5章)。
     * 一覧では各カテゴリのセクション数も見せたいので、withCountでまとめて取得する。
     */
    public function index(): View
    {
        $categories = Category::withCount('sections')->latest()->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('categories.show', $category)->with('status', 'カテゴリを作成しました。');
    }

    /**
     * カテゴリ詳細。中のセクション一覧+「+セクションを追加」を表示する(要件定義3.2)。
     * セクション機能は次のステップで実装するため、ビュー側では
     * セクションが0件の状態でも問題なく表示できるようにしておく。
     */
    public function show(Category $category): View
    {
        $category->load('sections');

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.show', $category)->with('status', 'カテゴリを更新しました。');
    }

    /**
     * カテゴリを削除する。配下のセクション・問題・回答履歴・採点結果は
     * DBの外部キーcascadeで連動削除される(要件定義3.2)。
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'カテゴリを削除しました。');
    }
}
