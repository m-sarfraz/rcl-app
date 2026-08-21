@extends('layouts.admin')
@section('title', $vcc->name)
@section('page-title', 'VCC Cabinet Member')

@section('topbar-actions')
    <a href="{{ route('admin.vcc.edit', $vcc) }}" class="topbar-btn"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('admin.vcc.index') }}" class="topbar-btn"><i class="bi bi-arrow-left"></i> Cabinet</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="rcl-card">
            <div class="rcl-card-body" style="text-align:center;">
                @if($vcc->photo)
                    <img src="{{ asset('storage/'.$vcc->photo) }}" alt="{{ $vcc->name }}"
                         style="width:140px;height:140px;border-radius:50%;object-fit:cover;border:3px solid var(--rcl-primary);">
                @else
                    <div style="width:140px;height:140px;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;
                                background:linear-gradient(135deg,var(--rcl-primary),var(--rcl-gold));color:#000;font-size:2.4rem;font-weight:900;">
                        {{ collect(explode(' ', $vcc->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('') }}
                    </div>
                @endif
                <div style="font-size:1.15rem;font-weight:800;margin-top:1rem;">{{ $vcc->name }}</div>
                <div style="color:var(--rcl-gold);font-size:.85rem;font-weight:600;">{{ $vcc->role_title }}</div>
                <span class="badge-rcl {{ $vcc->is_active ? 'badge-paid' : 'badge-completed' }}" style="margin-top:.75rem;display:inline-block;">
                    {{ $vcc->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="rcl-card">
            <div class="rcl-card-header"><span><i class="bi bi-person-badge-fill"></i> Details</span></div>
            <table class="rcl-table">
                <tbody>
                @foreach([
                    'Village'       => $vcc->village,
                    'Phone'         => $vcc->phone,
                    'Display order' => $vcc->display_order,
                    'Added'         => $vcc->created_at?->format('d M Y'),
                ] as $label => $value)
                    <tr>
                        <td style="width:180px;color:var(--rcl-muted);font-size:.82rem;">{{ $label }}</td>
                        <td style="font-weight:600;">{{ $value ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if($vcc->bio)
                <div class="rcl-card-body" style="border-top:1px solid var(--rcl-border);">
                    <div style="font-size:.72rem;color:var(--rcl-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Biography</div>
                    <div style="font-size:.9rem;line-height:1.6;">{{ $vcc->bio }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
