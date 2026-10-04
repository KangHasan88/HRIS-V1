<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Kurmigo HRIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#0A66C2', 600: '#2563EB', 700: '#1D4ED8' }, accent: { DEFAULT: '#10B981', 100: '#D1FAE5' } }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); min-height: 100vh; }
        .login-card { background: rgba(255,255,255,.96); box-shadow: 0 4px 6px -1px rgba(0,0,0,.08), 0 20px 44px rgba(15,23,42,.10); border-radius: 16px; border: 1px solid rgba(226,232,240,.9); }
        .input-field { border: 1px solid #d1d5db; background: #fff; transition: all .2s ease; }
        .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); outline: none; }
        .submit-btn { background: #2563eb; transition: all .2s ease; }
        .submit-btn:hover { background: #1d4ed8; transform: translateY(-1px); }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center p-6">
    <main class="login-card w-full max-w-md p-8">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-100 text-accent">
                <i class="bi bi-key-fill text-2xl"></i>
            </div>
            <h2 class="mb-2 text-3xl font-bold text-gray-900">Reset Password</h2>
            <p class="text-gray-600">Masukkan email akun HRIS untuk menerima link reset password.</p>
        </div>

        @if (session('status'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                <div class="relative">
                    <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input-field w-full rounded-xl py-3 pl-10 pr-4 text-sm text-slate-800" placeholder="admin@kurmigo.id">
                </div>
            </div>
            <button type="submit" class="submit-btn flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/10">
                Kirim Link Reset
            </button>
            <a href="{{ route('login') }}" class="block text-center text-sm font-semibold text-blue-600 hover:text-blue-700">Kembali ke login</a>
        </form>
    </main>
</body>
</html>
