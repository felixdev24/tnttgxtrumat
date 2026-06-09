<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\PostCategoryController;
use App\Http\Controllers\Dashboard\PostController;
use App\Http\Controllers\Dashboard\QuizController;
use App\Http\Controllers\Dashboard\CalendarController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicQuizController;
use Illuminate\Support\Facades\Route;

use App\Models\Post;
use Inertia\Inertia;

Route::get('/', function () {
    $posts = Post::where('status', 'published')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    $calendarEvents = \App\Models\CalendarEvent::whereDate('event_date', '>=', now()->startOfMonth())
        ->whereDate('event_date', '<=', now()->addMonths(2)->endOfMonth())
        ->orderBy('event_date')
        ->get();

    return Inertia::render('Welcome', [
        'title' => 'Trang chủ',
        'posts' => $posts,
        'calendarEvents' => $calendarEvents,
    ]);
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/quizzes', [PublicQuizController::class, 'index'])->name('quizzes.index');
Route::get('/quizzes/{quizWeek}', [PublicQuizController::class, 'play'])->name('quizzes.play');
Route::post('/quizzes/answer', [PublicQuizController::class, 'submitAnswer'])->middleware('auth')->name('quizzes.answer');

Route::get('/hoat-dong', [PublicPostController::class, 'index'])->name('posts.index');
Route::get('/hoat-dong/{slug}', [PublicPostController::class, 'show'])->name('posts.show');

Route::middleware(['auth', 'huynh_truong'])->prefix('dashboard')->group(function () {
    Route::get('/', function () { return redirect()->route('dashboard.posts.index'); })->name('dashboard.index');
    Route::get('/posts', [PostController::class, 'index'])->name('dashboard.posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('dashboard.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('dashboard.posts.store');
    Route::post('/posts/categories', [PostCategoryController::class, 'store'])->name('dashboard.posts.categories.store');
    Route::put('/posts/categories/{category}', [PostCategoryController::class, 'update'])->name('dashboard.posts.categories.update');
    Route::delete('/posts/categories/{category}', [PostCategoryController::class, 'destroy'])->name('dashboard.posts.categories.destroy');

    Route::get('/quizzes', [QuizController::class, 'index'])->name('dashboard.quizzes.index');
    Route::post('/quizzes/weeks', [QuizController::class, 'storeWeek'])->name('dashboard.quizzes.weeks.store');
    Route::post('/quizzes/questions', [QuizController::class, 'storeQuestion'])->name('dashboard.quizzes.questions.store');
    Route::delete('/quizzes/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('dashboard.quizzes.questions.destroy');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('dashboard.calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('dashboard.calendar.store');
    Route::put('/calendar/{calendarEvent}', [CalendarController::class, 'update'])->name('dashboard.calendar.update');
    Route::delete('/calendar/{calendarEvent}', [CalendarController::class, 'destroy'])->name('dashboard.calendar.destroy');
});

Route::fallback(function () {
    return Inertia::render('ComingSoon', [
        'title' => 'Tính năng đang phát triển'
    ]);
});
