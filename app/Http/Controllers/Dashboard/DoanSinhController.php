<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DoanSinhController extends Controller
{
    public function index(Request $request)
    {
        $query = User::doanSinh();

        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $doanSinhs = $query->orderBy('grade_level')->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::doanSinh()->count(),
            'by_grade' => User::doanSinh()
                ->whereNotNull('grade_level')
                ->selectRaw('grade_level, count(*) as count')
                ->groupBy('grade_level')
                ->pluck('count', 'grade_level'),
        ];

        return Inertia::render('dashboard/doan-sinh/Index', [
            'doanSinhs' => $doanSinhs,
            'stats' => $stats,
            'filters' => $request->only(['search', 'grade_level']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'grade_level' => 'required|string',
            'branch' => 'required|string',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $validated['role'] = 'giao_ly_sinh';
        $validated['password'] = Hash::make('password'); // Default password

        User::create($validated);

        return redirect()->route('dashboard.doan-sinh.index')->with('success', 'Thêm đoàn sinh thành công!');
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'giao_ly_sinh') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'grade_level' => 'required|string',
            'branch' => 'required|string',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Cập nhật thông tin đoàn sinh thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'giao_ly_sinh') {
            abort(403);
        }

        $user->delete();

        return redirect()->route('dashboard.doan-sinh.index')->with('success', 'Xóa đoàn sinh thành công!');
    }

    public function showQr(User $user)
    {
        if ($user->role !== 'giao_ly_sinh') {
            abort(403);
        }

        if (empty($user->qr_token)) {
            $user->qr_token = Str::random(32);
            $user->save();
        }

        return response()->json([
            'token' => $user->qr_token,
            'name' => $user->name,
        ]);
    }
}
