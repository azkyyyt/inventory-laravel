@extends('layouts.app')
@php $pageTitle = 'Transaksi Barang Masuk (IN)'; @endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-arrow-down me-2"></i>Transaksi Barang Masuk (IN)</h5>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Transaksi Gagal:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('stock.in.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold">Pilih Produk <span class="text-danger">*</span></label>
                        <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ old('product_id', $productId) == $p->id ? 'selected' : '' }}>
                                    [{{ $p->sku }}] {{ $p->name }} (Stok saat ini: {{ $p->stock }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label fw-semibold">Jumlah Barang Masuk (Quantity) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" min="1" required placeholder="Contoh: 10">
                        <div class="form-text">Jumlah harus berupa bilangan positif lebih dari nol (> 0).</div>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Catatan / Keterangan Transaksi</label>
                        <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Restok dari supplier PT Megah Tech">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                        <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Proses Barang Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
