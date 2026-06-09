<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('user')->latest()->paginate(10);

        $categories = PostCategory::query()
            ->orderBy('name')
            ->get()
            ->map(fn (PostCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'posts_count' => $category->postsCount(),
            ]);

        $tab = $request->query('tab', 'posts');

        if (! in_array($tab, ['posts', 'categories'], true)) {
            $tab = 'posts';
        }

        return Inertia::render('dashboard/posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'tab' => $tab,
        ]);
    }

    public function create()
    {
        return Inertia::render('dashboard/posts/Create', [
            'categories' => PostCategory::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('posts/covers', 'public');
        }

        $request->user()->posts()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? '',
            'cover_image' => $coverImagePath,
            'status' => $validated['status'],
            'category' => $validated['category'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        $message = $validated['status'] === 'published'
            ? 'Bài viết đã được đăng thành công!'
            : 'Bản nháp đã được lưu thành công!';

        return redirect()->route('dashboard.posts.index')->with('success', $message);
    }
}
