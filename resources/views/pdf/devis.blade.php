<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LA STATION - Devis {{ $devis->devis_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: white;
            color: #333;
            line-height: 1.6;
            width: 210mm;
            min-height: 297mm;
        }
        
        .invoice-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            position: relative;
        }
        
        .decorative-circles {
            position: absolute;
            top: 20px;
            right: 40px;
            z-index: 1;
        }
        
        .circle {
            width: 25px;
            height: 25px;
            background: #ffd700;
            border-radius: 50%;
            display: inline-block;
            margin-left: 8px;
        }
        
        .header {
            padding: 30px 40px 20px 40px;
            background: white;
            position: relative;
            z-index: 2;
        }
        
        .logo {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 2px;
            color: #000;
        }
        
        .subtitle {
            font-size: 10px;
            font-weight: 400;
            letter-spacing: 1px;
            color: #666;
            text-transform: uppercase;
        }
        
        .content {
            padding: 20px 40px 40px 40px;
            position: relative;
            z-index: 2;
        }
        
        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 35px;
        }
        
        .company-info, .invoice-title {
            display: table-cell;
            vertical-align: top;
        }
        
        .company-info {
            width: 55%;
        }
        
        .invoice-title {
            width: 45%;
            text-align: right;
        }
        
        .company-info h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #000;
        }
        
        .company-info p {
            font-size: 11px;
            color: #333;
            margin-bottom: 3px;
            line-height: 1.4;
        }
        
        .invoice-title h1 {
            font-size: 42px;
            font-weight: 700;
            color: #000;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .balance-due {
            background: #fff8dc;
            padding: 12px 20px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #ffd700;
            display: inline-block;
            min-width: 180px;
        }
        
        .balance-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .balance-amount {
            font-size: 16px;
            font-weight: 700;
            color: #000;
        }
        
        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .client-info, .invoice-meta {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        
        .client-info {
            padding-right: 30px;
        }
        
        .invoice-meta {
            padding-left: 30px;
        }
        
        .client-info h4, .invoice-meta h4 {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .client-name {
            font-size: 16px;
            font-weight: 700;
            color: #000;
            margin-bottom: 15px;
        }
        
        .object-info {
            margin-top: 15px;
        }
        
        .object-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .object-details {
            font-size: 13px;
            color: #333;
            line-height: 1.5;
        }
        
        .invoice-meta-item {
            margin-bottom: 6px;
            font-size: 12px;
            display: table;
            width: 100%;
        }
        
        .meta-label {
            color: #666;
            display: table-cell;
            width: 55%;
        }
        
        .meta-value {
            color: #000;
            font-weight: 500;
            display: table-cell;
            width: 45%;
            text-align: right;
        }
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            margin-top: 20px;
        }
        
        .services-table thead {
            background: #5a6c7d;
            color: white;
        }
        
        .services-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .services-table td {
            padding: 12px 8px;
            font-size: 12px;
            color: #333;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .services-table .center {
            text-align: center;
        }
        
        .services-table .right {
            text-align: right;
        }
        
        .totals {
            width: 100%;
            margin-top: 20px;
        }
        
        .totals-wrapper {
            margin-left: auto;
            width: 350px;
        }
        
        .total-row {
            padding: 8px 0;
            border-bottom: 1px solid #e5e5e5;
            display: table;
            width: 100%;
        }
        
        .total-row:last-child {
            border-bottom: 2px solid #ffd700;
            font-weight: 700;
            padding-top: 12px;
        }
        
        .total-label {
            color: #666;
            display: table-cell;
            width: 60%;
            font-size: 13px;
        }
        
        .total-amount {
            font-weight: 500;
            color: #000;
            display: table-cell;
            width: 40%;
            text-align: right;
            font-size: 13px;
        }
        
        .total-row:last-child .total-amount {
            font-weight: 700;
        }
        
        .terms {
            margin-top: 40px;
            padding: 18px 20px;
            background: #fff8dc;
            border-radius: 6px;
            border-left: 4px solid #ffd700;
            page-break-inside: avoid;
        }
        
        .terms h4 {
            font-size: 14px;
            color: #000;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .terms p {
            font-size: 12px;
            color: #333;
            margin-bottom: 6px;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        
        .company-details {
            margin-top: 8px;
            font-size: 10px;
            color: #999;
        }
        
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                margin: 0;
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="decorative-circles">
                <div class="circle"></div>
                <div class="circle"></div>
            </div>
            <div class="logo">LA STATION</div>
            <div class="subtitle">ESPACE COWORKING</div>
        </div>
        
        <div class="content">
            <div class="invoice-header">
                <div class="company-info">
                    <h3>{{ $company['name'] }}</h3>
                    <p>{{ $company['address'] }}</p>
                    <p>ICE : {{ $company['ice'] }} &nbsp; IF : {{ $company['if'] }} &nbsp; RC : {{ $company['rc'] }}</p>
                    <p>{{ $company['city'] }}</p>
                    <p>{{ $company['country'] }}</p>
                </div>
                
                <div class="invoice-title">
                    <h1>DEVIS</h1>
                    <div class="invoice-number">N° {{ $devis->devis_number }}</div>
                    <div class="balance-due">
                        <div class="balance-label">Montant total</div>
                        <div class="balance-amount">{{ number_format($devis->total_amount, 2, ',', ' ') }} MAD</div>
                    </div>
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="client-info">
                    <h4>Client</h4>
                    <div class="client-name">{{ $devis->client_name }}</div>
                    <div class="object-info">
                        <div class="object-label">Objet :</div>
                        <div class="object-details">{{ $devis->description }}</div>
                    </div>
                </div>
                
                <div class="invoice-meta">
                    <div class="invoice-meta-item">
                        <span class="meta-label">Date du devis :</span>
                        <span class="meta-value">{{ $devis->issued_at->format('d M. Y') }}</span>
                    </div>
                    <div class="invoice-meta-item">
                        <span class="meta-label">Valable jusqu'au :</span>
                        <span class="meta-value">{{ $devis->valid_until->format('d M. Y') }}</span>
                    </div>
                    <div class="invoice-meta-item">
                        <span class="meta-label">Statut :</span>
                        <span class="meta-value">{{ $devis->status_label }}</span>
                    </div>
                </div>
            </div>
            
            <table class="services-table">
                <thead>
                    <tr>
                        <th class="center" style="width: 8%;">N°</th>
                        <th style="width: 42%;">DESCRIPTION</th>
                        <th class="center" style="width: 12%;">QTÉ</th>
                        <th class="right" style="width: 15%;">PU HT</th>
                        <th class="center" style="width: 10%;">TVA %</th>
                        <th class="right" style="width: 13%;">TOTAL HT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="center">1</td>
                        <td>{{ $devis->description }}</td>
                        <td class="center">1.00</td>
                        <td class="right">{{ number_format($devis->amount, 2, ',', ' ') }}</td>
                        <td class="center">{{ $devis->tax_amount > 0 ? number_format(($devis->tax_amount / $devis->amount) * 100, 2, ',', ' ') : '0.00' }}</td>
                        <td class="right">{{ number_format($devis->amount, 2, ',', ' ') }}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="totals">
                <div class="totals-wrapper">
                    <div class="total-row">
                        <span class="total-label">TOTAL HT :</span>
                        <span class="total-amount">{{ number_format($devis->amount, 2, ',', ' ') }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">TVA :</span>
                        <span class="total-amount">{{ number_format($devis->tax_amount, 2, ',', ' ') }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">TOTAL TTC :</span>
                        <span class="total-amount">{{ number_format($devis->total_amount, 2, ',', ' ') }} MAD</span>
                    </div>
                </div>
            </div>
            
            @if($devis->terms)
            <div class="terms">
                <h4>Conditions du devis</h4>
                <p>{{ $devis->terms }}</p>
            </div>
            @else
            <div class="terms">
                <h4>Conditions du devis</h4>
                <p><strong>Validité :</strong> Ce devis est valable {{ $devis->valid_until->diffInDays($devis->issued_at) }} jours à compter de sa date d'émission.</p>
                <p><strong>Modalités de paiement :</strong> Paiement à la commande ou selon accord préalable.</p>
                <p><strong>Livraison :</strong> Accès immédiat après confirmation et paiement.</p>
                <p><strong>Conditions générales :</strong> Les tarifs incluent l'accès aux espaces communs et aux services de base.</p>
            </div>
            @endif
            
            @if($devis->notes)
            <div class="terms" style="background: #e8f4f8; border-left-color: #3498db; margin-top: 15px;">
                <h4>Notes</h4>
                <p>{{ $devis->notes }}</p>
            </div>
            @endif
            
            <div class="footer">
                <div class="company-details">
                    ICE : {{ $company['ice'] }} &nbsp;&nbsp; RC : {{ $company['rc'] }} &nbsp;&nbsp; IF : {{ $company['if'] }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>