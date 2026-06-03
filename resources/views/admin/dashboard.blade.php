@extends('layouts.admin')
@section('title','Dashboard')
@section('page-title','Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(0,230,118,.12);color:var(--rcl-primary)"><i class="bi bi-shield-fill"></i></div>
            <div><div style="font-size:1.75rem;font-weight:900;">{{ $stats['teams'] }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Teams</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(255,214,0,.12);color:var(--rcl-gold)"><i class="bi bi-people-fill"></i></div>
            <div><div style="font-size:1.75rem;font-weight:900;">{{ $stats['players'] }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Players</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="bi bi-broadcast"></i></div>
            <div><div style="font-size:1.75rem;font-weight:900;">{{ $stats['live_matches'] }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Live</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(124,77,255,.12);color:#7c4dff"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div><div style="font-size:1.75rem;font-weight:900;">{{ $stats['total_fines'] }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Unpaid Fines</div></div>
        </div>
    </div>
</div>

@if($financeBalance)
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(0,230,118,.12);color:var(--rcl-primary)"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div><div style="font-size:1.25rem;font-weight:800;">PKR {{ number_format($financeBalance['income']) }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Total Income</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div><div style="font-size:1.25rem;font-weight:800;">PKR {{ number_format($financeBalance['expense']) }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Total Expense</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(255,214,0,.12);color:var(--rcl-gold)"><i class="bi bi-wallet-fill"></i></div>
            <div>
                <div style="font-size:1.25rem;font-weight:800;color:{{ $financeBalance['balance'] >= 0 ? 'var(--rcl-primary)' : '#ef4444' }}">PKR {{ number_format(abs($financeBalance['balance'])) }}</div>
                <div style="font-size:.75rem;color:var(--rcl-muted);">{{ $financeBalance['balance'] >= 0 ? 'Surplus' : 'Deficit' }}</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    <div class="col-md-7">
        <div class="rcl-card">
            <div class="rcl-card-header">
                <span><i class="bi bi-broadcast me-2" style="color:#ef4444"></i>Live Matches</span>
                <a href="{{ route('admin.matches.index', ['status'=>'live']) }}" class="topbar-btn">View All</a>
            </div>
            <div class="rcl-card-body p-0">
                @forelse($liveMatches as $m)
                <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--rcl-border);display:flex;align-items:center;gap:.75rem;">
                    <div style="flex:1">
                        <div style="font-weight:700;font-size:.875rem;">{{ $m->homeTeam?->short_code }} vs {{ $m->awayTeam?->short_code }}</div>
                        <div style="font-size:.72rem;color:var(--rcl-muted);">{{ $m->venue }} • {{ $m->edition?->name }}</div>
                    </div>
                    <div style="text-align:right;">
                        @php $inn = $m->innings->last(); @endphp
                        @if($inn)
                            <div style="font-weight:800;">{{ $inn->total_runs }}/{{ $inn->total_wickets }}</div>
                            <div style="font-size:.72rem;color:var(--rcl-muted);">{{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }} ov</div>
                        @endif
                    </div>
                    <a href="{{ route('admin.scoring.console', $m) }}" class="btn-rcl-primary btn" style="font-size:.75rem;padding:.35rem .75rem;">Score</a>
                </div>
                @empty
                <div style="padding:2rem;text-align:center;color:var(--rcl-muted);font-size:.875rem;">No live matches</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="rcl-card">
            <div class="rcl-card-header">
                <span><i class="bi bi-clock-history me-2"></i>Recent Matches</span>
            </div>
            <div class="rcl-card-body p-0">
                @forelse($recentMatches as $m)
                <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--rcl-border);">
                    <div style="font-weight:600;font-size:.82rem;">{{ $m->homeTeam?->short_code }} vs {{ $m->awayTeam?->short_code }}</div>
                    <div style="font-size:.7rem;color:var(--rcl-muted);">
                        @if($m->winner) {{ $m->winner->name }} won by {{ $m->result_margin }} {{ $m->result_type }}
                        @elseif($m->result_type === 'tie') Match tied
                        @else No result @endif
                    </div>
                </div>
                @empty
                <div style="padding:1.5rem;text-align:center;color:var(--rcl-muted);font-size:.875rem;">No completed matches yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
