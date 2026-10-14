@extends('layouts.app')
@php $pageTitle = 'Histori Transaksi Stok'; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-0">Histori Transaksi Stok</h2>
        <p class="text-muted small mb-0">Catatan lengkap mutasi barang masuk (IN) dan barang keluar (OUT)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stock.in') }}" class="btn btn-success btn-sm"><i class="fa-solid fa-plus me-1"></i> Barang Masuk</a>
        <a href="{{ route('stock.out') }}" class="btn btn-danger btn-sm"><i class="fa-solid fa-minus me-1"></i> Barang Keluar</a>
    </div>
</div>

<!-- Filter Type -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light py-2">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold small text-secondary">Filter Tipe Transaksi:</span>
            <a href="{{ route('stock.history') }}" class="btn btn-sm {{ empty($typeFilter) ? 'btn-dark' : 'btn-outline-dark' }}">Semua</a>
            <a href="{{ route('stock.history', ['type' => 'IN']) }}" class="btn btn-sm {{ $typeFilter === 'IN' ? 'btn-success' : 'btn-outline-success' }}"><i class="fa-solid fa-arrow-down me-1"></i> Barang Masuk (IN)</a>
            <a href="{{ route('stock.history', ['type' => 'OUT']) }}" class="btn btn-sm {{ $typeFilter === 'OUT' ? 'btn-danger' : 'btn-outline-danger' }}"><i class="fa-solid fa-arrow-up me-1"></i> Barang Keluar (OUT)</a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Waktu &amp; Tanggal</th>
                        <th>Tipe</th>
                        <th>SKU / Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Jumlah (Qty)</th>
                        <th>Petugas (User)</th>
                        <th class="pe-3">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                    <tr>
                        <td class="ps-3 text-muted small">
                            <i class="fa-regular fa-clock me-1"></i>{{ $m->created_at }}
                        </td>
                        <td>
                            @if($m->type === 'IN')
                                <span class="badge bg-success"><i class="fa-solid fa-arrow-down me-1"></i>MASUK</span>
                            @else
                                <span class="badge bg-danger"><i class="fa-solid fa-arrow-up me-1"></i>KELUAR</span>
                            @endif
                        </td>
                        <td>
                            <code>{{ $m->product->sku ?? '-' }}</code><br>
                            <strong>{{ $m->product->name ?? '-' }}</strong>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $m->product->category->name ?? '-' }}</span></td>
                        <td class="text-center font-monospace fw-bold fs-6 {{ $m->type === 'IN' ? 'text-success' : 'text-danger' }}">
                            {{ $m->type === 'IN' ? '+' : '-' }}{{ number_format($m->quantity) }}
                        </td>
                        <td class="small">
                            <i class="fa-solid fa-user me-1 text-secondary"></i>{{ $m->user->profile->full_name ?? ($m->user->username ?? '-') }}
                            <span class="badge bg-secondary">{{ $m->user->role ?? '' }}</span>
                        </td>
                        <td class="pe-3 small text-muted">{{ $m->notes ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data histori transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
