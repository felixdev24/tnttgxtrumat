<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = Webhook::orderBy('created_at', 'desc')->get();
        return Inertia::render('dashboard/webhooks/Index', [
            'webhooks' => $webhooks
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->input('is_active', true);

        Webhook::create($validated);

        return redirect()->back()->with('success', 'Đã thêm Webhook thành công!');
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $webhook->update($validated);

        return redirect()->back()->with('success', 'Cập nhật Webhook thành công!');
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        return redirect()->back()->with('success', 'Đã xóa Webhook!');
    }
}
