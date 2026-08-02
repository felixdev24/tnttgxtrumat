<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\TnttClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $tnttClassId = $request->query('tntt_class_id');
        $type = $request->query('type', 'total'); // total, attendance, activity, quiz
        $period = $request->query('period', 'all'); // all, month, year
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $classes = TnttClass::orderBy('branch')->orderBy('name')->get();

        // Build the leaderboard
        $leaderboard = $this->buildLeaderboard($type, $period, $year, $month, $tnttClassId);

        // Monthly stats chart (last 6 months) for each type
        $monthlyStats = $this->buildMonthlyStats($tnttClassId);

        return Inertia::render('dashboard/leaderboard/Index', [
            'leaderboard' => $leaderboard,
            'monthlyStats' => $monthlyStats,
            'classes' => $classes,
            'filters' => [
                'tntt_class_id' => $tnttClassId,
                'type' => $type,
                'period' => $period,
                'year' => $year,
                'month' => $month,
            ],
        ]);
    }

    /**
     * @param  string|null  $tnttClassId
     */
    private function buildLeaderboard(
        string $type,
        string $period,
        int $year,
        int $month,
        ?string $tnttClassId
    ): array {
        $query = PointTransaction::selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->with(['user:id,name,username,tntt_class_id,avatar,branch', 'user.tnttClass:id,name']);

        // Filter by type
        if ($type !== 'total') {
            $query->where('type', $type);
        }

        // Filter by period
        if ($period === 'month') {
            $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        } elseif ($period === 'year') {
            $query->whereYear('created_at', $year);
        }

        // Filter by class
        if ($tnttClassId) {
            $query->whereHas('user', fn ($q) => $q->where('tntt_class_id', $tnttClassId));
        } else {
            $query->whereHas('user', fn ($q) => $q->doanSinh());
        }

        return $query->take(50)->get()->map(fn ($row, $index) => [
            'rank' => $index + 1,
            'user_id' => $row->user_id,
            'name' => $row->user?->name,
            'username' => $row->user?->username,
            'avatar' => $row->user?->avatar,
            'branch' => $row->user?->branch,
            'tntt_class_name' => $row->user?->tnttClass?->name,
            'total_points' => (int) $row->total_points,
        ])->values()->all();
    }

    /**
     * Build monthly stats for the last 6 months.
     */
    private function buildMonthlyStats(?string $tnttClassId): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'label' => 'Th.'.$date->month.'/'.$date->year,
                'year' => $date->year,
                'month' => $date->month,
            ];
        }

        $types = ['attendance', 'activity', 'quiz'];
        $result = [];

        foreach ($months as $m) {
            $entry = ['label' => $m['label']];
            foreach ($types as $t) {
                $q = PointTransaction::where('type', $t)
                    ->whereYear('created_at', $m['year'])
                    ->whereMonth('created_at', $m['month']);
                if ($tnttClassId) {
                    $q->whereHas('user', fn ($uq) => $uq->where('tntt_class_id', $tnttClassId));
                } else {
                    $q->whereHas('user', fn ($uq) => $uq->doanSinh());
                }
                $entry[$t] = (int) $q->sum('points');
            }
            $result[] = $entry;
        }

        return $result;
    }
}
