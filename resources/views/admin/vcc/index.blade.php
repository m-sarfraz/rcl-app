@extends('layouts.admin')
@section('title','VCC Cabinet')
@section('page-title','VCC Cabinet Members')
@section('topbar-actions')
<a href="{{ route('admin.vcc.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Add Member</a>
@endsection
@section('content')
<div class="row g-3">
    @forelse($members as $m)
    <div class="col-md-3 col-6">
        <div class="rcl-card text-center" style="padding:1.25rem;">
            @if($m->photo)
            <img src="{{ asset('storage/'.$m->photo) }}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid var(--rcl-primary);margin-bottom:.75rem;">
            @else
            <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--rcl-primary),var(--rcl-gold));display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;font-size:1.25rem;margin:0 auto .75rem;">{{ strtoupper(substr($m->name,0,2)) }}</div>
            @endif
            <div style="font-weight:700;font-size:.9rem;">{{ $m->name }}</div>
            <div style="font-size:.72rem;color:var(--rcl-primary);font-weight:600;margin:.2rem 0;">{{ $m->role_title }}</div>
            <div class="d-flex gap-1 justify-content-center mt-2">
                <a href="{{ route('admin.vcc.edit', $m) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                <form method="POST" action="{{ route('admin.vcc.destroy', $m) }}" onsubmit="return confirm('Remove?')">
                    @csrf @method('DELETE')
                    <button class="btn-rcl-danger btn" style="font-size:.72rem;padding:.25rem .6rem;">Del</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12" style="text-align:center;padding:3rem;color:var(--rcl-muted);">No cabinet members yet.</div>
    @endforelse
</div>
@endsection
