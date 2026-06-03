@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.75rem 0;">
    @if ($paginator->onFirstPage())
        <span style="padding:.35rem .875rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--mut);border:1px solid var(--bd);opacity:.5;">‹ Prev</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" style="padding:.35rem .875rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--txt);border:1px solid var(--bd);text-decoration:none;">‹ Prev</a>
    @endif
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" style="padding:.35rem .875rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--txt);border:1px solid var(--bd);text-decoration:none;">Next ›</a>
    @else
        <span style="padding:.35rem .875rem;border-radius:8px;font-size:.75rem;font-weight:600;background:var(--s2);color:var(--mut);border:1px solid var(--bd);opacity:.5;">Next ›</span>
    @endif
</nav>
@endif
