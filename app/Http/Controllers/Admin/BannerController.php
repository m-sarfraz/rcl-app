<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('display_order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'nullable|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'image'         => 'required|image|max:4096',
            'link_url'      => 'nullable|url|max:500',
            'display_order' => 'required|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $data['image_path'] = $request->file('image')->store('banners', 'public');
        unset($data['image']);

        Banner::create($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'         => 'nullable|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'image'         => 'nullable|image|max:4096',
            'link_url'      => 'nullable|url|max:500',
            'display_order' => 'required|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }
        unset($data['image']);

        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();
        return back()->with('success', 'Banner deleted.');
    }
}
