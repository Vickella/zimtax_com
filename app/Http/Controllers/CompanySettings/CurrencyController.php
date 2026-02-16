<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\StoreCurrencyRequest;
use App\Models\Currency;


class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::query()->orderBy('code')->get();
        return view('modules.company-settings.currencies', compact('currencies'));
    }

    public function store(StoreCurrencyRequest $request)
    {
        try {
            // Validate the code format
            $code = strtoupper(trim($request->input('code')));
            
            // Check if currency already exists
            $existing = Currency::find($code);
            
            if ($existing) {
                // Update existing
                $existing->update([
                    'name' => trim($request->input('name')),
                    'symbol' => trim($request->input('symbol')),
                    'is_active' => (bool) $request->input('is_active', false),
                ]);
            } else {
                // Create new
                Currency::create([
                    'code' => $code,
                    'name' => trim($request->input('name')),
                    'symbol' => trim($request->input('symbol')),
                    'is_active' => (bool) $request->input('is_active', false),
                ]);
            }

            return redirect()
                ->route('modules.company-settings.currencies.index')
                ->with('ok', 'Currency saved successfully.');

        } catch (\Exception $e) {
            \Log::error('Currency save failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save currency. Please try again.']);
        }
    }

    public function update(StoreCurrencyRequest $request, string $code)
    {
        try {
            $currency = Currency::where('code', strtoupper($code))->firstOrFail();
            
            $currency->update([
                'name' => trim($request->input('name')),
                'symbol' => trim($request->input('symbol')),
                'is_active' => (bool) $request->input('is_active', false),
            ]);

            return redirect()
                ->route('modules.company-settings.currencies.index')
                ->with('ok', 'Currency updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Currency update failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'code' => $code,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update currency. Please try again.']);
        }
    }
    
    // Optional: Add delete method if needed
    public function destroy(string $code)
    {
        try {
            $currency = Currency::where('code', strtoupper($code))->firstOrFail();
            $currency->delete();
            
            return redirect()
                ->route('modules.company-settings.currencies.index')
                ->with('ok', 'Currency deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Currency delete failed: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Failed to delete currency. Please try again.']);
        }
    }
}