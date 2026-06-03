<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function meeting()
    {
        $content = SiteSetting::get('meeting_content', '');
        return view('admin.settings.meeting', compact('content'));
    }

    public function saveMeeting(Request $request)
    {
        $request->validate(['content' => 'nullable|string']);
        SiteSetting::set('meeting_content', $request->input('content', ''));
        return back()->with('success', 'Meeting notice updated successfully.');
    }
}
