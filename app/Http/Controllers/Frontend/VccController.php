<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VccCabinet;

class VccController extends Controller
{
    public function index()
    {
        $members = VccCabinet::where('is_active', true)->orderBy('display_order')->get();
        return view('frontend.vcc', compact('members'));
    }
}
