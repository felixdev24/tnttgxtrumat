<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Post::where('status', 'published')
            ->with('user:id,name,avatar')
            ->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();

        $categories = Post::where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return Inertia::render('posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => [
                'category' => $request->input('category'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function show($slug): Response
    {
        $post = Post::where('status', 'published')
            ->where('slug', $slug)
            ->with('user:id,name,avatar')
            ->firstOrFail();

        return Inertia::render('posts/Show', [
            'post' => $post,
        ]);
    }
}
