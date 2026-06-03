<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount('players')->orderBy('name')->paginate(20);
        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.teams.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'village_name'    => 'required|string|max:255',
            'short_code'      => 'required|string|max:5|unique:teams',
            'logo'            => 'nullable|image|max:1024',
            'cover_photo'     => 'nullable|image|max:2048',
            'primary_color'   => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'description'     => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('teams/logos', 'public');
        }
        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')->store('teams/covers', 'public');
        }

        Team::create($data);

        return redirect()->route('admin.teams.index')->with('success', 'Team created.');
    }

    public function edit(Team $team)
    {
        return view('admin.teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'village_name'    => 'required|string|max:255',
            'short_code'      => 'required|string|max:5|unique:teams,short_code,'.$team->id,
            'logo'            => 'nullable|image|max:1024',
            'cover_photo'     => 'nullable|image|max:2048',
            'primary_color'   => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($team->logo) Storage::disk('public')->delete($team->logo);
            $data['logo'] = $request->file('logo')->store('teams/logos', 'public');
        }
        if ($request->hasFile('cover_photo')) {
            if ($team->cover_photo) Storage::disk('public')->delete($team->cover_photo);
            $data['cover_photo'] = $request->file('cover_photo')->store('teams/covers', 'public');
        }

        $team->update($data);
        return redirect()->route('admin.teams.index')->with('success', 'Team updated.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return back()->with('success', 'Team deleted.');
    }
}
