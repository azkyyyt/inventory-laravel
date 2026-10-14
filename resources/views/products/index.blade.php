@extends('layouts.app')
@php $pageTitle = 'Daftar Produk'; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-0">Kelola Produk</h2>
        <p class="text-muted small mb-0">Master data produk inventory toko</p>
    </div>
    @if(session('user.role') === 'admin')
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Tambah Produk
    </a>
    @endif
</div>

<!-- Form Search, Filter, & Sorting -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
            <!-- Search input -->
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama produk atau SKU..." value="{{ $search }}">
                </div>
            </div>

            <!-- Category filter -->
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-md-2">
                <select name="sort" class="form-select form-select-sm">
                    <option value="id" {{ request('sort', 'id') === 'id' ? 'selected' : '' }}>Urut: ID</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Urut: Nama</option>
                    <option value="sku" {{ request('sort') === 'sku' ? 'selected' : '' }}>Urut: SKU</option>
                    <option value="price" {{ request('sort') === 'price' ? 'selected' : '' }}>Urut: Harga</option>
                    <option value="stock" {{ request('sort') === 'stock' ? 'selected' : '' }}>Urut: Stok</option>
                </select>
            </div>

            <!-- Sort Direction -->
            <div class="col-md-2">
                <select name="dir" class="form-select form-select-sm">
                    <option value="ASC" {{ $sortDir === 'ASC' ? 'selected' : '' }}>Menaik (ASC)</option>
                    <option value="DESC" {{ $sortDir === 'DESC' ? 'selected' : '' }}>Menurun (DESC)</option>
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark w-100"><i class="fa-solid fa-filter"></i></button>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table Listing -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">SKU</th>
                        <th>
                            <a href="{{ route('products.index', array_merge(request()->all(), ['sort' => 'name', 'dir' => ($sortInput === 'name' && $sortDir === 'ASC') ? 'DESC' : 'ASC'])) }}" class="text-white text-decoration-none">
                                Nama Produk {{ $sortInput === 'name' ? ($sortDir === 'ASC' ? '▲' : '▼') : '' }}
                            </a>
                        </th>
                        <th>Kategori</th>
                        <th class="text-end">
                            <a href="{{ route('products.index', array_merge(request()->all(), ['sort' => 'price', 'dir' => ($sortInput === 'price' && $sortDir === 'ASC') ? 'DESC' : 'ASC'])) }}" class="text-white text-decoration-none">
                                Harga {{ $sortInput === 'price' ? ($sortDir === 'ASC' ? '▲' : '▼') : '' }}
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('products.index', array_merge(request()->all(), ['sort' => 'stock', 'dir' => ($sortInput === 'stock' && $sortDir === 'ASC') ? 'DESC' : 'ASC'])) }}" class="text-white text-decoration-none">
                                Stok {{ $sortInput === 'stock' ? ($sortDir === 'ASC' ? '▲' : '▼') : '' }}
                            </a>
                        </th>
                        <th class="text-center">Min. Stok</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td class="ps-3"><code>{{ $p->sku }}</code></td>
                        <td><strong>{{ $p->name }}</strong></td>
                        <td><span class="badge bg-secondary">{{ $p->category->name ?? '-' }}</span></td>
                        <td class="text-end">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($p->stock <= $p->minimum_stock)
                                <span class="badge bg-danger" title="Stok berada di bawah atau sama dengan batas minimum!">{{ number_format($p->stock) }}</span>
                            @else
                                <span class="badge bg-success">{{ number_format($p->stock) }}</span>
                            @endif
                        </td>
                        <td class="text-center text-muted">{{ number_format($p->minimum_stock) }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('products.suppliers', $p->id) }}" class="btn btn-sm btn-outline-info me-1" title="Kelola Supplier (Many-to-Many)">
                                <i class="fa-solid fa-truck-field"></i> Supplier
                            </a>
                            @if(session('user.role') === 'admin')
                            <a href="{{ route('products.edit', $p->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Produk">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('products.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Produk">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada produk yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($products->lastPage() > 1)
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">
            Menampilkan halaman <strong>{{ $products->currentPage() }}</strong> dari <strong>{{ $products->lastPage() }}</strong> (Total {{ $products->total() }} data)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $products->previousPageUrl() }}">Sebelumnya</a>
                </li>
                @for($i = 1; $i <= $products->lastPage(); $i++)
                    <li class="page-item {{ $products->currentPage() === $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $products->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="page-item {{ !$products->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $products->nextPageUrl() }}">Selanjutnya</a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
