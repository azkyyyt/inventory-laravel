@extends('layouts.app')
@php $pageTitle = 'Kelola Pengguna'; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-0">Kelola Pengguna (Users)</h2>
        <p class="text-muted small mb-0">Pengaturan akun dan hak akses pengguna sistem (Admin Only)</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-user-plus me-1"></i> Tambah User Baru
    </a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Username</th>
                        <th>Nama Lengkap (Profile)</th>
                        <th>No. Telepon</th>
                        <th class="text-center">Role / Hak Akses</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usersList as $u)
                    <tr>
                        <td class="ps-3">#{{ $u->id }}</td>
                        <td><code>{{ $u->username }}</code></td>
                        <td><strong>{{ $u->profile->full_name ?? '-' }}</strong></td>
                        <td>{{ $u->profile->phone ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $u->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                {{ strtoupper($u->role) }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-user-pen"></i> Edit</a>
                            @if($u->id !== session('user.id'))
                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-user-xmark"></i> Hapus</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
