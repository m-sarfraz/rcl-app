@extends('layouts.admin')
@section('title','Team Captains')
@section('page-title','Team Captains — Edition {{ $edition?->name ?? "N/A" }}')

@section('content')
<div class="rcl-card">
    <div class="rcl-card-header">
        <span><i class="bi bi-star-fill" style="color:var(--rcl-gold);"></i> Team Captains &amp; Vice-Captains</span>
        <span style="font-size:.75rem;color:var(--rcl-muted);">{{ $edition?->name }} · {{ $teams->count() }} teams</span>
    </div>
    <div class="rcl-card-body" style="padding:0;">
        <table class="rcl-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team</th>
                    <th>Village</th>
                    <th>Captain</th>
                    <th>Vice-Captain</th>
                    <th>Squad</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teams as $i => $team)
                @php
                    $r = $rosters[$team->id] ?? ['count'=>0,'captain'=>null,'vc'=>null];
                @endphp
                <tr>
                    <td style="color:var(--rcl-muted);font-size:.78rem;">{{ $i+1 }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,{{ $team->primary_color ?? '#00e676' }},{{ $team->secondary_color ?? '#ffd600' }});display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.65rem;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($team->short_code,0,2)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:.85rem;">{{ $team->name }}</div>
                                <div style="font-size:.7rem;color:var(--rcl-muted);">{{ $team->short_code }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $team->village_name }}</td>
                    <td>
                        @if($r['captain'])
                            <div style="display:flex;align-items:center;gap:.4rem;">
                                <span style="background:rgba(255,214,0,.15);color:var(--rcl-gold);padding:.2em .55em;border-radius:5px;font-size:.65rem;font-weight:700;">C</span>
                                <span style="font-size:.85rem;font-weight:600;">{{ $r['captain']->name }}</span>
                            </div>
                        @else
                            <span style="color:var(--rcl-muted);font-size:.8rem;font-style:italic;">Not set</span>
                        @endif
                    </td>
                    <td>
                        @if($r['vc'])
                            <div style="display:flex;align-items:center;gap:.4rem;">
                                <span style="background:rgba(0,230,118,.12);color:var(--rcl-primary);padding:.2em .55em;border-radius:5px;font-size:.65rem;font-weight:700;">VC</span>
                                <span style="font-size:.85rem;">{{ $r['vc']->name }}</span>
                            </div>
                        @else
                            <span style="color:var(--rcl-muted);font-size:.8rem;font-style:italic;">Not set</span>
                        @endif
                    </td>
                    <td>
                        <span style="background:rgba(0,230,118,.1);color:var(--rcl-primary);padding:.25em .65em;border-radius:20px;font-size:.75rem;font-weight:700;">
                            {{ $r['count'] }} players
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.captains.edit', $team) }}" class="btn-rcl-secondary" style="font-size:.78rem;padding:.3rem .75rem;text-decoration:none;">
                            <i class="bi bi-pencil-fill"></i> Manage
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
