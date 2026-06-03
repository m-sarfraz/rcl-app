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

    public function scoringKey()
    {
        $key = SiteSetting::get('scoring_secret_key', '');
        return view('admin.settings.scoring-key', compact('key'));
    }

    public function saveScoringKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string|min:6|max:64',
        ]);
        SiteSetting::set('scoring_secret_key', $request->input('key'));
        return back()->with('success', 'Scoring secret key updated.');
    }
}
