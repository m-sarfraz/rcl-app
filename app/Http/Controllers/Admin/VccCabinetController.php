<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VccCabinet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VccCabinetController extends Controller
{
    public function index()
    {
        $members = VccCabinet::orderBy('display_order')->get();
        return view('admin.vcc.index', compact('members'));
    }

    public function create()
    {
        return view('admin.vcc.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'role_title'    => 'required|string|max:255',
            'bio'           => 'nullable|string',
            'photo'         => 'nullable|image|max:2048',
            'phone'         => 'nullable|string|max:20',
            'village'       => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('vcc/photos', 'public');
        }

        $data['display_order'] = $data['display_order'] ?? 0;
        VccCabinet::create($data);
        return redirect()->route('admin.vcc.index')->with('success', 'Cabinet member added.');
    }

    public function show(VccCabinet $vcc)
    {
        return view('admin.vcc.show', compact('vcc'));
    }

    public function edit(VccCabinet $vcc)
    {
        return view('admin.vcc.edit', compact('vcc'));
    }

    public function update(Request $request, VccCabinet $vcc)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'role_title'    => 'required|string|max:255',
            'bio'           => 'nullable|string',
            'photo'         => 'nullable|image|max:2048',
            'phone'         => 'nullable|string|max:20',
            'village'       => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($vcc->photo) Storage::disk('public')->delete($vcc->photo);
            $data['photo'] = $request->file('photo')->store('vcc/photos', 'public');
        }

        $vcc->update($data);
        return redirect()->route('admin.vcc.index')->with('success', 'Member updated.');
    }

    public function destroy(VccCabinet $vcc)
    {
        if ($vcc->photo) Storage::disk('public')->delete($vcc->photo);
        $vcc->delete();
        return back()->with('success', 'Member removed.');
    }
}
