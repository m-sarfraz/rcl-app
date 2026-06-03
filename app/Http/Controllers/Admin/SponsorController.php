<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsors = Sponsor::orderBy('display_order')->orderBy('name')->get();
        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.sponsors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'logo'          => 'nullable|image|max:2048',
            'website'       => 'nullable|url|max:255',
            'tier'          => 'required|in:title,gold,silver,general',
            'description'   => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('sponsors', 'public');
        }

        $data['display_order'] = $data['display_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        Sponsor::create($data);
        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor added.');
    }

    public function edit(Sponsor $sponsor)
    {
        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'logo'          => 'nullable|image|max:2048',
            'website'       => 'nullable|url|max:255',
            'tier'          => 'required|in:title,gold,silver,general',
            'description'   => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($sponsor->logo) Storage::disk('public')->delete($sponsor->logo);
            $data['logo'] = $request->file('logo')->store('sponsors', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $sponsor->update($data);
        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor updated.');
    }

    public function destroy(Sponsor $sponsor)
    {
        if ($sponsor->logo) Storage::disk('public')->delete($sponsor->logo);
        $sponsor->delete();
        return back()->with('success', 'Sponsor removed.');
    }
}
