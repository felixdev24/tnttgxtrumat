<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\TnttClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceSession::withCount(['records as total_records', 'presentMembers as present_records'])->latest('session_date');

        if ($request->filled('tntt_class_id')) {
            $query->byTnttClass($request->tntt_class_id);
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

        $classes = TnttClass::orderBy('branch')->orderBy('name')->get();

        return Inertia::render('dashboard/attendance/Index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'classes' => $classes,
            'filters' => $request->only(['tntt_class_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'session_date' => 'required|date',
            'tntt_class_id' => 'required|exists:tntt_classes,id',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'upcoming';
        $validated['created_by'] = $request->user()->id;

        $session = AttendanceSession::create($validated);

        // Auto create records for the class
        $doanSinhs = User::doanSinh()->where('tntt_class_id', $validated['tntt_class_id'])->get();
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
        $session->load(['creator', 'tnttClass']);

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

        $session->load('tnttClass');

        return Inertia::render('dashboard/attendance/Scan', [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'tntt_class_id' => $session->tntt_class_id,
                'tntt_class_name' => $session->tnttClass ? $session->tnttClass->name : '',
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

        $user = User::with('tnttClass')->where('qr_token', $request->qr_token)->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Mã QR không hợp lệ.'], 404);
        }

        $session = AttendanceSession::find($request->session_id);

        if ($session->tntt_class_id && $user->tntt_class_id !== $session->tntt_class_id) {
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
                    'tntt_class_name' => $user->tnttClass ? $user->tnttClass->name : '',
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
                'tntt_class_name' => $user->tnttClass ? $user->tnttClass->name : '',
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
        $classes = TnttClass::orderBy('branch')->orderBy('name')->get();
        $selectedClass = $request->query('tntt_class_id');

        // Simple stats for demonstration. In a real app, this would be more complex.
        $topMembersQuery = AttendanceRecord::where('status', 'present')
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->with(['user:id,name,tntt_class_id', 'user.tnttClass:id,name'])
            ->take(10);

        if ($selectedClass) {
            $topMembersQuery->whereHas('user', function ($q) use ($selectedClass) {
                $q->where('tntt_class_id', $selectedClass);
            });
        }

        $topMembers = $topMembersQuery->get();

        return Inertia::render('dashboard/attendance/Stats', [
            'topMembers' => $topMembers,
            'classes' => $classes,
            'filters' => $request->only(['tntt_class_id']),
        ]);
    }

    public function export(AttendanceSession $session)
    {
        $session->load(['records.user']);

        $csvFileName = 'Diem_Danh_'.date('Y_m_d_H_i_s').'.csv';
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$csvFileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($session) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8 Excel support
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, ['STT', 'Họ Tên', 'Lớp', 'SĐT', 'Trạng Thái', 'Giờ Điểm Danh', 'Ghi Chú']);

            $i = 1;
            foreach ($session->records as $record) {
                $statusMap = [
                    'present' => 'Có mặt',
                    'absent' => 'Vắng mặt',
                    'late' => 'Đi trễ',
                    'excused' => 'Có phép',
                ];
                $statusText = $statusMap[$record->status] ?? $record->status;

                fputcsv($file, [
                    $i++,
                    $record->user->name ?? '',
                    $record->user->tnttClass->name ?? '',
                    $record->user->parent_phone ?? $record->user->phone ?? '',
                    $statusText,
                    $record->checked_in_at ? Carbon::parse($record->checked_in_at)->format('H:i') : '',
                    $record->notes ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportStats(Request $request)
    {
        $classFilter = $request->query('tntt_class_id');

        // Get all doan sinh, optionally filtered by class
        $doanSinhQuery = User::doanSinh()->with('tnttClass');
        if ($classFilter) {
            $doanSinhQuery->where('tntt_class_id', $classFilter);
        }
        $doanSinhs = $doanSinhQuery->orderBy('tntt_class_id')->orderBy('name')->get();

        // Get all completed/in-progress sessions, optionally filtered by class
        $sessionQuery = AttendanceSession::with('tnttClass')
            ->whereIn('status', ['completed', 'in_progress'])
            ->orderBy('session_date');
        if ($classFilter) {
            $sessionQuery->where('tntt_class_id', $classFilter);
        }
        $sessions = $sessionQuery->get();

        // Build attendance matrix
        $records = AttendanceRecord::whereIn('attendance_session_id', $sessions->pluck('id'))
            ->whereIn('user_id', $doanSinhs->pluck('id'))
            ->get()
            ->groupBy(function ($r) {
                return $r->user_id.'_'.$r->attendance_session_id;
            });

        $csvFileName = 'Bao_Cao_Chuyen_Can_'.date('Y_m_d').'.csv';
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$csvFileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($doanSinhs, $sessions, $records) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM for UTF-8

            // Header row
            $headerRow = ['STT', 'Họ Tên', 'Lớp', 'SĐT Phụ Huynh'];
            foreach ($sessions as $session) {
                $headerRow[] = Carbon::parse($session->session_date)->format('d/m');
            }
            $headerRow[] = 'Có mặt';
            $headerRow[] = 'Tổng buổi';
            $headerRow[] = 'Tỷ lệ (%)';
            fputcsv($file, $headerRow);

            $statusMap = [
                'present' => 'O',
                'absent' => 'X',
                'late' => 'T',
                'excused' => 'P',
            ];

            $i = 1;
            foreach ($doanSinhs as $ds) {
                $row = [
                    $i++,
                    $ds->name,
                    $ds->tnttClass->name ?? '',
                    $ds->parent_phone ?? $ds->phone ?? '',
                ];

                $presentCount = 0;
                $totalSessions = 0;

                foreach ($sessions as $session) {
                    $key = $ds->id.'_'.$session->id;
                    $record = $records->get($key)?->first();

                    if ($record) {
                        $row[] = $statusMap[$record->status] ?? $record->status;
                        $totalSessions++;
                        if (in_array($record->status, ['present', 'late'])) {
                            $presentCount++;
                        }
                    } else {
                        $row[] = '-';
                    }
                }

                $row[] = $presentCount;
                $row[] = $totalSessions;
                $row[] = $totalSessions > 0 ? round($presentCount / $totalSessions * 100, 1) : 0;

                fputcsv($file, $row);
            }

            // Legend row
            fputcsv($file, []);
            fputcsv($file, ['Chú thích:', 'O = Có mặt', 'X = Vắng', 'T = Đi trễ', 'P = Có phép', '- = Không áp dụng']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
