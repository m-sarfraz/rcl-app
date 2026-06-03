<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditionController extends Controller
{
    public function index()
    {
        $editions = Edition::withCount('teams', 'matches')->orderByDesc('edition_number')->get();
        return view('admin.editions.index', compact('editions'));
    }

    public function create()
    {
        $teams = Team::where('is_active', true)->orderBy('name')->get();
        return view('admin.editions.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'edition_number' => 'required|integer|unique:editions',
            'host_village'   => 'required|string|max:255',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'status'         => 'required|in:upcoming,active,completed,archived',
            'description'    => 'nullable|string',
            'thumbnail'      => 'nullable|image|max:2048',
            'banner'         => 'nullable|image|max:4096',
            'team_ids'       => 'nullable|array',
            'team_ids.*'     => 'exists:teams,id',
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('editions/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('editions/banners', 'public');
        }

        $edition = Edition::create($data);

        if ($request->filled('team_ids')) {
            $edition->teams()->sync($request->team_ids);
        }

        if ($request->boolean('set_as_current')) {
            Edition::where('is_current', true)->update(['is_current' => false]);
            $edition->update(['is_current' => true]);
        }

        return redirect()->route('admin.editions.index')->with('success', 'Edition created successfully.');
    }

    public function edit(Edition $edition)
    {
        $teams        = Team::where('is_active', true)->orderBy('name')->get();
        $assignedIds  = $edition->teams->pluck('id')->toArray();
        return view('admin.editions.edit', compact('edition','teams','assignedIds'));
    }

    public function update(Request $request, Edition $edition)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'edition_number' => 'required|integer|unique:editions,edition_number,'.$edition->id,
            'host_village'   => 'required|string|max:255',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'status'         => 'required|in:upcoming,active,completed,archived',
            'description'    => 'nullable|string',
            'thumbnail'      => 'nullable|image|max:2048',
            'banner'         => 'nullable|image|max:4096',
            'team_ids'       => 'nullable|array',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($edition->thumbnail) Storage::disk('public')->delete($edition->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('editions/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            if ($edition->banner) Storage::disk('public')->delete($edition->banner);
            $data['banner'] = $request->file('banner')->store('editions/banners', 'public');
        }

        $edition->update($data);
        $edition->teams()->sync($request->get('team_ids', []));

        if ($request->boolean('set_as_current')) {
            Edition::where('is_current', true)->update(['is_current' => false]);
            $edition->update(['is_current' => true]);
        }

        return redirect()->route('admin.editions.index')->with('success', 'Edition updated.');
    }

    public function destroy(Edition $edition)
    {
        $edition->delete();
        return back()->with('success', 'Edition deleted.');
    }
}
