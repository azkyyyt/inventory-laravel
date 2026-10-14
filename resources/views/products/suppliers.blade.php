@extends('layouts.app')
@php $pageTitle = 'Supplier Produk - ' . $product->name; @endphp

@section('content')
<div class="mb-3">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Produk</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-secondary mb-1">{{ $product->category->name ?? '-' }}</span>
                <h3 class="fw-bold mb-1">{{ $product->name }}</h3>
                <p class="text-muted small mb-0">SKU: <code>{{ $product->sku }}</code> | Harga Jual: <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong> | Stok: <strong>{{ $product->stock }} unit</strong></p>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <!-- List Supplier Terhubung -->
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-truck-field me-2"></i>Daftar Supplier Produk Ini</h5>
                <span class="badge bg-primary">{{ count($attachedSuppliers) }} Supplier</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Supplier</th>
                                <th>Kontak / Telp</th>
                                <th class="text-end">Harga Pasokan (Beli)</th>
                                @if(session('user.role') === 'admin')<th class="text-center">Aksi</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attachedSuppliers as $sup)
                            <tr>
                                <td class="ps-3"><strong>{{ $sup->name }}</strong></td>
                                <td class="small">
                                    {{ $sup->contact_name ?: '-' }}<br>
                                    <span class="text-muted">{{ $sup->phone ?: '-' }}</span>
                                </td>
                                <td class="text-end">
                                    {{ $sup->pivot->supplier_price !== null ? 'Rp ' . number_format($sup->pivot->supplier_price, 0, ',', '.') : '-' }}
                                </td>
                                @if(session('user.role') === 'admin')
                                <td class="text-center">
                                    <form method="POST" action="{{ route('products.suppliers.detach', $product->id) }}" onsubmit="return confirm('Hapus relasi supplier ini?');">
                                        @csrf
                                        <input type="hidden" name="supplier_id" value="{{ $sup->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="fa-solid fa-unlink"></i> Lepas
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada supplier yang dihubungkan ke produk ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Tambah/Hubungkan Supplier (Admin Only) -->
    @if(session('user.role') === 'admin')
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-plus me-2"></i>Hubungkan Supplier Baru</h5>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('products.suppliers.attach', $product->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label fw-semibold">Pilih Supplier <span class="text-danger">*</span></label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($allSuppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="supplier_price" class="form-label fw-semibold">Harga Beli dari Supplier (Rp)</label>
                        <input type="number" step="0.01" class="form-control @error('supplier_price') is-invalid @enderror" id="supplier_price" name="supplier_price" placeholder="Contoh: 350000">
                        <div class="form-text">Harga pasokan dari supplier ini (opsional).</div>
                        @error('supplier_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fa-solid fa-link me-1"></i> Simpan Relasi (Junction Table)
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
