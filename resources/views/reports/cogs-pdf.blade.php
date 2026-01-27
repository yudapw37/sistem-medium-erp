<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan HPP</title>
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
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        .summary-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .summary-box .label {
            font-size: 11px;
            color: #666;
        }
        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
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
            background-color: #f5f5f5;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        tfoot tr {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN HARGA POKOK PENJUALAN (HPP)</h1>
        <p>Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}</p>
    </div>

    <div class="summary" style="display: table; width: 100%; margin-bottom: 20px;">
        <div style="display: table-cell; width: 33%; padding: 5px;">
            <div class="summary-box">
                <div class="label">Total HPP</div>
                <div class="value">Rp {{ number_format($totalCogs, 0, ',', '.') }}</div>
            </div>
        </div>
        <div style="display: table-cell; width: 33%; padding: 5px;">
            <div class="summary-box">
                <div class="label">Total Qty Terjual</div>
                <div class="value">{{ number_format($totalQty, 0, ',', '.') }}</div>
            </div>
        </div>
        <div style="display: table-cell; width: 33%; padding: 5px;">
            <div class="summary-box">
                <div class="label">Jumlah Produk</div>
                <div class="value">{{ count($cogsData) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th style="width: 100px;">Barcode</th>
                <th>Nama Produk</th>
                <th style="width: 100px;">Kategori</th>
                <th class="text-right" style="width: 100px;">HPP/Unit</th>
                <th class="text-right" style="width: 80px;">Qty</th>
                <th class="text-right" style="width: 120px;">Total HPP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cogsData as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono">{{ $item->barcode ?? '-' }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->category_name ?? '-' }}</td>
                <td class="text-right font-mono">Rp {{ number_format($item->current_buy_price, 0, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($item->total_cogs, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data HPP untuk periode ini</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="font-bold">TOTAL</td>
                <td class="text-right font-mono font-bold">{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($totalCogs, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d F Y H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
