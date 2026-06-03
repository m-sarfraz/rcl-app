@extends('layouts.admin')
@section('title',$player->name)
@section('page-title','Player Profile')
@section('topbar-actions')
<a href="{{ route('admin.players.edit',$player) }}" class="topbar-btn"><i class="bi bi-pencil"></i> Edit</a>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="rcl-card text-center" style="padding:1.5rem;">
            @if($player->photo)
            <img src="{{ asset('storage/'.$player->photo) }}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--rcl-primary);margin-bottom:1rem;">
            @else
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--rcl-primary),var(--rcl-gold));display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;font-size:2rem;margin:0 auto 1rem;">{{ strtoupper(substr($player->name,0,2)) }}</div>
            @endif
            <div style="font-weight:800;font-size:1.1rem;">{{ $player->name }}</div>
            <div style="color:var(--rcl-primary);font-size:.8rem;margin:.25rem 0;">{{ ucfirst(str_replace('_',' ',$player->role)) }}</div>
            <span class="badge-rcl {{ $player->bowling_action_status==='legal'?'badge-paid':($player->bowling_action_status==='flagged'?'badge-upcoming':'badge-unpaid') }} mt-1">{{ ucfirst($player->bowling_action_status) }}</span>
        </div>
        <div class="rcl-card mt-3">
            <div class="rcl-card-header">Unpaid Fines</div>
            <div class="rcl-card-body p-0">
                @forelse($player->fines->where('status','unpaid') as $fine)
                <div style="padding:.625rem 1rem;border-bottom:1px solid var(--rcl-border);">
                    <div style="font-size:.82rem;font-weight:600;">PKR {{ number_format($fine->amount) }}</div>
                    <div style="font-size:.72rem;color:var(--rcl-muted);">{{ $fine->reason }}</div>
                </div>
                @empty
                <div style="padding:1rem;text-align:center;color:var(--rcl-muted);font-size:.85rem;">No unpaid fines ✅</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="rcl-card">
            <div class="rcl-card-header">Edition Statistics</div>
            <div class="rcl-card-body p-0">
                <table class="rcl-table">
                    <thead><tr><th>Edition</th><th>Team</th><th>Runs</th><th>Wkts</th><th>50s</th><th>100s</th><th>SR</th><th>Econ</th></tr></thead>
                    <tbody>
                        @forelse($player->editionStats as $stat)
                        <tr>
                            <td>{{ $stat->edition?->name }}</td>
                            <td>{{ $stat->team?->name }}</td>
                            <td style="font-weight:700;color:var(--rcl-primary);">{{ $stat->total_runs }}</td>
                            <td style="font-weight:700;">{{ $stat->total_wickets }}</td>
                            <td>{{ $stat->fifties }}</td>
                            <td>{{ $stat->hundreds }}</td>
                            <td style="color:var(--rcl-muted);">{{ $stat->strike_rate ? number_format($stat->strike_rate,1) : '—' }}</td>
                            <td style="color:var(--rcl-muted);">{{ $stat->economy ? number_format($stat->economy,2) : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No stats recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
