@extends('layouts.app')
@php $pageTitle = 'Kelola Supplier'; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-0">Kelola Supplier</h2>
        <p class="text-muted small mb-0">Master data pemasok produk toko</p>
    </div>
    @if(session('user.role') === 'admin')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Tambah Supplier
    </a>
    @endif
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Nama Supplier</th>
                        <th>Kontak PIC</th>
                        <th>Telepon &amp; Email</th>
                        <th>Alamat</th>
                        <th class="text-center">Produk Disuplai</th>
                        @if(session('user.role') === 'admin')<th class="text-end pe-3">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                    <tr>
                        <td class="ps-3"><strong>{{ $s->name }}</strong></td>
                        <td>{{ $s->contact_name ?: '-' }}</td>
                        <td>
                            <i class="fa-solid fa-phone text-muted me-1"></i>{{ $s->phone ?: '-' }}<br>
                            <i class="fa-solid fa-envelope text-muted me-1"></i>{{ $s->email ?: '-' }}
                        </td>
                        <td class="small">{{ $s->address ?: '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ number_format($s->products_count) }} item</span>
                        </td>
                        @if(session('user.role') === 'admin')
                        <td class="text-end pe-3">
                            <a href="{{ route('suppliers.edit', $s->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Hapus supplier ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada supplier terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
