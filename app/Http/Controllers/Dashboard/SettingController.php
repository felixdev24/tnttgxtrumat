<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get();

        return Inertia::render('dashboard/settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated['settings'] as $item) {
            Setting::set($item['key'], $item['value'] ?? '');
        }

        return back()->with('success', 'Cài đặt đã được lưu thành công!');
    }
}
