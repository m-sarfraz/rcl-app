@extends('layouts.admin')
@section('title','Finance Ledger')
@section('page-title','Finance Ledger')

@section('content')
{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(0,230,118,.12);color:var(--rcl-primary)"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div><div style="font-size:1.5rem;font-weight:900;">PKR {{ number_format($summary['income']) }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Total Income</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div><div style="font-size:1.5rem;font-weight:900;">PKR {{ number_format($summary['expense']) }}</div><div style="font-size:.75rem;color:var(--rcl-muted);">Total Expense</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(255,214,0,.12);color:var(--rcl-gold)"><i class="bi bi-wallet-fill"></i></div>
            <div><div style="font-size:1.5rem;font-weight:900;color:{{ $summary['balance']>=0?'var(--rcl-primary)':'#ef4444' }}">PKR {{ number_format(abs($summary['balance'])) }}</div>
            <div style="font-size:.75rem;color:var(--rcl-muted);">{{ $summary['balance']>=0?'Surplus':'Deficit' }}</div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Add Transaction --}}
    <div class="col-md-4">
        <div class="rcl-card">
            <div class="rcl-card-header">Record Transaction</div>
            <div class="rcl-card-body">
                <form method="POST" action="{{ route('admin.finance.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Edition</label>
                        <select name="edition_id" class="form-select" required>
                            @foreach($editions as $e)<option value="{{ $e->id }}" {{ $e->id==$editionId?'selected':'' }}>{{ $e->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="entry_fee">Entry Fee</option>
                            <option value="sponsorship">Sponsorship</option>
                            <option value="umpire_fee">Umpire Fee</option>
                            <option value="scorer_fee">Scorer Fee</option>
                            <option value="groundsman_fee">Groundsman Fee</option>
                            <option value="equipment">Equipment</option>
                            <option value="prize_money">Prize Money</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-rcl-primary w-100">Record</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="col-md-8">
        <div class="rcl-card">
            <div class="rcl-card-body p-0">
                <table class="rcl-table">
                    <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Type</th><th>Amount</th><th></th></tr></thead>
                    <tbody>
                        @forelse($transactions as $t)
                        <tr>
                            <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $t->transaction_date?->format('d M Y') }}</td>
                            <td><span class="badge-rcl badge-upcoming" style="font-size:.68rem;">{{ ucfirst(str_replace('_',' ',$t->category)) }}</span></td>
                            <td style="font-size:.82rem;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $t->description }}</td>
                            <td><span class="badge-rcl {{ $t->type==='income'?'badge-paid':'badge-unpaid' }}">{{ ucfirst($t->type) }}</span></td>
                            <td style="font-weight:700;color:{{ $t->type==='income'?'var(--rcl-primary)':'#ef4444' }}">
                                {{ $t->type==='income'?'+':'-' }} PKR {{ number_format($t->amount) }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.finance.destroy', $t) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-rcl-danger btn" style="padding:.2rem .5rem;font-size:.72rem;">Del</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{ $transactions->links() }}
    </div>
</div>
@endsection
