<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HuynhTruongController extends Controller
{
    public function index(Request $request)
    {
        $query = User::huynhTruong();

        if ($request->filled('branch')) {
            $query->where('branch', $request->branch);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $huynhTruongs = $query->orderBy('branch')->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::huynhTruong()->count(),
            'by_branch' => User::huynhTruong()
                ->whereNotNull('branch')
                ->selectRaw('branch, count(*) as count')
                ->groupBy('branch')
                ->pluck('count', 'branch'),
        ];

        $branches = User::huynhTruong()->whereNotNull('branch')->distinct()->pluck('branch');

        return Inertia::render('dashboard/huynh-truong/Index', [
            'huynhTruongs' => $huynhTruongs,
            'stats' => $stats,
            'branches' => $branches,
            'filters' => $request->only(['search', 'branch']),
        ]);
    }

    public function store(Request $request)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện chức năng này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
            'branch' => 'nullable|string',
            'rank' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'years_of_activity' => 'nullable|integer|min:0',
        ]);

        $validated['role'] = 'huynh_truong';
        $validated['password'] = Hash::make('password');

        User::create($validated);

        return redirect()->route('dashboard.huynh-truong.index')->with('success', 'Thêm huynh trưởng thành công!');
    }

    public function update(Request $request, User $user)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện chức năng này.');
        }

        if ($user->role !== 'huynh_truong') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'branch' => 'nullable|string',
            'rank' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'years_of_activity' => 'nullable|integer|min:0',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Cập nhật thông tin huynh trưởng thành công!');
    }

    public function destroy(User $user)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện chức năng này.');
        }

        if ($user->role !== 'huynh_truong') {
            abort(403);
        }

        $user->delete();

        return redirect()->route('dashboard.huynh-truong.index')->with('success', 'Xóa huynh trưởng thành công!');
    }

    public function resetPassword(User $user)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện chức năng này.');
        }

        if ($user->role !== 'huynh_truong') {
            abort(403);
        }

        $user->update(['password' => Hash::make('password')]);

        return redirect()->back()->with('success', 'Đã đặt lại mật khẩu về "password".');
    }
}
