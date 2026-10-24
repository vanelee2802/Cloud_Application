@php
    use App\Models\Receipt;
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rechnung {{ $r->number }}</title>
    <style>
        @page { margin: 28mm 20mm 25mm 20mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            color: #2b2226;
            line-height: 1.45;
        }

        .header { border-bottom: 2px solid #8f5364; padding-bottom: 10px; margin-bottom: 24px; }
        .studio-name { font-size: 16pt; font-weight: bold; color: #8f5364; }
        .muted { color: #7a6a70; font-size: 8.5pt; }

        h1 { font-size: 18pt; margin: 0 0 4px; color: #4f3540; }

        .two-col { width: 100%; margin-bottom: 24px; }
        .two-col td { vertical-align: top; }

        .meta td { padding: 1px 12px 1px 0; }
        .meta td:first-child { color: #7a6a70; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th {
            text-align: left;
            border-bottom: 1.5px solid #4f3540;
            padding: 6px 4px;
            font-size: 9pt;
        }
        table.items td { border-bottom: 1px solid #e6dde0; padding: 7px 4px; }
        .right { text-align: right; }

        table.totals { width: 55%; margin-left: 45%; margin-top: 14px; border-collapse: collapse; }
        table.totals td { padding: 3px 4px; }
        table.totals .grand td {
            border-top: 1.5px solid #4f3540;
            font-weight: bold;
            font-size: 11.5pt;
            padding-top: 6px;
        }

        .paid {
            display: inline-block;
            margin-top: 18px;
            padding: 5px 10px;
            border: 1px solid #477052;
            color: #477052;
            border-radius: 4px;
            font-weight: bold;
        }

        .qr { margin-top: 28px; }
        .qr img { width: 32mm; height: 32mm; }
        .qr td { vertical-align: middle; }

        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #7a6a70;
            border-top: 1px solid #e6dde0;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    {{-- Rechnungsaussteller --}}
    <div class="header">
        <div class="studio-name">{{ $r->studio_name }}</div>
        <div class="muted">{{ $r->studio_address }}</div>
    </div>

    <table class="two-col">
        <tr>
            {{-- Rechnungsempfängerin --}}
            <td style="width: 55%">
                <div class="muted">Rechnung an</div>
                <strong>{{ $r->customer_name }}</strong><br>
                {{ $r->customer_email }}
            </td>

            {{-- Rechnungsdaten --}}
            <td>
                <h1>Rechnung</h1>
                <table class="meta">
                    <tr><td>Rechnungsnr.</td><td><strong>{{ $r->number }}</strong></td></tr>
                    <tr><td>Rechnungsdatum</td><td>{{ $r->issued_at->format('d.m.Y') }}</td></tr>
                    <tr><td>Leistungsdatum</td><td>{{ $r->service_date->format('d.m.Y') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Positionen --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 8%">Pos.</th>
                <th>Leistung</th>
                <th class="right" style="width: 10%">Menge</th>
                <th class="right" style="width: 20%">Einzelpreis</th>
                <th class="right" style="width: 20%">Gesamt</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($r->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td class="right">{{ $item['quantity'] }}</td>
                    <td class="right">{{ Receipt::euro($item['price_cents']) }}</td>
                    <td class="right">{{ Receipt::euro($item['price_cents'] * $item['quantity']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summen --}}
    <table class="totals">
        @if ($r->is_small_business)
            <tr class="grand">
                <td>Gesamtbetrag</td>
                <td class="right">{{ Receipt::euro($r->gross_cents) }}</td>
            </tr>
        @else
            <tr>
                <td>Nettobetrag</td>
                <td class="right">{{ Receipt::euro($r->net_cents) }}</td>
            </tr>
            <tr>
                <td>zzgl. {{ $r->vat_rate }} % USt.</td>
                <td class="right">{{ Receipt::euro($r->vat_cents) }}</td>
            </tr>
            <tr class="grand">
                <td>Gesamtbetrag</td>
                <td class="right">{{ Receipt::euro($r->gross_cents) }}</td>
            </tr>
        @endif
    </table>

    @if ($r->is_small_business)
        <p class="muted" style="margin-top: 10px">
            Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.
        </p>
    @endif

    <div class="paid">
        Bezahlt am {{ $r->issued_at->format('d.m.Y') }} per Online-Zahlung
    </div>

    {{-- QR-Code zur Terminseite --}}
    <table class="qr">
        <tr>
            <td style="width: 38mm"><img src="{{ $qrCode }}" alt="QR-Code"></td>
            <td class="muted">
                Scanne den QR-Code, um deinen Termin<br>
                online anzusehen – auch ohne Anmeldung.
            </td>
        </tr>
    </table>

    {{-- Fußzeile mit Steuerangaben --}}
    <div class="footer">
        {{ $r->studio_name }} · {{ $r->studio_address }}
        @if ($r->tax_number) · Steuernummer: {{ $r->tax_number }} @endif
        @if ($r->vat_id) · USt-IdNr.: {{ $r->vat_id }} @endif
    </div>

</body>
</html>