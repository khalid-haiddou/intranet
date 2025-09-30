<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $company['name'] }} - Facture {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f8f9fa; color: #333; line-height: 1.4; }
        .invoice-container { max-width: 800px; margin: 20px auto; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
        .decorative-circles { position: absolute; top: 20px; right: 20px; z-index: 1; }
        .circle { width: 30px; height: 30px; background: #ffd700; border-radius: 50%; display: inline-block; margin-left: 10px; }
        .header { padding: 40px; background: white; color: black; position: relative; z-index: 2; border-bottom: 1px solid #eee; }
        .logo { font-size: 32px; font-weight: 700; letter-spacing: 3px; margin-bottom: 8px; }
        .subtitle { font-size: 14px; font-weight: 300; letter-spacing: 2px; color: #666; }
        .content { padding: 40px; position: relative; z-index: 2; }
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .company-info { float: left; width: 50%; }
        .company-info h3 { font-size: 18px; font-weight: 600; margin-bottom: 10px; color: #2c3e50; }
        .company-info p { font-size: 14px; color: #666; margin-bottom: 4px; }
        .invoice-title { float: right; width: 50%; text-align: right; }
        .invoice-title h1 { font-size: 48px; font-weight: 300; color: #2c3e50; margin-bottom: 5px; }
        .invoice-number { font-size: 16px; color: #666; margin-bottom: 20px; }
        .balance-due { background: #e8f4fd; padding: 15px; border-radius: 8px; text-align: center; }
        .balance-label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .balance-amount { font-size: 18px; font-weight: 600; color: #2c3e50; }
        .clear { clear: both; }
        .invoice-details { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .client-info h4, .invoice-meta h4 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .client-name { font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 15px; }
        .object-info { margin-bottom: 10px; }
        .object-label { font-size: 14px; color: #666; margin-bottom: 5px; }
        .object-details { font-size: 16px; color: #2c3e50; }
        .invoice-meta-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 14px; }
        .meta-label { color: #666; }
        .meta-value { color: #2c3e50; font-weight: 500; }
        .services-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .services-table thead { background: #5a6c7d; color: white; }
        .services-table th, .services-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .services-table th { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .services-table td { font-size: 14px; }
        .services-table .center { text-align: center; }
        .services-table .right { text-align: right; }
        .totals { margin-left: auto; width: 300px; }
        .total-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
        .total-row:last-child { border-bottom: 2px solid #4facfe; font-weight: 600; font-size: 16px; color: #2c3e50; }
        .total-label { color: #666; }
        .total-amount { font-weight: 500; color: #2c3e50; }
        .footer { margin-top: 60px; padding-top: 30px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #999; }
        .company-details { margin-top: 10px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="decorative-circles">
                <div class="circle"></div>
                <div class="circle"></div>
            </div>
            <div class="logo">{{ $company['name'] }}</div>
            <div class="subtitle">ESPACE COWORKING</div>
        </div>
        
        <div class="content">
            <div class="invoice-header">
                <div class="company-info">
                    <h3>{{ $company['name'] }}</h3>
                    <p>{{ $company['address'] }}</p>
                    <p>ICE : {{ $company['ice'] }} &nbsp;&nbsp; IF : {{ $company['if'] }} &nbsp;&nbsp; RC : {{ $company['rc'] }}</p>
                    <p>{{ $company['city'] }}</p>
                    <p>{{ $company['country'] }}</p>
                </div>
                
                <div class="invoice-title">
                    <h1>FACTURE</h1>
                    <div class="invoice-number">N° {{ $invoice->invoice_number }}</div>
                    <div class="balance-due">
                        <div class="balance-label">Solde dû</div>
                        <div class="balance-amount">{{ $invoice->status === 'paid' ? '0.00' : number_format($invoice->total_amount, 2) }}MAD</div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            
            <div class="invoice-details">
                <div class="client-info">
                    <h4>Facturer à</h4>
                    <div class="client-name">{{ $invoice->user->display_name }}</div>
                    <div class="object-info">
                        <div class="object-label">Objet :</div>
                        <div class="object-details">{{ $invoice->description }}</div>
                    </div>
                </div>
                
                <div class="invoice-meta">
                    <div class="invoice-meta-item">
                        <span class="meta-label">Date de facture :</span>
                        <span class="meta-value">{{ $invoice->issued_at->format('d M Y') }}</span>
                    </div>
                    <div class="invoice-meta-item">
                        <span class="meta-label">Conditions :</span>
                        <span class="meta-value">Payable à réception</span>
                    </div>
                    <div class="invoice-meta-item">
                        <span class="meta-label">Date d'échéance :</span>
                        <span class="meta-value">{{ $invoice->due_at ? $invoice->due_at->format('d M Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <table class="services-table">
                <thead>
                    <tr>
                        <th>SERVICE</th>
                        <th>Description</th>
                        <th class="center">QTÉ</th>
                        <th class="right">PU HT</th>
                        <th class="center">TVA</th>
                        <th class="right">TOTAL HT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="center">1</td>
                        <td>{{ $invoice->description }}</td>
                        <td class="center">1.00</td>
                        <td class="right">{{ number_format($invoice->amount, 2) }}</td>
                        <td class="center">20.00</td>
                        <td class="right">{{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-row">
                    <span class="total-label">TOTAL HT :</span>
                    <span class="total-amount">{{ number_format($invoice->amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">TVA (20%)</span>
                    <span class="total-amount">{{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">TOTAL TTC</span>
                    <span class="total-amount">{{ number_format($invoice->total_amount, 2) }}MAD</span>
                </div>
            </div>
            
            <div class="footer">
                <p>Merci pour votre confiance!</p>
                <div class="company-details">
                    ICE : {{ $company['ice'] }} &nbsp;&nbsp;&nbsp; RC : {{ $company['rc'] }} &nbsp;&nbsp;&nbsp; IF : {{ $company['if'] }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>