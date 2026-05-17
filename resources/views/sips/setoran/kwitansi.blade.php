<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Setoran #{{ str_pad($setoran->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #111;
            background: #f5f5f5;
        }

        .page {
            width: 80mm;
            min-height: 100mm;
            background: #fff;
            margin: 20px auto;
            padding: 16px 14px;
            border: 1px solid #ddd;
        }

        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 10px; margin-bottom: 10px; }
        .header h2 { font-size: 15px; font-weight: 700; }
        .header p  { font-size: 11px; color: #555; margin-top: 2px; }

        .kw-id { text-align: center; font-size: 11px; color: #777; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        td { padding: 3px 0; vertical-align: top; font-size: 12px; }
        td:last-child { text-align: right; }
        .label { color: #555; width: 45%; }

        .divider { border: none; border-top: 1px dashed #aaa; margin: 8px 0; }

        .items-header { font-weight: 600; font-size: 11px; color: #444; text-transform: uppercase; margin-bottom: 4px; }

        .item-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 3px; }
        .item-row .item-name { flex: 1; }
        .item-row .item-sub  { text-align: right; white-space: nowrap; }

        .badge-dipilah  { font-size: 10px; color: #16a34a; }
        .badge-campur   { font-size: 10px; color: #ca8a04; }

        .total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: 14px; margin-top: 8px; }

        .status-box {
            text-align: center;
            border: 2px solid;
            border-radius: 6px;
            padding: 4px 0;
            margin: 10px 0;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .status-box.paid   { border-color: #16a34a; color: #16a34a; }
        .status-box.unpaid { border-color: #dc2626; color: #dc2626; }

        .footer { text-align: center; font-size: 10px; color: #888; border-top: 1px dashed #ccc; padding-top: 8px; margin-top: 10px; }

        @media print {
            body { background: #fff; }
            .page { margin: 0; border: none; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center; padding: 12px 0; background:#fff; border-bottom:1px solid #eee;">
    <button onclick="window.print()"
            style="background:#2f55d4;color:#fff;border:none;padding:8px 24px;border-radius:6px;font-size:13px;cursor:pointer;">
        🖨️ Cetak / Simpan PDF
    </button>
    <a href="{{ route('sips.setoran.show', $setoran->id) }}"
       style="margin-left:12px;font-size:13px;color:#555;text-decoration:none;">← Kembali</a>
</div>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <h2>SIPS — Desa Banjarsari</h2>
        <p>Sistem Informasi Pemilahan Sampah</p>
        <p>Kwitansi Setoran Sampah</p>
    </div>

    <p class="kw-id">No. #{{ str_pad($setoran->id, 5, '0', STR_PAD_LEFT) }}</p>

    {{-- Info warga --}}
    <table>
        <tr>
            <td class="label">Warga</td>
            <td>{{ $setoran->warga->nama }}</td>
        </tr>
        <tr>
            <td class="label">RT / Dusun</td>
            <td>RT {{ $setoran->warga->rt }} / {{ $setoran->warga->dusun }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td>{{ $setoran->tanggal_setoran->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Petugas</td>
            <td>{{ $setoran->petugas->name ?? '-' }}</td>
        </tr>
    </table>

    <hr class="divider">

    {{-- Item detail --}}
    <p class="items-header">Detail Sampah</p>

    @foreach($setoran->items as $item)
    <div class="item-row">
        <div class="item-name">
            @if($item->status_pemilahan === 'tidak_dipilah')
                Sampah Tidak Dipilah<br>
                <span class="badge-campur">⚠ Tidak Dipilah</span>
                {{ number_format($item->berat_kg, 1, ',', '.') }} kg
            @else
                {{ $item->tarifItem?->nama_item ?? 'Item Sampah' }}<br>
                @if($item->status_pemilahan === 'dipilah')
                <span class="badge-dipilah">✓ Dipilah</span>
                @else
                <span style="font-size:10px;color:#777;">- Tidak dicatat</span>
                @endif
                {{ number_format($item->berat_kg, 1, ',', '.') }} kg
                × Rp {{ number_format($item->harga_per_kg_saat_itu ?? 0, 0, ',', '.') }}
            @endif
        </div>
        <div class="item-sub">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
    </div>
    @endforeach

    <hr class="divider">

    <div class="total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}</span>
    </div>

    {{-- Payment status --}}
    @if($setoran->status_pembayaran === 'sudah_dibayar' && $setoran->pembayaran)
    <div class="status-box paid">SUDAH DIBAYAR</div>
    <table>
        <tr>
            <td class="label">Tgl. Bayar</td>
            <td>{{ $setoran->pembayaran->tanggal_bayar->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Dibayar</td>
            <td>Rp {{ number_format($setoran->pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
        </tr>
        @if($setoran->pembayaran->catatan)
        <tr>
            <td class="label">Catatan</td>
            <td>{{ $setoran->pembayaran->catatan }}</td>
        </tr>
        @endif
    </table>
    @else
    <div class="status-box unpaid">BELUM DIBAYAR</div>
    @endif

    @if($setoran->catatan_kondisi)
    <hr class="divider">
    <p style="font-size:11px;color:#555;">Catatan: {{ $setoran->catatan_kondisi }}</p>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Dokumen ini sah tanpa tanda tangan.</p>
    </div>

</div>

</body>
</html>
