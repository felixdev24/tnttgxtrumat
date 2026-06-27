<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceSession::withCount(['records as total_records', 'presentMembers as present_records'])->latest('session_date');

        if ($request->filled('grade_level')) {
            $query->byGradeLevel($request->grade_level);
        }

        $sessions = $query->paginate(20)->withQueryString();

        $stats = [
            'total_sessions' => AttendanceSession::count(),
            'avg_attendance' => AttendanceSession::count() > 0
                ? round(AttendanceRecord::where('status', 'present')->count() / max(AttendanceRecord::count(), 1) * 100, 1)
                : 0,
            // Example additional stat: upcoming sessions
            'upcoming_sessions' => AttendanceSession::where('status', 'upcoming')->count(),
        ];

        $gradeLevels = User::doanSinh()->whereNotNull('grade_level')->distinct()->pluck('grade_level');

        return Inertia::render('dashboard/attendance/Index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'gradeLevels' => $gradeLevels,
            'filters' => $request->only(['grade_level']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'session_date' => 'required|date',
            'grade_level' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'upcoming';
        $validated['created_by'] = $request->user()->id;

        $session = AttendanceSession::create($validated);

        // Auto create records for the grade level
        $doanSinhs = User::doanSinh()->where('grade_level', $validated['grade_level'])->get();
        $recordsData = $doanSinhs->map(function ($ds) use ($session) {
            return [
                'attendance_session_id' => $session->id,
                'user_id' => $ds->id,
                'status' => 'absent',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (! empty($recordsData)) {
            AttendanceRecord::insert($recordsData);
        }

        return redirect()->route('dashboard.attendance.index')->with('success', 'Tạo phiên điểm danh thành công!');
    }

    public function show(AttendanceSession $session)
    {
        $session->load('creator');

        $records = $session->records()->with('user')->get()->map(function ($record) {
            return [
                'id' => $record->id,
                'user_id' => $record->user_id,
                'status' => $record->status,
                'notes' => $record->notes,
                'user' => [
                    'name' => $record->user->name,
                    'username' => $record->user->username,
                    'avatar' => $record->user->avatar,
                ],
            ];
        });

        return Inertia::render('dashboard/attendance/Show', [
            'session' => $session,
            'records' => $records,
            'attendanceRate' => $session->attendanceRate(),
        ]);
    }

    public function update(Request $request, AttendanceSession $session)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'session_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:upcoming,in_progress,completed',
        ]);

        $session->update($validated);

        return redirect()->back()->with('success', 'Cập nhật phiên điểm danh thành công!');
    }

    public function destroy(AttendanceSession $session)
    {
        $session->delete();

        return redirect()->route('dashboard.attendance.index')->with('success', 'Xóa phiên điểm danh thành công!');
    }

    public function scan(AttendanceSession $session)
    {
        if ($session->status === 'completed') {
            return redirect()->route('dashboard.attendance.show', $session)->with('error', 'Phiên điểm danh đã kết thúc.');
        }

        if ($session->status === 'upcoming') {
            $session->update(['status' => 'in_progress']);
        }

        return Inertia::render('dashboard/attendance/Scan', [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'grade_level' => $session->grade_level,
            ],
            'csrf_token' => csrf_token(),
        ]);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:attendance_sessions,id',
            'qr_token' => 'required|string',
        ]);

        $user = User::where('qr_token', $request->qr_token)->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Mã QR không hợp lệ.'], 404);
        }

        $session = AttendanceSession::find($request->session_id);

        if ($session->grade_level && $user->grade_level !== $session->grade_level) {
            return response()->json(['success' => false, 'message' => 'Đoàn sinh không thuộc lớp này.'], 403);
        }

        $record = AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $record) {
            // In case they weren't added initially, add them now
            $record = AttendanceRecord::create([
                'attendance_session_id' => $session->id,
                'user_id' => $user->id,
                'status' => 'absent',
            ]);
        }

        if ($record->status === 'present') {
            return response()->json([
                'success' => true,
                'already_checked_in' => true,
                'user' => [
                    'name' => $user->name,
                    'grade_level' => $user->grade_level,
                ],
            ]);
        }

        $record->update([
            'status' => 'present',
            'checked_in_at' => now(),
            'checked_in_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'grade_level' => $user->grade_level,
            ],
        ]);
    }

    public function manualCheckIn(Request $request, AttendanceSession $session)
    {
        $request->validate([
            'record_id' => 'required|exists:attendance_records,id',
            'status' => 'required|in:present,absent,late,excused',
        ]);

        $record = AttendanceRecord::where('id', $request->record_id)
            ->where('attendance_session_id', $session->id)
            ->firstOrFail();

        $data = [
            'status' => $request->status,
        ];

        if ($request->status === 'present' && ! $record->checked_in_at) {
            $data['checked_in_at'] = now();
            $data['checked_in_by'] = $request->user()->id;
        }

        $record->update($data);

        return redirect()->back();
    }

    public function stats(Request $request)
    {
        $gradeLevels = User::doanSinh()->whereNotNull('grade_level')->distinct()->pluck('grade_level');
        $selectedGrade = $request->query('grade_level');

        // Simple stats for demonstration. In a real app, this would be more complex.
        $topMembersQuery = AttendanceRecord::where('status', 'present')
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->with('user:id,name,grade_level')
            ->take(10);

        if ($selectedGrade) {
            $topMembersQuery->whereHas('user', function ($q) use ($selectedGrade) {
                $q->where('grade_level', $selectedGrade);
            });
        }

        $topMembers = $topMembersQuery->get();

        return Inertia::render('dashboard/attendance/Stats', [
            'topMembers' => $topMembers,
            'gradeLevels' => $gradeLevels,
            'filters' => $request->only(['grade_level']),
        ]);
    }
}
