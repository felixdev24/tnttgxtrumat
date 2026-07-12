<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\CalendarEvent;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardStatsController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // --- Summary Cards ---
        $totalDoanSinh = User::doanSinh()->count();

        // Growth compared to last month
        $lastMonthCount = User::doanSinh()
            ->where('created_at', '<', $now->copy()->startOfMonth())
            ->count();
        $growthPercent = $lastMonthCount > 0
            ? round(($totalDoanSinh - $lastMonthCount) / $lastMonthCount * 100, 1)
            : 0;

        $postsThisMonth = Post::where('status', 'published')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        $upcomingEvents = CalendarEvent::whereDate('event_date', '>=', $now->toDateString())
            ->whereDate('event_date', '<=', $now->copy()->addDays(30)->toDateString())
            ->count();

        // Today's attendance rate
        $todaySession = AttendanceSession::whereDate('session_date', $now->toDateString())->first();
        $attendanceRate = 0;
        if ($todaySession) {
            $totalRecords = $todaySession->records()->count();
            $presentRecords = $todaySession->records()->whereIn('status', ['present', 'late'])->count();
            $attendanceRate = $totalRecords > 0 ? round($presentRecords / $totalRecords * 100) : 0;
        }

        // --- Growth Chart (last 6 months) ---
        $monthlyGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $count = User::doanSinh()
                ->where('created_at', '<=', $date->endOfMonth())
                ->count();
            $monthlyGrowth[] = [
                'label' => 'Th'.$date->month,
                'count' => $count,
            ];
        }

        // --- Branch Distribution (Cơ cấu Ngành) ---
        $branchDistribution = User::doanSinh()
            ->select('branch', DB::raw('count(*) as count'))
            ->whereNotNull('branch')
            ->groupBy('branch')
            ->get()
            ->map(function ($item) {
                return [
                    'branch' => $item->branch,
                    'count' => $item->count,
                ];
            })
            ->values();

        // --- Recent Activity ---
        $recentPosts = Post::with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(function ($post) {
                return [
                    'type' => 'post',
                    'user_name' => $post->user?->name ?? 'Hệ thống',
                    'user_role' => $post->user?->role === 'super_admin' ? 'Admin' : 'Huynh Trưởng',
                    'action' => ($post->status === 'published' ? 'Đã đăng' : 'Tạo nháp').': "'.$post->title.'"',
                    'category' => $post->category ?? 'Bài viết',
                    'status' => $post->status === 'published' ? 'Công khai' : 'Nháp',
                    'time' => $post->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('dashboard/Stats', [
            'summary' => [
                'totalDoanSinh' => $totalDoanSinh,
                'growthPercent' => $growthPercent,
                'postsThisMonth' => $postsThisMonth,
                'upcomingEvents' => $upcomingEvents,
                'attendanceRate' => $attendanceRate,
            ],
            'monthlyGrowth' => $monthlyGrowth,
            'branchDistribution' => $branchDistribution,
            'totalForPie' => $totalDoanSinh,
            'recentActivity' => $recentPosts,
        ]);
    }
}
