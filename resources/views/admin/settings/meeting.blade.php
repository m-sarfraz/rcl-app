@extends('layouts.admin')
@section('title','Meeting Notice')
@section('page-title','Meeting Notice / Announcement')

@section('content')
<div class="rcl-card" style="max-width:900px;">
    <div class="rcl-card-header" style="display:flex;align-items:center;gap:.625rem;">
        <i class="bi bi-megaphone-fill" style="color:var(--rcl-primary);"></i>
        Home Page — Meeting / Announcement Block
    </div>
    <div class="rcl-card-body">
        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <p style="font-size:.82rem;color:var(--rcl-muted);margin-bottom:1rem;">
            This content appears as a full-width rich block on the home page. Use it for upcoming matches, general notices, meeting announcements, or any HTML content. Leave blank to hide the section.
        </p>

        <form method="POST" action="{{ route('admin.settings.meeting.save') }}">
            @csrf
            <textarea id="meeting-editor" name="content" style="display:none;">{{ old('content', $content) }}</textarea>

            <div style="margin-top:1rem;display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-rcl-primary">
                    <i class="bi bi-save"></i> Save Changes
                </button>
                <button type="button" class="btn btn-rcl-secondary" onclick="tinymce.get('meeting-editor').setContent('')">
                    Clear
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/xry578cl7ythqywspy5ltc4r52h6ctps0lrhivkeuthja2vk/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#meeting-editor',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    height: 480,
    skin: 'oxide',
    content_css: 'default',
    font_size_formats: '10px 12px 14px 16px 18px 24px 30px 36px 48px',
    setup: function(editor) {
        editor.on('change', function() { editor.save(); });
    },
    content_style: "body { font-family: 'Quicksand', sans-serif; font-size: 14px; line-height: 1.6; padding: 1rem; }",
});
</script>
@endpush
