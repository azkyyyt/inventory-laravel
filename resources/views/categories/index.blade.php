@extends('layouts.app')
@php $pageTitle = 'Kelola Kategori'; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-0">Kelola Kategori</h2>
        <p class="text-muted small mb-0">Master data kategori produk inventory</p>
    </div>
    @if(session('user.role') === 'admin')
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Tambah Kategori
    </a>
    @endif
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah Produk</th>
                        @if(session('user.role') === 'admin')<th class="text-end pe-3">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $c)
                    <tr>
                        <td class="ps-3">#{{ $c->id }}</td>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->description ?: '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">{{ number_format($c->products_count) }} produk</span>
                        </td>
                        @if(session('user.role') === 'admin')
                        <td class="text-end pe-3">
                            <a href="{{ route('categories.edit', $c->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('Hapus kategori ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada kategori terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
