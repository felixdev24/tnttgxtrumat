<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TnttClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TnttClassController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $branch = $request->query('branch');

        $query = TnttClass::withCount('users');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($branch) {
            $query->where('branch', $branch);
        }

        $classes = $query->orderBy('branch')->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('dashboard/tntt-classes/Index', [
            'classes' => $classes,
            'filters' => [
                'search' => $search,
                'branch' => $branch,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tntt_classes,name',
            'branch' => 'required|string|max:255',
        ]);

        TnttClass::create($validated);

        return back()->with('success', 'Đã thêm lớp thành công.');
    }

    public function update(Request $request, TnttClass $tnttClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tntt_classes,name,'.$tnttClass->id,
            'branch' => 'required|string|max:255',
        ]);

        $tnttClass->update($validated);

        return back()->with('success', 'Đã cập nhật lớp thành công.');
    }

    public function destroy(TnttClass $tnttClass)
    {
        if ($tnttClass->users()->count() > 0 || $tnttClass->attendanceSessions()->count() > 0) {
            return back()->with('error', 'Không thể xóa lớp đang có đoàn sinh hoặc buổi điểm danh.');
        }

        $tnttClass->delete();

        return back()->with('success', 'Đã xóa lớp thành công.');
    }
}
