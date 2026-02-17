<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>
        @if(count($orders) === 1)
            Invoice_{{ $orders[0]['order']->id }}_{{ $orders[0]['order']->customer->nama ?? 'Customer' }}
        @else
            Bulk_Invoices_{{ date('Y-m-d') }}
        @endif
    </title>
    <style>
        @page {
            margin: 15px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .page {
            background: #fff;
            padding: 0px 5px;
            margin: 0;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .header-brand {
            width: 50%;
            vertical-align: top;
        }

        .order-info-td {
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .order-info-content {
            display: inline-block;
            text-align: left;
            width: 200px; /* Force a consistent width for labels/values */
        }

        .order-info-content .label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .order-info-content .value {
            font-weight: bold;
            font-size: 12px;
        }

        .greeting {
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
        }

        th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        td {
            padding: 5px;
            vertical-align: top;
            border-bottom: 0.5px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 300px;
            margin-left: auto;
            margin-top: 10px;
        }

        .summary td {
            padding: 2px 5px;
            border-bottom: none;
        }

        .total td {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .bank-info {
            margin-top: 20px;
            font-size: 11px;
        }

        .note {
            margin-top: 10px;
            font-size: 10px;
            font-style: italic;
            border: 1px solid red;
            padding: 8px;
        }

        .bottom-section {
            border-top: 1px dashed #000;
            margin-top: 20px;
            padding-top: 10px;
            width: 100%;
        }

        .bundle-children {
            margin-top: 2px;
            padding-left: 10px;
            border-left: 2px solid #ccc;
        }

        .bundle-child {
            font-size: 10px;
            color: #666;
            padding: 1px 0;
        }
    </style>
</head>

<body>


@foreach($orders as $data)
    @php
        $order = $data['order'];
        $groupedItems = $data['groupedItems'];
        $grandTotal = $data['grandTotal'];
    @endphp
    <div class="page" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
        <table class="header-table">
            <tr>
                <td class="header-brand">
                    <img src="{{ public_path('assets/logo/logoInv.png') }}" alt="AQWAM" style="max-height: 120px;">
                </td>
                <td class="order-info-td">
                    <div class="order-info-content">
                        <div class="label">Tanggal</div>
                        <div class="value">{{ $order->created_at }}</div>
                        <div class="label">Nomor Invoice</div>
                        <div class="value">{{ $order->id }}</div>
                        <div class="label">Nama Admin</div>
                        <div class="value">Admin Aqwam</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="greeting">
            Assalamu'alaikum Warahmatullahi Wabarakatuh<br>
            Kepada <strong>{{ $order->customer->nama ?? '-' }}</strong><br>
            Terimakasih sudah berbelanja di AQWAM STORE, Berikut rincian dari orderan anda:
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Jml</th>
                    <th>Berat</th>
                    <th>Harga</th>
                    <th>Disc</th>
                    <th class="text-right">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupedItems as $item)
                <tr>
                    <td>
                        @if($item['isBundle'])
                            <strong>{{ $item['nama_promo'] }}</strong>
                            <div class="bundle-children">
                                @foreach($item['children'] as $child)
                                    <div class="bundle-child">• {{ $child->barang->judul_buku ?? '-' }} ({{ $child->jumlah }}x)</div>
                                @endforeach
                            </div>
                        @else
                            {{ $item['detail']->barang->judul_buku ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $item['totalQty'] }}</td>
                    <td>{{ number_format($item['totalBerat'] / 1000, 2) }} kg</td>
                    <td>{{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td>{{ $item['diskon'] > 0 ? $item['diskon'] . '%' : '-' }}</td>
                    <td class="text-right">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tr><td>Jumlah</td><td class="text-right">{{ number_format($order->total_harga, 0, ',', '.') }}</td></tr>
            <tr><td>Diskon</td><td class="text-right">{{ number_format($order->totalDiskon, 0, ',', '.') }}</td></tr>
            <tr><td>Diskon Kode Unik</td><td class="text-right">{{ number_format($order->diskonKodeUnik, 0, ',', '.') }}</td></tr>
            <tr><td>Biaya Expedisi</td><td class="text-right">{{ number_format($order->biayaExpedisi, 0, ',', '.') }}</td></tr>
            <tr class="total"><td>TOTAL</td><td class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td></tr>
        </table>

        <table class="bottom-section">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="bank-info">
                        <strong>Rekening Pembayaran</strong><br>
                        - Mandiri 138-00-2980809-9 an PT AQWAM MEDIA PROFETIKA<br>
                        - BSI 700-2055-273 an PT AQWAM MEDIA PROFETIKA
                    </div>

                    <p>Semoga Allah Memberi Keberkahan</p>
                    <div class="note">
                        NOTE: Untuk mendukung kecepatan dan ketepatan pengiriman order, harap transfer sesuai dengan nominal
                        invoice, melalui satu rekening bank yang dipilih.
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div>
                        <strong>#Pengirim</strong><br>
                        {{ $order->nama_pengirim ?? '-' }} ({{ $order->telephone_pengirim ?? '-' }})
                    </div>
                    <br>
                    <div>
                        <strong>#Tujuan</strong><br>
                        {{ $order->nama_penerima ?? '-' }} ({{ $order->telephone_penerima ?? '-' }})<br>
                        {{ $order->alamat ?? '-' }}<br>
                        {{ $order->kecamatan ? 'Kec. ' . $order->kecamatan . ', ' : '' }}{{ $order->kab_kota ? 'Kab. ' . $order->kab_kota : '' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endforeach

</body>

</html>
