<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\Numbers\NumberSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $q = trim((string)$request->get('q', ''));

        $journals = JournalEntry::query()
            ->when($status, fn($x) => $x->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('entry_no', 'like', "%{$q}%")
                       ->orWhere('memo', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('posting_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.accounting.journals.index', compact('journals', 'status', 'q'));
    }

    public function create()
    {
        $journal = new JournalEntry([
            'posting_date' => now()->toDateString(),
            'currency' => company_currency(),
            'exchange_rate' => 1,
            'status' => 'DRAFT',
        ]);

        // IMPORTANT: no forCompany() — global scope already applies
        $accounts = ChartOfAccount::query()
            ->where('is_active', 1)
            ->orderBy('code')
            ->get(['id','code','name','type']);

        return view('modules.accounting.journals.create', compact('journal','accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'posting_date' => ['required','date'],
            'memo' => ['nullable','string','max:255'],
            'currency' => ['required','string','size:3'],
            'exchange_rate' => ['required','numeric','min:0.00000001'],

            'lines' => ['required','array','min:2'],
            'lines.*.account_id' => ['required','integer'],
            'lines.*.description' => ['nullable','string','max:255'],
            'lines.*.debit' => ['nullable','numeric','min:0'],
            'lines.*.credit' => ['nullable','numeric','min:0'],
        ]);

        $companyId = company_id();

        return DB::transaction(function () use ($data, $companyId) {

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($data['lines'] as $l) {
                $d = (float)($l['debit'] ?? 0);
                $c = (float)($l['credit'] ?? 0);
                $totalDebit += $d;
                $totalCredit += $c;
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                abort(422, 'Journal is not balanced. Total debit must equal total credit.');
            }

            // Validate accounts exist in this company (global scope)
            $accountIds = collect($data['lines'])->pluck('account_id')->unique()->values();
            $count = ChartOfAccount::query()->whereIn('id', $accountIds)->count();
            if ($count !== $accountIds->count()) {
                abort(422, 'One or more accounts are invalid.');
            }

            $entryNo = NumberSeries::next('JE', $companyId, 'journal_entries', 'entry_no');

            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'entry_no' => $entryNo,
                'posting_date' => $data['posting_date'],
                'memo' => $data['memo'] ?? null,
                'status' => 'DRAFT',
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'],
                'created_by' => auth()->id(),
            ]);

            foreach ($data['lines'] as $l) {
                JournalLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $l['account_id'],
                    'description' => $l['description'] ?? null,
                    'debit' => (float)($l['debit'] ?? 0),
                    'credit' => (float)($l['credit'] ?? 0),
                    'party_type' => 'NONE',
                    'party_id' => null,
                ]);
            }

            return redirect()->route('modules.accounting.journals.show', $journal)->with('ok','Journal saved (Draft).');
        });
    }

    public function show(JournalEntry $journal)
    {
        $journal->load(['lines.account']);
        return view('modules.accounting.journals.show', compact('journal'));
    }

    public function edit(JournalEntry $journal)
    {
        abort_if($journal->status !== 'DRAFT', 403, 'Only DRAFT journals can be edited.');

        $journal->load('lines');

        $accounts = ChartOfAccount::query()
            ->where('is_active', 1)
            ->orderBy('code')
            ->get(['id','code','name','type']);

        return view('modules.accounting.journals.edit', compact('journal','accounts'));
    }

    public function update(Request $request, JournalEntry $journal)
    {
        abort_if($journal->status !== 'DRAFT', 403, 'Only DRAFT journals can be updated.');

        $data = $request->validate([
            'posting_date' => ['required','date'],
            'memo' => ['nullable','string','max:255'],
            'currency' => ['required','string','size:3'],
            'exchange_rate' => ['required','numeric','min:0.00000001'],

            'lines' => ['required','array','min:2'],
            'lines.*.account_id' => ['required','integer'],
            'lines.*.description' => ['nullable','string','max:255'],
            'lines.*.debit' => ['nullable','numeric','min:0'],
            'lines.*.credit' => ['nullable','numeric','min:0'],
        ]);

        return DB::transaction(function () use ($data, $journal) {

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($data['lines'] as $l) {
                $totalDebit += (float)($l['debit'] ?? 0);
                $totalCredit += (float)($l['credit'] ?? 0);
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                abort(422, 'Journal is not balanced.');
            }

            $journal->update([
                'posting_date' => $data['posting_date'],
                'memo' => $data['memo'] ?? null,
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'],
            ]);

            JournalLine::query()->where('journal_entry_id', $journal->id)->delete();

            foreach ($data['lines'] as $l) {
                JournalLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $l['account_id'],
                    'description' => $l['description'] ?? null,
                    'debit' => (float)($l['debit'] ?? 0),
                    'credit' => (float)($l['credit'] ?? 0),
                    'party_type' => 'NONE',
                    'party_id' => null,
                ]);
            }

            return redirect()->route('modules.accounting.journals.show', $journal)->with('ok','Journal updated.');
        });
    }

    public function post(\App\Models\JournalEntry $journal)
{
    // Safety: only allow posting draft
    if (in_array($journal->status, ['POSTED', 'CANCELLED'], true)) {
        return back()->with('error', 'This journal cannot be posted (current status: '.$journal->status.').');
    }

    // Make sure we have lines
    $lines = $journal->lines()->get();
    if ($lines->isEmpty()) {
        return back()->with('error', 'Cannot post: journal has no lines.');
    }

    // Validate required fields in header (add more if needed)
    if (empty($journal->posting_date) || empty($journal->currency) || empty($journal->exchange_rate)) {
        return back()->with('error', 'Cannot post: missing required journal header fields.');
    }

    // Validate each line has an account and at least one of debit/credit > 0
    foreach ($lines as $i => $line) {
        if (empty($line->account_id)) {
            return back()->with('error', 'Line '.($i + 1).' is missing an account.');
        }

        $debit = (float) ($line->debit ?? 0);
        $credit = (float) ($line->credit ?? 0);

        if ($debit <= 0 && $credit <= 0) {
            return back()->with('error', 'Line '.($i + 1).' must have a debit or credit amount.');
        }

        // Optional: prevent both debit and credit on same line
        if ($debit > 0 && $credit > 0) {
            return back()->with('error', 'Line '.($i + 1).' cannot have both debit and credit.');
        }
    }

    // Balance check
    $totalDebit  = round((float) $lines->sum(fn ($l) => (float) ($l->debit ?? 0)), 2);
    $totalCredit = round((float) $lines->sum(fn ($l) => (float) ($l->credit ?? 0)), 2);

    if ($totalDebit <= 0 || $totalCredit <= 0) {
        return back()->with('error', 'Cannot post: total debit and credit must be greater than zero.');
    }

    if (abs($totalDebit - $totalCredit) > 0.01) {
        return back()->with('error', "Cannot post: debits ($totalDebit) do not match credits ($totalCredit).");
    }

    DB::transaction(function () use ($journal) {
        $journal->status = 'POSTED';
        $journal->posted_by = Auth::id();
        $journal->posted_at = Carbon::now();
        $journal->save();

        // If later you implement GL posting, you can create ledger rows here.
        // For now, posting = locking the journal as POSTED.
    });

    return redirect()
        ->route('modules.accounting.journals.show', $journal)
        ->with('success', 'Journal posted successfully.');
}

    public function reverse(JournalEntry $journal)
    {
        abort(501, 'Reverse not implemented yet in this snippet.');
    }

    public function cancel(JournalEntry $journal)
    {
        abort_if($journal->status !== 'DRAFT', 403, 'Only DRAFT journals can be cancelled.');
        $journal->status = 'CANCELLED';
        $journal->save();

        return back()->with('ok','Journal cancelled.');
    }
}
