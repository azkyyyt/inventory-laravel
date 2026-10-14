@extends('layouts.app')
@php $pageTitle = 'Dashboard'; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Inventory</h2>
        <p class="text-muted small mb-0">Ringkasan statistik persediaan barang &amp; laporan agregat</p>
    </div>
    <div>
        <a href="{{ route('stock.in') }}" class="btn btn-success btn-sm me-2"><i class="fa-solid fa-plus me-1"></i> Barang Masuk</a>
        <a href="{{ route('stock.out') }}" class="btn btn-danger btn-sm"><i class="fa-solid fa-minus me-1"></i> Barang Keluar</a>
    </div>
</div>

<!-- 5 Widget Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg">
        <div class="card card-stat bg-white shadow-sm p-3 border-start border-4 border-primary">
            <div class="text-muted small text-uppercase fw-bold">Total Produk</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($totalProducts) }}</div>
            <div class="small text-muted mt-1"><i class="fa-solid fa-box text-primary me-1"></i> Item terdaftar</div>
        </div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card card-stat bg-white shadow-sm p-3 border-start border-4 border-success">
            <div class="text-muted small text-uppercase fw-bold">Nilai Inventory</div>
            <div class="fs-4 fw-bold text-dark mt-1">Rp {{ number_format($inventoryValue, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1"><i class="fa-solid fa-sack-dollar text-success me-1"></i> Total estimasi nilai</div>
        </div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card card-stat bg-white shadow-sm p-3 border-start border-4 border-warning">
            <div class="text-muted small text-uppercase fw-bold">Stok Rendah</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($lowStockCount) }}</div>
            <div class="small text-muted mt-1"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Perlu restok</div>
        </div>
    </div>
    <div class="col-md-6 col-lg">
        <div class="card card-stat bg-white shadow-sm p-3 border-start border-4 border-info">
            <div class="text-muted small text-uppercase fw-bold">Barang Masuk (IN)</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($totalIn) }}</div>
            <div class="small text-muted mt-1"><i class="fa-solid fa-arrow-down text-info me-1"></i> Akumulasi unit masuk</div>
        </div>
    </div>
    <div class="col-md-6 col-lg">
        <div class="card card-stat bg-white shadow-sm p-3 border-start border-4 border-danger">
            <div class="text-muted small text-uppercase fw-bold">Barang Keluar (OUT)</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($totalOut) }}</div>
            <div class="small text-muted mt-1"><i class="fa-solid fa-arrow-up text-danger me-1"></i> Akumulasi unit keluar</div>
        </div>
    </div>
</div>

@if(count($lowStockProducts) > 0)
<div class="alert alert-warning shadow-sm mb-4" role="alert">
    <div class="d-flex align-items-center mb-2">
        <i class="fa-solid fa-triangle-exclamation fa-lg me-2"></i>
        <h5 class="alert-heading mb-0 fw-bold">Peringatan Produk Stok Rendah</h5>
    </div>
    <p class="small mb-2">Produk berikut memiliki stok di bawah atau sama dengan batas minimum stok (<code>stock &lt;= minimum_stock</code>):</p>
    <div class="table-responsive">
        <table class="table table-sm table-bordered bg-white mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Stok Saat Ini</th><th>Min. Stok</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $lp)
                <tr>
                    <td><code>{{ $lp->sku }}</code></td>
                    <td><strong>{{ $lp->name }}</strong></td>
                    <td>{{ $lp->category->name ?? '-' }}</td>
                    <td><span class="badge bg-danger">{{ $lp->stock }}</span></td>
                    <td>{{ $lp->minimum_stock }}</td>
                    <td>
                        <a href="{{ route('stock.in', ['product_id' => $lp->id]) }}" class="btn btn-sm btn-success py-0 px-2">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Stok
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Laporan Inventory Aggregate Query -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-chart-pie me-2"></i>Laporan Rincian Inventory &amp; Pergerakan Stok</h5>
        <span class="badge bg-secondary">Query Agregat</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Total Masuk (IN)</th>
                        <th class="text-center">Total Keluar (OUT)</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-end pe-3">Nilai Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryReports as $rep)
                    <tr>
                        <td class="ps-3"><code>{{ $rep->sku }}</code></td>
                        <td><strong>{{ $rep->name }}</strong></td>
                        <td><span class="badge bg-light text-dark border">{{ $rep->category->name ?? '-' }}</span></td>
                        <td class="text-end">Rp {{ number_format($rep->price, 0, ',', '.') }}</td>
                        <td class="text-center text-success font-monospace">+{{ number_format($rep->total_in ?? 0) }}</td>
                        <td class="text-center text-danger font-monospace">-{{ number_format($rep->total_out ?? 0) }}</td>
                        <td class="text-center">
                            @if($rep->stock <= $rep->minimum_stock)
                                <span class="badge bg-danger">{{ number_format($rep->stock) }} (Rendah)</span>
                            @else
                                <span class="badge bg-success">{{ number_format($rep->stock) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-3 fw-bold">Rp {{ number_format($rep->stock * $rep->price, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
