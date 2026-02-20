<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; }
        .page { position: relative; width: 210mm; height: 297mm; }
        .bg { position:absolute; inset:0; width:100%; height:100%; }
        .field { position:absolute; font-size: 11px; }
        .box { padding: 2px 4px; }
    </style>
</head>
<body>
<div class="page">
    <img class="bg" src="{{ public_path('tax/forms/itf12c.png') }}" alt="ITF12C"/>

    <div class="field box" style="left:25mm; top:40mm;">
        {{ $incomeTaxReturn->period_start?->format('Y-m-d') }}
    </div>
    <div class="field box" style="left:65mm; top:40mm;">
        {{ $incomeTaxReturn->period_end?->format('Y-m-d') }}
    </div>

    <div class="field box" style="left:140mm; top:110mm;">
        {{ number_format($incomeTaxReturn->taxable_income,2) }}
    </div>

    <div class="field box" style="left:140mm; top:120mm; font-weight:bold;">
        {{ number_format($incomeTaxReturn->income_tax_payable,2) }}
    </div>
</div>
</body>
</html>
