<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $accounts = ChartOfAccount::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('code', 'like', "%{$q}%")
                       ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->paginate(30)
            ->withQueryString();

        return view('modules.accounting.chart.index', compact('accounts', 'q'));
    }

    public function create()
    {
        $account = new ChartOfAccount([
            'is_active' => 1,
        ]);

        return view('modules.accounting.chart.create', compact('account'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:30'],
            'name' => ['required','string','max:150'],
            'type' => ['required','string','in:ASSET,LIABILITY,EQUITY,INCOME,EXPENSE'],
            'parent_id' => ['nullable','integer'],
            'is_control_account' => ['nullable','boolean'],
            'is_active' => ['nullable','boolean'],
        ]);

        // Ensure code uniqueness per company (global scope already filters company)
        $exists = ChartOfAccount::query()->where('code', $data['code'])->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'This account code already exists.'])->withInput();
        }

        ChartOfAccount::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
            'is_control_account' => (int)($data['is_control_account'] ?? 0),
            'is_active' => (int)($data['is_active'] ?? 1),
        ]);

        return redirect()->route('modules.accounting.chart.index')->with('ok', 'Account created.');
    }

    public function edit(ChartOfAccount $account)
    {
        return view('modules.accounting.chart.edit', compact('account'));
    }

    public function update(Request $request, ChartOfAccount $account)
    {
        $data = $request->validate([
            'code' => ['required','string','max:30'],
            'name' => ['required','string','max:150'],
            'type' => ['required','string','in:ASSET,LIABILITY,EQUITY,INCOME,EXPENSE'],
            'parent_id' => ['nullable','integer'],
            'is_control_account' => ['nullable','boolean'],
            'is_active' => ['nullable','boolean'],
        ]);

        $exists = ChartOfAccount::query()
            ->where('code', $data['code'])
            ->where('id', '!=', $account->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'This account code already exists.'])->withInput();
        }

        $account->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
            'is_control_account' => (int)($data['is_control_account'] ?? 0),
            'is_active' => (int)($data['is_active'] ?? 1),
        ]);

        return redirect()->route('modules.accounting.chart.index')->with('ok', 'Account updated.');
    }
}
