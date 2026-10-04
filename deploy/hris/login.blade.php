<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $isEssLogin = request()->getPort() === 8092
            || (int) request()->server('SERVER_PORT') === 8092
            || str_ends_with((string) request()->getHttpHost(), ':8092')
            || str_contains((string) session('url.intended'), '/ess');
        $brandTitle = $isEssLogin ? 'Kurmigo ESS' : 'Kurmigo HRIS';
        $brandSubtitle = $isEssLogin ? 'Employee Self Service' : 'Administration Panel';
        $loginTitle = $isEssLogin ? 'Masuk ESS' : 'Masuk HRIS';
        $loginCopy = $isEssLogin
            ? 'Gunakan akun karyawan untuk akses layanan mandiri.'
            : 'Gunakan akun yang sudah diberikan oleh admin.';
        $panelCards = $isEssLogin
            ? [
                ['title' => 'Employee Self Service', 'body' => 'Akses cuti, lembur, dinas, koreksi absensi, slip gaji, dan dokumen karyawan.'],
                ['title' => 'Mobile WebView Ready', 'body' => 'Tampilan disiapkan untuk browser, Android WebView, dan iOS WKWebView.'],
            ]
            : [
                ['title' => 'Login & Admin Panel', 'body' => 'Kelola HR, payroll, attendance, ESS, approval, analytics, dan integrasi.'],
                ['title' => 'Kurmigo HRIS Platform', 'body' => 'Pattern visual konsisten untuk admin panel dan modul enterprise HR.'],
            ];
    @endphp
    <title>{{ $loginTitle }} | {{ $brandTitle }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#0A66C2', 600: '#2563EB', 700: '#1D4ED8' },
                        accent: { DEFAULT: '#10B981', 100: '#D1FAE5', 200: '#A7F3D0', 400: '#34D399' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); min-height: 100vh; }
        .brand-panel {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(16,185,129,.20) 0%, transparent 44%),
                radial-gradient(circle at 80% 20%, rgba(10,102,194,.22) 0%, transparent 44%);
        }
        .login-card {
            background: rgba(255,255,255,.96);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.08), 0 20px 44px rgba(15,23,42,.10);
            border-radius: 16px;
            border: 1px solid rgba(226,232,240,.9);
        }
        .input-field {
            transition: all .2s ease;
            border: 1px solid #d1d5db;
            background: #fff;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
            outline: none;
        }
        .submit-btn {
            background: #2563eb;
            transition: all .2s ease;
        }
        .submit-btn:hover { background: #1d4ed8; transform: translateY(-1px); }
        .gradient-text {
            background: linear-gradient(135deg, #0A66C2 0%, #10B981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="flex min-h-screen">
    <section class="brand-panel hidden lg:flex w-[46%] items-center justify-center p-10 text-white">
        <div class="relative z-10 max-w-lg">
            <div class="mb-8 inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur">
                <span class="text-lg font-bold gradient-text">HR</span>
            </div>
            <h1 class="mb-2 text-4xl font-bold">{{ $brandTitle }}</h1>
            <p class="text-xl opacity-90">{{ $brandSubtitle }}</p>
            <div class="mt-8 grid gap-3">
                @foreach($panelCards as $card)
                    <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-lg font-semibold">{{ $card['title'] }}</div>
                        <div class="mt-1 text-sm opacity-80">{{ $card['body'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <main class="flex flex-1 items-center justify-center p-6">
        <div class="login-card w-full max-w-md p-8">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-100 text-accent">
                    <i class="bi bi-shield-lock-fill text-2xl"></i>
                </div>
                <h2 class="mb-2 text-3xl font-bold text-gray-900">{{ $loginTitle }}</h2>
                <p class="text-gray-600">{{ $loginCopy }}</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="input-field w-full rounded-xl py-3 pl-10 pr-4 text-sm text-slate-800"
                               placeholder="{{ $isEssLogin ? 'dimas@kurmigo.id' : 'admin@kurmigo.id' }}">
                    </div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lupa password?</a>
                    </div>
                    <div class="relative">
                        <i class="bi bi-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="password" name="password" type="password" required
                               class="input-field w-full rounded-xl py-3 pl-10 pr-4 text-sm text-slate-800"
                               placeholder="password123">
                    </div>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-primary">
                    Ingat saya
                </label>
                <button type="submit" class="submit-btn flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/10">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk
                </button>
            </form>
        </div>
    </main>
</body>
</html>
