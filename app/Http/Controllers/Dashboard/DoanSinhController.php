<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TnttClass;
use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DoanSinhController extends Controller
{
    public function index(Request $request)
    {
        $query = User::doanSinh()->with('tnttClass');

        if ($request->filled('tntt_class_id')) {
            $query->where('tntt_class_id', $request->tntt_class_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $doanSinhs = $query->orderBy('tntt_class_id')->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::doanSinh()->count(),
            'by_grade' => User::doanSinh()
                ->whereNotNull('tntt_class_id')
                ->selectRaw('tntt_class_id, count(*) as count')
                ->groupBy('tntt_class_id')
                ->pluck('count', 'tntt_class_id'),
        ];

        $classes = TnttClass::orderBy('branch')->orderBy('name')->get();

        return Inertia::render('dashboard/doan-sinh/Index', [
            'doanSinhs' => $doanSinhs,
            'stats' => $stats,
            'classes' => $classes,
            'filters' => $request->only(['search', 'tntt_class_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'tntt_class_id' => 'required|exists:tntt_classes,id',
            'branch' => 'nullable|string',
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
            'tntt_class_id' => 'required|exists:tntt_classes,id',
            'branch' => 'nullable|string',
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

        $options = new QROptions([
            'version' => 5,
            'outputType' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::L,
            'addQuietzone' => true,
            'outputBase64' => false,
        ]);

        $qrcode = new QRCode($options);
        $svg = $qrcode->render($user->qr_token);

        return response()->json([
            'svg' => $svg,
            'token' => $user->qr_token,
            'name' => $user->name,
            'tntt_class_name' => $user->tnttClass ? $user->tnttClass->name : '',
        ]);
    }

    public function resetPassword(User $user)
    {
        if ($user->role !== 'giao_ly_sinh') {
            abort(403);
        }

        $user->update([
            'password' => Hash::make('password'),
        ]);

        return back()->with('success', 'Đã đặt lại mật khẩu thành mặc định (password).');
    }
}
