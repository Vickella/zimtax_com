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
        .small { font-size: 10px; }
        .box { padding: 2px 4px; }
    </style>
</head>
<body>
<div class="page">
    {{-- Background (exact form) --}}
    <img class="bg" src="{{ public_path('tax/forms/vat7.png') }}" alt="VAT7"/>

    {{-- Overlay values (positions must be tuned once based on your vat7.png) --}}
    <div class="field box" style="left:25mm; top:40mm;">
        {{ $vatReturn->period_start?->format('Y-m-d') }}
    </div>
    <div class="field box" style="left:65mm; top:40mm;">
        {{ $vatReturn->period_end?->format('Y-m-d') }}
    </div>

    <div class="field box" style="left:150mm; top:40mm;">
        {{ number_format($vatReturn->vat_rate*100,2) }}%
    </div>

    <div class="field box" style="left:140mm; top:100mm;">
        {{ number_format($vatReturn->taxable_sales,2) }}
    </div>

    <div class="field box" style="left:140mm; top:110mm;">
        {{ number_format($vatReturn->output_vat,2) }}
    </div>

    <div class="field box" style="left:140mm; top:140mm;">
        {{ number_format($vatReturn->taxable_purchases,2) }}
    </div>

    <div class="field box" style="left:140mm; top:150mm;">
        {{ number_format($vatReturn->input_vat,2) }}
    </div>

    <div class="field box" style="left:140mm; top:180mm; font-weight:bold;">
        {{ number_format($vatReturn->net_vat_payable,2) }}
    </div>

</div>
</body>
</html>
