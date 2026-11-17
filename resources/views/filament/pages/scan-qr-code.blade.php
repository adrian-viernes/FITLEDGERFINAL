<x-filament-panels::page>
    <div class="space-y-6">

        <!-- Result Banner Alert -->
        @if ($lastResult)
            <div class="rounded-2xl p-6 shadow-xl border transition-all animate-in fade-in zoom-in-95 duration-200"
                style="
                    @if($lastResult['color'] === 'success') background-color: #052e16; border-color: #15803d; color: #bbf7d0;
                    @elseif($lastResult['color'] === 'warning') background-color: #451a03; border-color: #b45309; color: #fde68a;
                    @else background-color: #450a0a; border-color: #b91c1c; color: #fecaca;
                    @endif
                ">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded-xl flex-shrink-0"
                            style="background-color: {{ $lastResult['color_hex'] }}30; color: {{ $lastResult['color_hex'] }};">
                            @if ($lastResult['color'] === 'success')
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif ($lastResult['color'] === 'warning')
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @else
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black tracking-wide uppercase"
                                    style="background-color: {{ $lastResult['color_hex'] }}; color: #ffffff;">
                                    {{ $lastResult['title'] }}
                                </span>
                                <span class="text-xs opacity-75">{{ $lastResult['timestamp'] }}</span>
                            </div>

                            @if (!empty($lastResult['member']))
                                <h2 class="text-2xl font-black text-white mt-1">{{ $lastResult['member']['name'] }}</h2>
                                <p class="text-xs opacity-80 font-mono">{{ $lastResult['member']['phone'] }} &bull; {{ $lastResult['member']['qr_code'] }}</p>
                            @endif

                            <p class="text-sm font-semibold mt-2">{{ $lastResult['message'] }}</p>

                            @if (!empty($lastResult['subscription']))
                                <div class="mt-3 flex flex-wrap gap-3 text-xs">
                                    <span class="px-2.5 py-1 rounded-lg bg-black/30 border border-white/10">
                                        Plan: <strong>{{ $lastResult['subscription']['plan'] }}</strong>
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-black/30 border border-white/10">
                                        Valid Until: <strong>{{ $lastResult['subscription']['ends_at'] }}</strong>
                                    </span>
                                    @if ($lastResult['subscription']['amount_due'] > 0)
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-300 font-bold border border-rose-500/40">
                                            Amount Due: PHP {{ number_format($lastResult['subscription']['amount_due'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex sm:flex-col gap-2 flex-shrink-0">
                        @if (!empty($lastResult['member']))
                            <a href="{{ $lastResult['member']['pass_url'] }}" target="_blank"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition text-center border border-white/20">
                                Open Digital Pass ↗
                            </a>
                        @endif
                        <button type="button" wire:click="clearResult" onclick="resumeScanning()"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-gray-900 hover:bg-gray-100 transition shadow flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Scan Next Member</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Scanner & Input Section (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Camera Scanner Panel -->
                <div class="rounded-2xl p-6 shadow-xl" style="background-color: #12151b; border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 1.25rem;">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="font-athletic text-xl tracking-wider text-white">Live Camera Scanner</span>
                            </h3>
                            <p class="text-xs text-zinc-400 mt-0.5">
                                Point a member's QR code or digital mobile pass towards your webcam.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" id="start-scan-btn"
                                onclick="startCamera()"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-white transition flex items-center gap-1.5 shadow-md"
                                style="background: linear-gradient(135deg, #FF6D00 0%, #FF3D00 100%);">
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                <span>Start Camera</span>
                            </button>
                            <button type="button" id="stop-scan-btn"
                                onclick="stopCamera()"
                                style="display: none; background-color: #27272a;"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-zinc-300 hover:text-white transition">
                                Stop Camera
                            </button>
                        </div>
                    </div>

                    <!-- Viewfinder container -->
                    <div class="relative rounded-xl overflow-hidden min-h-[300px] flex items-center justify-center"
                        style="background-color: #0a0c10; border: 1px solid rgba(255, 255, 255, 0.08);">
                        <div id="reader" class="w-full"></div>

                        <!-- Scan Complete / Paused Overlay (Stops spam / continuous loop) -->
                        <div id="camera-paused-overlay" style="display: none;" class="absolute inset-0 bg-black/85 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center z-10 animate-in fade-in zoom-in-95 duration-200">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center mb-3 shadow-xl shadow-emerald-950/50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h4 class="text-white font-black text-lg tracking-wide">Check-In Scanned</h4>
                            <p class="text-zinc-400 text-xs mt-1 mb-5 max-w-xs">
                                Camera paused to prevent duplicate scans. Ready for the next member?
                            </p>
                            <button type="button" onclick="resumeScanning()"
                                class="px-6 py-3 rounded-xl font-bold text-xs text-white shadow-xl transition flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98]"
                                style="background: linear-gradient(135deg, #FF6D00 0%, #FF3D00 100%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Scan Next Member (Spacebar)</span>
                            </button>
                        </div>

                        <!-- Permission / Error Notice -->
                        <div id="camera-error-notice" style="display: none;" class="p-6 text-center max-w-md space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center text-rose-400 bg-rose-500/10 border border-rose-500/30">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                            <div class="text-sm font-bold text-rose-300" id="camera-error-title">Camera Permission Blocked</div>
                            <p class="text-xs text-zinc-400" id="camera-error-desc">
                                Your browser or system blocked camera access. Click the site settings icon in your browser's address bar, set Camera to <strong>Allow</strong>, and refresh the page.
                            </p>
                            <button type="button" onclick="startCamera()" class="px-4 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition border border-white/20">
                                Try Again
                            </button>
                        </div>

                        <div id="camera-idle-placeholder" class="text-center p-8 space-y-3">
                            <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-zinc-400"
                                style="background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div class="text-sm font-semibold text-zinc-300">Camera is currently paused</div>
                            <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                                Click "Start Camera" above to enable live continuous scanning, or scan using a handheld USB barcode scanner below.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Handheld Gun / Manual Code Input -->
                <div class="rounded-2xl p-6 shadow-xl" style="background-color: #12151b; border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 1.25rem;">
                    <h3 class="text-base font-bold text-white flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span class="font-athletic text-xl tracking-wider text-white">Handheld Barcode Gun or Manual Code</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mb-4">
                        USB barcode scanners automatically press Enter upon scanning. You can also paste a pass URL or type a member code directly.
                    </p>

                    <form wire:submit="processScan" class="flex gap-3">
                        <div class="relative flex-1">
                            <input 
                                type="text" 
                                wire:model="manualCode"
                                id="manual-code-input"
                                placeholder="Scan with USB gun, paste pass URL, or type code..." 
                                autofocus
                                class="w-full rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none transition font-mono"
                                style="background-color: #0a0c10; border: 1px solid rgba(255, 255, 255, 0.12);"
                            >
                        </div>

                        <button 
                            type="submit"
                            class="px-6 py-3 rounded-xl font-bold text-sm text-white shadow-lg transition flex items-center gap-2 flex-shrink-0"
                            style="background: linear-gradient(135deg, #FF6D00 0%, #FF3D00 100%);">
                            <span>Check In</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Recent Check-Ins Today Feed (1 Column) -->
            <div class="space-y-4">
                <div class="rounded-2xl p-6 shadow-xl" style="background-color: #12151b; border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 1.25rem;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-athletic text-xl tracking-wider text-white">Live Activity Feed</span>
                        </h3>
                        <a href="{{ url('/admin/' . Filament\Facades\Filament::getTenant()?->slug . '/check-ins') }}" 
                           class="text-xs text-orange-400 hover:text-orange-300 font-semibold">
                            Full Log &rarr;
                        </a>
                    </div>

                    @php $recents = $this->recentCheckIns; @endphp

                    @if ($recents->isEmpty())
                        <div class="text-center py-8 text-xs text-zinc-500">
                            No check-in activity recorded yet for today.
                        </div>
                    @else
                        <div class="divide-y divide-zinc-800">
                            @foreach ($recents as $item)
                                <div class="py-3 flex items-start justify-between gap-3 text-xs">
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $item->status->colorHex() }};"></span>
                                            <span>{{ $item->member?->name ?? 'Unknown Member' }}</span>
                                        </div>
                                        <div class="text-[11px] text-zinc-400 mt-0.5">
                                            {{ $item->subscription?->plan?->name ?? 'No plan' }} &bull; {{ $item->scanned_at->format('g:i:s A') }}
                                        </div>
                                    </div>

                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                        style="background-color: {{ $item->status->colorHex() }}20; color: {{ $item->status->colorHex() }}; border: 1px solid {{ $item->status->colorHex() }}40;">
                                        {{ $item->status->getLabel() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Front-Desk Quick Tips (Matches dark athletic aesthetic) -->
                <div class="rounded-2xl p-5 text-xs text-zinc-400 space-y-2.5 shadow-lg"
                    style="background-color: #12151b; border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 1rem;">
                    <div class="font-athletic text-sm uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        Front Desk Guide
                    </div>
                    <ul class="space-y-1.5 text-[11px] text-zinc-400">
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            <span><strong class="text-emerald-400">Green (Active)</strong>: Allowed entry immediately.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                            <span><strong class="text-amber-300">Yellow (Due)</strong>: Expiring soon or collect pending payment.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                            <span><strong class="text-rose-400">Red (Expired)</strong>: Deny entry until pass is renewed.</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

    <!-- Html5Qrcode Library Script -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        let html5QrCode = null;
        let isProcessing = false;
        let isPaused = false;
        let lastScannedCode = null;
        let lastScannedAt = 0;

        function playBeep(success = true) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.value = success ? 880 : 300; // 880Hz high beep or 300Hz low boop
                gain.gain.value = 0.15;
                osc.start();
                setTimeout(() => {
                    osc.stop();
                    ctx.close();
                }, success ? 140 : 250);
            } catch (e) {
                // AudioContext not allowed or not supported
            }
        }

        function resumeScanning() {
            isProcessing = false;
            isPaused = false;
            lastScannedCode = null;

            const pausedOverlay = document.getElementById('camera-paused-overlay');
            if (pausedOverlay) pausedOverlay.style.display = 'none';

            if (html5QrCode) {
                try {
                    if (typeof html5QrCode.resume === 'function') {
                        html5QrCode.resume();
                    }
                } catch (e) {
                    console.warn("Camera resume error:", e);
                }
            }
        }

        // Spacebar shortcut: press Space anywhere (except when typing in an input) to resume scanning
        window.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && document.activeElement.tagName !== 'INPUT' && isPaused) {
                e.preventDefault();
                resumeScanning();
            }
        });

        async function startCamera() {
            const placeholder = document.getElementById('camera-idle-placeholder');
            const errorNotice = document.getElementById('camera-error-notice');
            const pausedOverlay = document.getElementById('camera-paused-overlay');
            const startBtn = document.getElementById('start-scan-btn');
            const stopBtn = document.getElementById('stop-scan-btn');

            isProcessing = false;
            isPaused = false;
            lastScannedCode = null;

            if (placeholder) placeholder.style.display = 'none';
            if (errorNotice) errorNotice.style.display = 'none';
            if (pausedOverlay) pausedOverlay.style.display = 'none';
            if (startBtn) startBtn.style.display = 'none';
            if (stopBtn) stopBtn.style.display = 'inline-flex';

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            const qrConfig = {
                fps: 10,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const qrboxSize = Math.floor(minEdge * 0.7);
                    return { width: Math.max(180, qrboxSize), height: Math.max(180, qrboxSize) };
                },
                aspectRatio: 1.0,
            };

            const onScanSuccess = (decodedText) => {
                const now = Date.now();

                // Prevent repeat scanning if already paused or same code within 6 seconds
                if (isPaused || isProcessing) return;
                if (decodedText === lastScannedCode && (now - lastScannedAt) < 6000) return;

                isProcessing = true;
                isPaused = true;
                lastScannedCode = decodedText;
                lastScannedAt = now;

                playBeep(true);

                // Pause camera feed immediately to stop continuous spam
                try {
                    if (html5QrCode && typeof html5QrCode.pause === 'function') {
                        html5QrCode.pause(true);
                    }
                } catch (e) {
                    console.warn("Pause camera error:", e);
                }

                // Show the paused overlay
                const pausedOverlay = document.getElementById('camera-paused-overlay');
                if (pausedOverlay) pausedOverlay.style.display = 'flex';

                // Call Livewire component method
                @this.call('processScan', decodedText).catch(() => {
                    isProcessing = false;
                });
            };

            const onScanError = (errorMessage) => {
                // Ignore per-frame non-detections
            };

            // Attempt 1: Start with environment (rear camera on mobile phones)
            try {
                await html5QrCode.start({ facingMode: "environment" }, qrConfig, onScanSuccess, onScanError);
                return;
            } catch (errEnv) {
                console.warn("Environment facingMode camera start failed, trying fallback:", errEnv);
            }

            // Attempt 2: Fallback to user-facing camera (desktop/laptop webcams, front camera)
            try {
                await html5QrCode.start({ facingMode: "user" }, qrConfig, onScanSuccess, onScanError);
                return;
            } catch (errUser) {
                console.warn("User facingMode camera start failed, trying device list:", errUser);
            }

            // Attempt 3: Query device enumeration
            try {
                const cameras = await Html5Qrcode.getCameras();
                if (cameras && cameras.length > 0) {
                    await html5QrCode.start(cameras[0].id, qrConfig, onScanSuccess, onScanError);
                    return;
                }
                throw new Error("No camera hardware detected on device.");
            } catch (finalErr) {
                console.error("Camera start failed completely:", finalErr);
                const errStr = (finalErr || '').toString();
                if (stopBtn) stopBtn.style.display = 'none';
                if (startBtn) startBtn.style.display = 'inline-flex';
                if (placeholder) placeholder.style.display = 'none';

                if (errorNotice) {
                    errorNotice.style.display = 'block';
                    const desc = document.getElementById('camera-error-desc');
                    if (errStr.includes('NotAllowedError') || errStr.includes('Permission denied')) {
                        desc.innerHTML = 'Camera access was blocked. Tap the <strong>lock/site settings icon</strong> in your browser\'s address bar, set Camera to <strong>Allow</strong>, and refresh the page.';
                    } else if (errStr.includes('NotFoundError') || errStr.includes('DevicesNotFoundError')) {
                        desc.innerHTML = 'No camera was found on your device. You can still scan using a handheld USB barcode scanner or type member codes manually below.';
                    } else {
                        desc.innerHTML = 'Unable to start camera: ' + (finalErr.message || errStr);
                    }
                }
            }
        }

        function stopCamera() {
            isProcessing = false;
            isPaused = false;
            lastScannedCode = null;

            const placeholder = document.getElementById('camera-idle-placeholder');
            const errorNotice = document.getElementById('camera-error-notice');
            const pausedOverlay = document.getElementById('camera-paused-overlay');
            const startBtn = document.getElementById('start-scan-btn');
            const stopBtn = document.getElementById('stop-scan-btn');

            if (errorNotice) errorNotice.style.display = 'none';
            if (pausedOverlay) pausedOverlay.style.display = 'none';

            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    if (placeholder) placeholder.style.display = 'block';
                    if (startBtn) startBtn.style.display = 'inline-flex';
                    if (stopBtn) stopBtn.style.display = 'none';
                }).catch(() => {
                    if (placeholder) placeholder.style.display = 'block';
                    if (startBtn) startBtn.style.display = 'inline-flex';
                    if (stopBtn) stopBtn.style.display = 'none';
                });
            } else {
                if (placeholder) placeholder.style.display = 'block';
                if (startBtn) startBtn.style.display = 'inline-flex';
                if (stopBtn) stopBtn.style.display = 'none';
            }
        }
    </script>
</x-filament-panels::page>
