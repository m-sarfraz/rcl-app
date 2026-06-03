@extends('layouts.admin')
@section('title','Sponsors')
@section('page-title','Sponsors')
@section('topbar-actions')
<a href="{{ route('admin.sponsors.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Add Sponsor</a>
@endsection
@section('content')
@php
$tiers = ['title'=>['label'=>'Title Sponsor','color'=>'#D4900A'],
          'gold'=>['label'=>'Gold','color'=>'#D4900A'],
          'silver'=>['label'=>'Silver','color'=>'#9CA3AF'],
          'general'=>['label'=>'General','color'=>'#1B8A4E']];
@endphp
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>Logo</th><th>Name</th><th>Tier</th><th>Website</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($sponsors as $s)
                <tr>
                    <td>
                        @if($s->logo)
                        <img src="{{ asset('storage/'.$s->logo) }}" style="height:36px;max-width:80px;object-fit:contain;border-radius:4px;">
                        @else
                        <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--rcl-primary),var(--rcl-gold));display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;font-size:.65rem;">{{ strtoupper(substr($s->name,0,2)) }}</div>
                        @endif
                    </td>
                    <td style="font-weight:700;">{{ $s->name }}</td>
                    <td><span style="background:{{ $tiers[$s->tier]['color'] ?? '#1B8A4E' }}22;color:{{ $tiers[$s->tier]['color'] ?? '#1B8A4E' }};padding:.2em .6em;border-radius:5px;font-size:.72rem;font-weight:700;">{{ $tiers[$s->tier]['label'] ?? ucfirst($s->tier) }}</span></td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $s->website ? Str::limit($s->website, 30) : '—' }}</td>
                    <td>{{ $s->display_order }}</td>
                    <td><span class="badge-rcl {{ $s->is_active ? 'badge-paid' : 'badge-unpaid' }}">{{ $s->is_active ? 'Active' : 'Hidden' }}</span></td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.sponsors.edit', $s) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                        <form method="POST" action="{{ route('admin.sponsors.destroy', $s) }}" onsubmit="return confirm('Delete sponsor?')">
                            @csrf @method('DELETE')
                            <button class="btn-rcl-danger btn" style="font-size:.72rem;padding:.25rem .6rem;">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No sponsors yet. Add your first sponsor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
