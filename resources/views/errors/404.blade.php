<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolarOps - Page Not Found</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden">
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-16 h-16 bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-5">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-slate-800 text-amber-400 border border-slate-700 inline-block mb-3">
            404 &bull; Page Not Found
        </span>

        <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mb-2">
            Requested Page Not Available
        </h1>

        <p class="text-sm text-slate-300 leading-relaxed mb-6 font-medium">
            The page or report record you are looking for has moved, expired, or does not exist.
        </p>

        <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/80 mb-6 text-xs text-slate-300">
            <div class="flex items-center justify-center gap-2 mb-2 font-bold text-slate-200">
                <svg class="w-4 h-4 text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Redirecting to home in <span id="countdown" class="text-amber-400 font-extrabold text-sm">4</span> seconds...</span>
            </div>
            <div class="w-full bg-slate-900 rounded-full h-1.5 overflow-hidden">
                <div id="progress" class="bg-amber-400 h-1.5 rounded-full transition-all duration-1000 ease-linear" style="width: 100%"></div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5">
            <a href="{{ url('/') }}" 
               class="w-full sm:w-auto px-5 py-3 bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <span>Return to Home</span>
                <span>&rarr;</span>
            </a>
            <button type="button" onclick="window.history.back()" 
                    class="w-full sm:w-auto px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl border border-slate-700 transition-all">
                Go Back
            </button>
        </div>
    </div>

    <script>
        (function() {
            var homeUrl = "{{ url('/') }}";
            var seconds = 4;
            var countdownEl = document.getElementById('countdown');
            var progressEl = document.getElementById('progress');

            var timer = setInterval(function() {
                seconds--;
                if (countdownEl) countdownEl.textContent = seconds;
                if (progressEl) progressEl.style.width = (seconds / 4 * 100) + '%';
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = homeUrl;
                }
            }, 1000);
        })();
    </script>
</body>
</html>
