<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'categories' => PostCategory::query()->orderBy('name')->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]),
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

    public function edit(Post $post)
    {
        return Inertia::render('dashboard/posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'status' => $post->status,
                'category' => $post->category,
                'tags' => $post->tags ?? [],
                'cover_image' => $post->cover_image ? Storage::url($post->cover_image) : null,
                'scheduled_at' => $post->scheduled_at?->format('Y-m-d\TH:i'),
            ],
            'categories' => PostCategory::query()->orderBy('name')->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]),
        ]);
    }

    public function update(StorePostRequest $request, Post $post)
    {
        $validated = $request->validated();

        // Regenerate slug only if title changed
        if ($post->title !== $validated['title']) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;

            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $originalSlug.'-'.$counter++;
            }

            $post->slug = $slug;
        }

        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $post->cover_image = $request->file('cover_image')->store('posts/covers', 'public');
        }

        $post->fill([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'status' => $validated['status'],
            'category' => $validated['category'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        $post->save();

        $message = $validated['status'] === 'published'
            ? 'Bài viết đã được cập nhật và đăng thành công!'
            : 'Bài viết đã được cập nhật thành công!';

        return redirect()->route('dashboard.posts.index')->with('success', $message);
    }

    public function destroy(Post $post)
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->delete();

        return redirect()->route('dashboard.posts.index')->with('success', 'Đã xóa bài viết.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $path = $request->file('image')->store('uploads/editor', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}
