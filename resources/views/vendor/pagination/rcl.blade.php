@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.75rem 0;flex-wrap:wrap;">
    <div style="font-size:.72rem;color:var(--mut);">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </div>
    <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="padding:.3rem .7rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--mut);border:1px solid var(--bd);opacity:.5;cursor:default;">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="padding:.3rem .7rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--txt);border:1px solid var(--bd);text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='var(--p)';this.style.color='var(--p)'" onmouseout="this.style.borderColor='var(--bd)';this.style.color='var(--txt)'">‹</a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:.3rem .5rem;font-size:.75rem;color:var(--mut);">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="padding:.3rem .7rem;border-radius:8px;font-size:.75rem;font-weight:700;background:var(--p);color:#fff;border:1px solid var(--p);">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="padding:.3rem .7rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--txt);border:1px solid var(--bd);text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='var(--p)';this.style.color='var(--p)'" onmouseout="this.style.borderColor='var(--bd)';this.style.color='var(--txt)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="padding:.3rem .7rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--txt);border:1px solid var(--bd);text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='var(--p)';this.style.color='var(--p)'" onmouseout="this.style.borderColor='var(--bd)';this.style.color='var(--txt)'">›</a>
        @else
            <span style="padding:.3rem .7rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--mut);border:1px solid var(--bd);opacity:.5;cursor:default;">›</span>
        @endif
    </div>
</nav>
@endif
