<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostCategoryRequest;
use App\Http\Requests\UpdatePostCategoryRequest;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;

class PostCategoryController extends Controller
{
    public function store(StorePostCategoryRequest $request): RedirectResponse
    {
        PostCategory::query()->create($request->validated());

        return redirect()
            ->route('dashboard.posts.index', ['tab' => 'categories'])
            ->with('success', 'Đã thêm danh mục mới.');
    }

    public function update(UpdatePostCategoryRequest $request, PostCategory $category): RedirectResponse
    {
        $previousName = $category->name;

        $category->update($request->validated());

        if ($previousName !== $category->name) {
            Post::query()
                ->where('category', $previousName)
                ->update(['category' => $category->name]);
        }

        return redirect()
            ->route('dashboard.posts.index', ['tab' => 'categories'])
            ->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(PostCategory $category): RedirectResponse
    {
        if ($category->postsCount() > 0) {
            return redirect()
                ->route('dashboard.posts.index', ['tab' => 'categories'])
                ->with('error', 'Không thể xóa danh mục đang có bài viết.');
        }

        $category->delete();

        return redirect()
            ->route('dashboard.posts.index', ['tab' => 'categories'])
            ->with('success', 'Đã xóa danh mục.');
    }
}
