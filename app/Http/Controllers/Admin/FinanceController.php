<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $editionId = $request->edition_id ?? optional(Edition::where('is_current',true)->first())->id;

        $transactions = FinanceTransaction::with(['edition','recordedBy'])
            ->when($editionId, fn($q) => $q->where('edition_id', $editionId))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(25);

        $summary = [
            'income'  => FinanceTransaction::when($editionId, fn($q) => $q->where('edition_id',$editionId))->where('type','income')->sum('amount'),
            'expense' => FinanceTransaction::when($editionId, fn($q) => $q->where('edition_id',$editionId))->where('type','expense')->sum('amount'),
        ];
        $summary['balance'] = $summary['income'] - $summary['expense'];

        $editions = Edition::orderByDesc('edition_number')->get();
        return view('admin.finance.index', compact('transactions','summary','editions','editionId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'edition_id'   => 'required|exists:editions,id',
            'type'         => 'required|in:income,expense',
            'category'     => 'required|in:entry_fee,sponsorship,umpire_fee,scorer_fee,groundsman_fee,equipment,prize_money,other',
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'required|string|max:500',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'team_id'      => 'nullable|exists:teams,id',
        ]);

        $data['recorded_by'] = auth()->id();
        FinanceTransaction::create($data);

        return back()->with('success', 'Transaction recorded.');
    }

    public function destroy(FinanceTransaction $transaction)
    {
        $transaction->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}
