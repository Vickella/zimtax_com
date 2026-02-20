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
    <img class="bg" src="{{ public_path('tax/forms/itf12b.png') }}" alt="ITF12B"/>

    <div class="field box" style="left:20mm; top:35mm; font-weight:bold;">
        {{ $projection->tax_year }} / Q{{ $quarterNo }}
    </div>

    <div class="field box" style="left:140mm; top:60mm;">
        {{ number_format($summary['estimated_tax_payable'],2) }}
    </div>

    <div class="field box" style="left:140mm; top:75mm; font-weight:bold;">
        {{ number_format($quarter['balance_due'],2) }}
    </div>

    <div class="field box" style="left:140mm; top:85mm;">
        Due: {{ $quarter['due_date'] }}
    </div>
</div>
</body>
</html>
