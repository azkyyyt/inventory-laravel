<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; }
        .card-login { border: none; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .brand-icon { font-size: 2.5rem; color: #3b82f6; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card card-login p-4 bg-white">
                <div class="text-center mb-4">
                    <div class="brand-icon mb-2"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <h4 class="fw-bold text-dark mb-1">Inventory System</h4>
                    <p class="text-muted small">Silakan masuk ke akun Anda</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label text-secondary small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="Masukkan username">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label text-secondary small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center text-muted small">
                    <div class="fw-semibold text-secondary mb-1">Akun Demo Pengujian:</div>
                    <div><i class="fa-solid fa-user-shield me-1 text-danger"></i> <strong>admin</strong> / <code>admin123</code> (Role: Admin)</div>
                    <div><i class="fa-solid fa-user-gear me-1 text-primary"></i> <strong>staff</strong> / <code>staff123</code> (Role: Staff)</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
