<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\AttendanceController;
use App\Http\Controllers\Dashboard\CalendarController;
use App\Http\Controllers\Dashboard\DoanSinhController;
use App\Http\Controllers\Dashboard\HuynhTruongController;
use App\Http\Controllers\Dashboard\PostCategoryController;
use App\Http\Controllers\Dashboard\PostController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\QuizController;
use App\Http\Controllers\Dashboard\TnttClassController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicQuizController;
use App\Models\CalendarEvent;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $posts = Post::where('status', 'published')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    $calendarEvents = CalendarEvent::whereDate('event_date', '>=', now()->startOfMonth())
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
Route::put('/account/password', [AccountController::class, 'updatePassword'])->middleware('auth')->name('account.password.update');

Route::get('/quizzes', [PublicQuizController::class, 'index'])->name('quizzes.index');
Route::get('/quizzes/leaderboard', [PublicQuizController::class, 'leaderboard'])->name('quizzes.leaderboard');
Route::get('/quizzes/{quizWeek}', [PublicQuizController::class, 'play'])->name('quizzes.play');
Route::post('/quizzes/answer', [PublicQuizController::class, 'submitAnswer'])->middleware('auth')->name('quizzes.answer');

Route::get('/hoat-dong', [PublicPostController::class, 'index'])->name('posts.index');
Route::get('/hoat-dong/{slug}', [PublicPostController::class, 'show'])->name('posts.show');

Route::middleware(['auth', 'huynh_truong'])->prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard.posts.index');
    })->name('dashboard.index');

    // Posts
    Route::get('/posts', [PostController::class, 'index'])->name('dashboard.posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('dashboard.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('dashboard.posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('dashboard.posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('dashboard.posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('dashboard.posts.destroy');
    Route::post('/upload-image', [PostController::class, 'uploadImage'])->name('dashboard.upload-image');

    // Post Categories
    Route::post('/posts/categories', [PostCategoryController::class, 'store'])->name('dashboard.posts.categories.store');
    Route::put('/posts/categories/{category}', [PostCategoryController::class, 'update'])->name('dashboard.posts.categories.update');
    Route::delete('/posts/categories/{category}', [PostCategoryController::class, 'destroy'])->name('dashboard.posts.categories.destroy');

    // Tntt Classes
    Route::get('/tntt-classes', [TnttClassController::class, 'index'])->name('dashboard.tntt-classes.index');
    Route::post('/tntt-classes', [TnttClassController::class, 'store'])->name('dashboard.tntt-classes.store');
    Route::put('/tntt-classes/{tnttClass}', [TnttClassController::class, 'update'])->name('dashboard.tntt-classes.update');
    Route::delete('/tntt-classes/{tnttClass}', [TnttClassController::class, 'destroy'])->name('dashboard.tntt-classes.destroy');

    // Quizzes
    Route::get('/quizzes', [QuizController::class, 'index'])->name('dashboard.quizzes.index');
    Route::post('/quizzes/weeks', [QuizController::class, 'storeWeek'])->name('dashboard.quizzes.weeks.store');
    Route::post('/quizzes/questions', [QuizController::class, 'storeQuestion'])->name('dashboard.quizzes.questions.store');
    Route::delete('/quizzes/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('dashboard.quizzes.questions.destroy');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('dashboard.calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('dashboard.calendar.store');
    Route::put('/calendar/{calendarEvent}', [CalendarController::class, 'update'])->name('dashboard.calendar.update');
    Route::delete('/calendar/{calendarEvent}', [CalendarController::class, 'destroy'])->name('dashboard.calendar.destroy');

    // Đoàn Sinh
    Route::get('/doan-sinh', [DoanSinhController::class, 'index'])->name('dashboard.doan-sinh.index');
    Route::post('/doan-sinh', [DoanSinhController::class, 'store'])->name('dashboard.doan-sinh.store');
    Route::put('/doan-sinh/{user}', [DoanSinhController::class, 'update'])->name('dashboard.doan-sinh.update');
    Route::delete('/doan-sinh/{user}', [DoanSinhController::class, 'destroy'])->name('dashboard.doan-sinh.destroy');
    Route::get('/doan-sinh/{user}/qr', [DoanSinhController::class, 'showQr'])->name('dashboard.doan-sinh.qr');
    Route::get('/doan-sinh/{user}/qr/download', [DoanSinhController::class, 'downloadQr'])->name('dashboard.doan-sinh.qr.download');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('dashboard.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('dashboard.profile.password.update');

    // Huynh Trưởng
    Route::middleware('super_admin')->group(function () {
        Route::get('/huynh-truong', [HuynhTruongController::class, 'index'])->name('dashboard.huynh-truong.index');
        Route::post('/huynh-truong', [HuynhTruongController::class, 'store'])->name('dashboard.huynh-truong.store');
        Route::put('/huynh-truong/{user}', [HuynhTruongController::class, 'update'])->name('dashboard.huynh-truong.update');
        Route::delete('/huynh-truong/{user}', [HuynhTruongController::class, 'destroy'])->name('dashboard.huynh-truong.destroy');
        Route::post('/huynh-truong/{user}/reset-password', [HuynhTruongController::class, 'resetPassword'])->name('dashboard.huynh-truong.reset-password');
        Route::post('/doan-sinh/{user}/reset-password', [DoanSinhController::class, 'resetPassword'])->name('dashboard.doan-sinh.reset-password');
    });

    // Điểm Danh
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('dashboard.attendance.index');
    Route::get('/attendance/stats', [AttendanceController::class, 'stats'])->name('dashboard.attendance.stats');
    Route::get('/attendance/stats/export', [AttendanceController::class, 'exportStats'])->name('dashboard.attendance.stats.export');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('dashboard.attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('dashboard.attendance.store');
    Route::get('/attendance/{session}', [AttendanceController::class, 'show'])->name('dashboard.attendance.show');
    Route::put('/attendance/{session}', [AttendanceController::class, 'update'])->name('dashboard.attendance.update');
    Route::delete('/attendance/{session}', [AttendanceController::class, 'destroy'])->name('dashboard.attendance.destroy');
    Route::get('/attendance/{session}/scan', [AttendanceController::class, 'scan'])->name('dashboard.attendance.scan');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('dashboard.attendance.check-in');
    Route::post('/attendance/{session}/manual', [AttendanceController::class, 'manualCheckIn'])->name('dashboard.attendance.manual-check-in');
    Route::get('/attendance/{session}/export', [AttendanceController::class, 'export'])->name('dashboard.attendance.export');
});

Route::fallback(function () {
    return Inertia::render('ComingSoon', [
        'title' => 'Tính năng đang phát triển',
    ]);
});
