<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'message'    => 'required|string|max:500',
            'type'       => 'required|in:info,live_update,suspension,fine,announcement',
            'expires_at' => 'nullable|date|after:now',
            'is_ticker'  => 'boolean',
        ]);

        $data['created_by'] = auth()->id();
        Notification::create($data);

        return back()->with('success', 'Notification published.');
    }

    public function toggle(Notification $notification)
    {
        $notification->update(['is_active' => !$notification->is_active]);
        return back()->with('success', 'Status toggled.');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }

    public function ticker()
    {
        $items = Notification::where('is_active', true)
            ->where('is_ticker', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()
            ->limit(10)
            ->pluck('message');

        return response()->json($items);
    }
}
