@extends('layouts.admin')
@section('title','Banners')
@section('page-title','Home Banners')
@section('topbar-actions')
<a href="{{ route('admin.banners.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Add Banner</a>
@endsection

@section('content')
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>Preview</th><th>Title</th><th>Link</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($banners as $banner)
                <tr>
                    <td style="width:100px;">
                        <img src="{{ asset('storage/'.$banner->image_path) }}" style="width:90px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--rcl-border);">
                    </td>
                    <td>
                        <div style="font-weight:600;">{{ $banner->title ?? '—' }}</div>
                        @if($banner->subtitle)<div style="font-size:.75rem;color:var(--rcl-muted);">{{ $banner->subtitle }}</div>@endif
                    </td>
                    <td style="font-size:.78rem;color:var(--rcl-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $banner->link_url ?? '—' }}</td>
                    <td style="font-weight:700;color:var(--rcl-gold);">{{ $banner->display_order }}</td>
                    <td>
                        <span class="badge-rcl {{ $banner->is_active ? 'badge-live' : 'badge-completed' }}">{{ $banner->is_active ? 'Active' : 'Hidden' }}</span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-rcl-secondary" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                        <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-rcl-danger" style="font-size:.72rem;padding:.25rem .6rem;">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No banners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
