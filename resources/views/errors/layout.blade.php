<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &bull; {{ config('app.name', 'FitLedger') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,800;0,900;1,700&family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0c0f17;
            color: #f1f5f9;
        }
        .font-athletic {
            font-family: 'Barlow Condensed', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased selection:bg-[#FF7A00] selection:text-white">
    <div class="w-full max-w-lg bg-[#161c26] border border-white/10 rounded-2xl p-8 sm:p-10 shadow-2xl relative overflow-hidden text-center">
        <!-- Ambient decorative glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 w-48 h-48 rounded-full bg-[#FF7A00]/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-12 -bottom-12 w-48 h-48 rounded-full bg-[#22c55e]/10 blur-3xl"></div>

        <div class="relative z-10">
            <!-- Brand Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.06] border border-white/10 text-xs text-slate-300 font-mono mb-6">
                <span class="w-2 h-2 rounded-full bg-[#FF7A00]"></span>
                <span>{{ config('app.name', 'FitLedger') }} Security</span>
            </div>

            <!-- Error Code Hero -->
            <h1 class="text-7xl sm:text-8xl font-black font-athletic tracking-tight text-white mb-2 leading-none">
                @yield('code')
            </h1>

            <!-- Generic Safe Title -->
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-3">
                @yield('title')
            </h2>

            <!-- Generic Safe Message (No Information Leakage) -->
            <p class="text-sm text-slate-400 mb-8 max-w-md mx-auto leading-relaxed">
                @yield('message')
            </p>

            <!-- Navigation Actions -->
            <div class="flex items-center justify-center gap-3 flex-wrap">
                <a href="{{ url('/') }}" class="px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider text-black bg-[#22c55e] hover:bg-emerald-400 transition shadow-md">
                    Return Home
                </a>
                <a href="{{ url('/admin') }}" class="px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider text-slate-200 bg-[#1e2634] hover:bg-[#283344] transition border border-white/10">
                    Admin Portal
                </a>
            </div>
        </div>
    </div>
</body>
</html>
