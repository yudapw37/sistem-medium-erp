<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 11px;
        }
        
        .info {
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f4f4f4;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .status-finalized {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .purchase-group {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .purchase-header {
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PEMBELIAN</h1>
        <p>Aplikasi Kasir - Point of Sale</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info">
        <p><strong>Total Transaksi:</strong> {{ $purchases->count() }}</p>
        <p><strong>Total Grand Total:</strong> Rp {{ number_format($purchases->sum('grand_total'), 0, ',', '.') }}</p>
    </div>

    @foreach($purchases as $purchase)
    <div class="purchase-group">
        <div class="purchase-header">
            <table style="border: none; margin-bottom: 5px;">
                <tr style="border: none;">
                    <td style="border: none; width: 120px;"><strong>Invoice:</strong></td>
                    <td style="border: none;">{{ $purchase->invoice }}</td>
                    <td style="border: none; width: 120px;"><strong>Tanggal:</strong></td>
                    <td style="border: none;">{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none;"><strong>Supplier:</strong></td>
                    <td style="border: none;">{{ $purchase->supplier->name ?? '-' }}</td>
                    <td style="border: none;"><strong>Gudang:</strong></td>
                    <td style="border: none;">{{ $purchase->warehouse->name ?? '-' }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none;"><strong>Status:</strong></td>
                    <td style="border: none;">
                        <span class="status {{ $purchase->status === 'finalized' ? 'status-finalized' : 'status-draft' }}">
                            {{ $purchase->status === 'finalized' ? 'FINALIZED' : 'DRAFT' }}
                        </span>
                    </td>
                    <td style="border: none;"><strong>User:</strong></td>
                    <td style="border: none;">{{ $purchase->user->name ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Produk</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="20%" class="text-right">Harga Beli</th>
                    <th width="25%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->product->title ?? '-' }}</td>
                    <td class="text-center">{{ $detail->qty }}</td>
                    <td class="text-right">Rp {{ number_format($detail->buy_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->qty * $detail->buy_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="text-right"><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
            Cetak / Simpan sebagai PDF
        </button>
    </div>
</body>
</html>
