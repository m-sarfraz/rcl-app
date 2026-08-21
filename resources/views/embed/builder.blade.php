@extends('layouts.app')
@section('title', 'Share & Broadcast')

@section('content')
@php
    $home = $match->homeTeam?->short_code ?? 'TBD';
    $away = $match->awayTeam?->short_code ?? 'TBD';
    $base = route('embed.widget', $match);
    $stream = collect($specs)->filter(fn ($s) => $s['stream']);
    $web    = collect($specs)->reject(fn ($s) => $s['stream']);
@endphp

<div style="padding:1rem 1rem 5rem;">

    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;">
        <a href="{{ route('scorecard', $match) }}" style="color:var(--mut);font-size:1.4rem;text-decoration:none;line-height:1;">‹</a>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:800;font-size:1.05rem;">Share &amp; Broadcast</div>
            <div style="font-size:.76rem;color:var(--mut);">
                {{ $home }} v {{ $away }} · {{ $match->edition?->name }}
                @if($match->status === 'live')
                    <span class="live-badge" style="margin-left:.35rem;">LIVE</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ Live preview ═══ --}}
    <div style="background:var(--s1);border:1px solid var(--bd);border-radius:16px;overflow:hidden;margin-bottom:1.25rem;">
        <div style="padding:.7rem 1rem;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
            <span style="font-weight:700;font-size:.85rem;">Live preview</span>
            <span id="previewSize" style="font-size:.72rem;color:var(--mut);font-family:ui-monospace,monospace;"></span>
        </div>

        {{-- Layout picker --}}
        <div style="display:flex;gap:.4rem;overflow-x:auto;padding:.7rem 1rem;border-bottom:1px solid var(--bd);">
            @foreach($specs as $key => $spec)
            <button type="button" class="layout-pick" data-layout="{{ $key }}"
                    data-w="{{ $spec['width'] }}" data-h="{{ $spec['height'] }}"
                    data-stream="{{ $spec['stream'] ? 1 : 0 }}"
                    style="flex:0 0 auto;background:var(--s2);border:1px solid var(--bd);border-radius:999px;
                           padding:.35rem .8rem;font-size:.75rem;font-weight:700;color:var(--txt);cursor:pointer;
                           white-space:nowrap;transition:all .15s;">
                {{ $spec['label'] }}
            </button>
            @endforeach
        </div>

        {{-- The checkerboard shows transparency, which is what a browser source sees --}}
        <div style="background:repeating-conic-gradient(#e2e8f0 0% 25%, #f8fafc 0% 50%) 50%/16px 16px;
                    padding:1rem;overflow:auto;">
            <iframe id="preview" src="{{ $base }}?layout=broadcast&transparent=1"
                    style="width:100%;min-width:640px;height:190px;border:0;background:transparent;display:block;"
                    scrolling="no" title="Scoreboard preview"></iframe>
        </div>

        <div style="padding:.75rem 1rem;border-top:1px solid var(--bd);">
            <p id="previewBlurb" style="font-size:.78rem;color:var(--mut);margin:0 0 .55rem;line-height:1.5;"></p>
            <div style="display:flex;gap:.4rem;align-items:stretch;">
                <input readonly id="previewUrl" value=""
                       style="flex:1;min-width:0;background:var(--s2);border:1px solid var(--bd);border-radius:9px;
                              padding:.5rem .65rem;font-size:.71rem;font-family:ui-monospace,monospace;color:var(--txt);">
                <button type="button" data-copy="previewUrl"
                        style="background:var(--p);border:0;color:#fff;font-weight:700;font-size:.75rem;
                               border-radius:9px;padding:.5rem .9rem;cursor:pointer;white-space:nowrap;">Copy URL</button>
            </div>
        </div>
    </div>

    {{-- ═══ Putting it on Facebook ═══ --}}
    <div style="background:linear-gradient(135deg,rgba(14,169,84,.09),rgba(2,136,209,.09));
                border:1px solid var(--bd);border-radius:16px;padding:1rem;margin-bottom:1.25rem;">
        <div style="font-weight:800;font-size:.95rem;margin-bottom:.15rem;">📡 Getting the score onto Facebook Live</div>
        <p style="font-size:.78rem;color:var(--mut);margin:0 0 .85rem;line-height:1.55;">
            Facebook will not run a live scoreboard inside a post — but it will carry one inside a
            <strong>video</strong>. So the score goes out burned into your stream. You score on the phone;
            the overlay follows within a few seconds. Nothing else to do during the match.
        </p>

        <ol style="margin:0;padding-left:1.15rem;font-size:.79rem;color:var(--txt);line-height:1.75;">
            <li>Pick <strong>Broadcast bar</strong> above (or <strong>Score bug</strong> if you want something smaller) and press <strong>Copy URL</strong>.</li>
            <li>Open <strong>PRISM Live Studio</strong> (or OBS Studio — either works).</li>
            <li>Add a source: <strong>+ → Web / Browser</strong>.</li>
            <li>Paste the URL. Set the <strong>width and height shown above</strong> for that layout.</li>
            <li>Leave the custom CSS box empty. The overlay already has a transparent background.</li>
            <li>Drag it where you want it — usually along the bottom.</li>
            <li>Set your destination to <strong>Facebook</strong> and go live.</li>
        </ol>

        <p style="font-size:.74rem;color:var(--mut);margin:.85rem 0 0;line-height:1.5;
                  border-top:1px solid var(--bd);padding-top:.7rem;">
            <strong>The one catch:</strong> the machine running PRISM must be able to reach this address.
            On the same Wi-Fi that is automatic. Streaming from somewhere else means putting this server
            online first.
        </p>
    </div>

    {{-- ═══ Stream layouts ═══ --}}
    <div style="font-weight:800;font-size:.9rem;margin:0 0 .6rem;">For streaming</div>
    <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.25rem;">
        @foreach($stream as $key => $spec)
        <div style="background:var(--s1);border:1px solid var(--bd);border-radius:14px;padding:.85rem 1rem;">
            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;margin-bottom:.2rem;">
                <span style="font-weight:700;font-size:.88rem;">{{ $spec['label'] }}</span>
                <span style="font-size:.7rem;color:var(--p);font-weight:700;font-family:ui-monospace,monospace;">
                    {{ $spec['width'] }} × {{ $spec['height'] }}
                </span>
            </div>
            <p style="font-size:.75rem;color:var(--mut);margin:0 0 .55rem;line-height:1.45;">{{ $spec['blurb'] }}</p>
            <div style="display:flex;gap:.4rem;">
                <input readonly id="url-{{ $key }}" value="{{ $base }}?layout={{ $key }}&transparent=1"
                       style="flex:1;min-width:0;background:var(--s2);border:1px solid var(--bd);border-radius:9px;
                              padding:.45rem .6rem;font-size:.69rem;font-family:ui-monospace,monospace;color:var(--txt);">
                <button type="button" data-copy="url-{{ $key }}"
                        style="background:var(--p);border:0;color:#fff;font-weight:700;font-size:.72rem;
                               border-radius:9px;padding:.45rem .8rem;cursor:pointer;">Copy</button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ Web layouts ═══ --}}
    <div style="font-weight:800;font-size:.9rem;margin:0 0 .6rem;">For a website</div>
    <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.25rem;">
        @foreach($web as $key => $spec)
        <div style="background:var(--s1);border:1px solid var(--bd);border-radius:14px;padding:.85rem 1rem;">
            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;margin-bottom:.2rem;">
                <span style="font-weight:700;font-size:.88rem;">{{ $spec['label'] }}</span>
                <span style="font-size:.7rem;color:var(--blue);font-weight:700;font-family:ui-monospace,monospace;">
                    {{ $spec['width'] }} × {{ $spec['height'] }}
                </span>
            </div>
            <p style="font-size:.75rem;color:var(--mut);margin:0 0 .55rem;line-height:1.45;">{{ $spec['blurb'] }}</p>
            <div style="display:flex;gap:.4rem;">
                <input readonly id="iframe-{{ $key }}"
                       value='<iframe src="{{ $base }}?layout={{ $key }}" width="{{ $spec['width'] }}" height="{{ $spec['height'] }}" frameborder="0" scrolling="no" style="border:0;max-width:100%"></iframe>'
                       style="flex:1;min-width:0;background:var(--s2);border:1px solid var(--bd);border-radius:9px;
                              padding:.45rem .6rem;font-size:.69rem;font-family:ui-monospace,monospace;color:var(--txt);">
                <button type="button" data-copy="iframe-{{ $key }}"
                        style="background:var(--blue);border:0;color:#fff;font-weight:700;font-size:.72rem;
                               border-radius:9px;padding:.45rem .8rem;cursor:pointer;">Copy</button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ Share as a post ═══ --}}
    <div style="font-weight:800;font-size:.9rem;margin:0 0 .6rem;">As a post or message</div>
    <div style="background:var(--s1);border:1px solid var(--bd);border-radius:14px;padding:.85rem 1rem;margin-bottom:.6rem;">
        <div style="font-weight:700;font-size:.88rem;margin-bottom:.2rem;">Share link</div>
        <p style="font-size:.75rem;color:var(--mut);margin:0 0 .55rem;line-height:1.45;">
            Posting this on Facebook or WhatsApp shows a scoreboard image as the preview.
            Facebook caches that preview, so re-share when you want a newer score to show.
        </p>
        <div style="display:flex;gap:.4rem;">
            <input readonly id="url-link" value="{{ route('scorecard', $match) }}"
                   style="flex:1;min-width:0;background:var(--s2);border:1px solid var(--bd);border-radius:9px;
                          padding:.45rem .6rem;font-size:.69rem;font-family:ui-monospace,monospace;color:var(--txt);">
            <button type="button" data-copy="url-link"
                    style="background:var(--g);border:0;color:#fff;font-weight:700;font-size:.72rem;
                           border-radius:9px;padding:.45rem .8rem;cursor:pointer;">Copy</button>
        </div>
    </div>

    <div style="background:var(--s1);border:1px solid var(--bd);border-radius:14px;padding:.85rem 1rem;">
        <div style="font-weight:700;font-size:.88rem;margin-bottom:.2rem;">Scoreboard image</div>
        <p style="font-size:.75rem;color:var(--mut);margin:0 0 .55rem;line-height:1.45;">
            A picture of the score right now — save it and send it straight to a WhatsApp group.
        </p>
        <a href="{{ route('embed.image', $match) }}" target="_blank" rel="noopener" style="display:block;margin-bottom:.55rem;">
            <img src="{{ route('embed.image', $match) }}?v={{ $data['revision'] }}" alt="Scoreboard"
                 style="width:100%;border-radius:10px;border:1px solid var(--bd);display:block;">
        </a>
        <div style="display:flex;gap:.4rem;">
            <input readonly id="url-image" value="{{ route('embed.image', $match) }}"
                   style="flex:1;min-width:0;background:var(--s2);border:1px solid var(--bd);border-radius:9px;
                          padding:.45rem .6rem;font-size:.69rem;font-family:ui-monospace,monospace;color:var(--txt);">
            <button type="button" data-copy="url-image"
                    style="background:var(--pur);border:0;color:#fff;font-weight:700;font-size:.72rem;
                           border-radius:9px;padding:.45rem .8rem;cursor:pointer;">Copy</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var base    = @json($base);
    var specs   = @json($specs);
    var preview = document.getElementById('preview');
    var picks   = document.querySelectorAll('.layout-pick');

    function urlFor(key) {
        return base + '?layout=' + key + (specs[key].stream ? '&transparent=1' : '');
    }

    function select(key) {
        var spec = specs[key];

        preview.src = urlFor(key);
        preview.style.height = Math.min(spec.height, 460) + 'px';
        preview.style.minWidth = Math.min(spec.width, 1200) + 'px';

        document.getElementById('previewSize').textContent = spec.width + ' × ' + spec.height + ' px';
        document.getElementById('previewBlurb').textContent = spec.blurb + '  —  ' + spec.best + '.';
        document.getElementById('previewUrl').value = urlFor(key);

        picks.forEach(function (b) {
            var on = b.getAttribute('data-layout') === key;
            b.style.background  = on ? 'var(--p)' : 'var(--s2)';
            b.style.color       = on ? '#fff' : 'var(--txt)';
            b.style.borderColor = on ? 'var(--p)' : 'var(--bd)';
        });
    }

    picks.forEach(function (b) {
        b.addEventListener('click', function () { select(b.getAttribute('data-layout')); });
    });

    select('broadcast');

    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = document.getElementById(btn.getAttribute('data-copy'));
            field.select();
            field.setSelectionRange(0, 99999);
            try { document.execCommand('copy'); } catch (e) {}
            if (navigator.clipboard) navigator.clipboard.writeText(field.value).catch(function () {});
            var was = btn.textContent;
            btn.textContent = '✓ Copied';
            setTimeout(function () { btn.textContent = was; }, 1400);
        });
    });
}());
</script>
@endpush
@endsection
