@php
    $data = $this->getViewData();
    $user = $data['user'];
    $tenant = $data['tenant'];
@endphp

<x-filament-widgets::widget>
    <div class="fi-account-widget w-full max-w-7xl mx-auto font-sans" style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- ══════════════════════════════════════════════════════════
             TOP HEADER: Greeting + Date Pill + Action Buttons
        ══════════════════════════════════════════════════════════ --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:0.75rem;">
                    <h1 style="font-size:1.6rem;font-weight:800;color:#ffffff;letter-spacing:-0.02em;line-height:1;text-transform:uppercase;font-family:'Barlow Condensed',sans-serif;">
                        {{ $data['heading'] }} <span style="color:#22c55e;">{{ $user?->name ?? 'Admin' }}</span>
                    </h1>
                    <span style="padding:0.3rem 0.85rem;border-radius:9999px;font-size:0.7rem;font-weight:600;background:#1e2634;color:#94a3b8;white-space:nowrap;">
                        {{ now()->format('l') }} &bull; {{ now()->format('M d, Y') }}
                    </span>
                </div>
                <p style="font-size:0.8rem;color:#64748b;margin-top:0.35rem;font-weight:400;">
                    {{ $data['subheading'] }}
                </p>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
                @if ($tenant)
                    <a href="{{ route('filament.admin.resources.check-ins.index', ['tenant' => $tenant->slug]) }}"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1rem;border-radius:9999px;font-size:0.7rem;font-weight:600;background:#1e2634;color:#94a3b8;text-decoration:none;border:1px solid rgba(255,255,255,0.07);">
                        <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Export
                    </a>
                @endif

                @if (! $data['isPlatformAdmin'] && $tenant)
                    <a href="{{ route('filament.admin.pages.scan', ['tenant' => $tenant->slug]) }}"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1rem;border-radius:9999px;font-size:0.7rem;font-weight:600;background:#1e2634;color:#94a3b8;text-decoration:none;border:1px solid rgba(255,255,255,0.07);">
                        <svg style="width:0.85rem;height:0.85rem;color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Scanner
                    </a>

                    <a href="{{ route('filament.admin.resources.members.create', ['tenant' => $tenant->slug]) }}"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1.1rem;border-radius:9999px;font-size:0.7rem;font-weight:800;background:#22c55e;color:#052e16;text-decoration:none;text-transform:uppercase;letter-spacing:0.06em;box-shadow:0 4px 14px rgba(34,197,94,0.3);">
                        <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Member
                    </a>
                @elseif ($tenant)
                    <a href="{{ route('filament.admin.resources.gyms.index', ['tenant' => $tenant->slug]) }}"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1.1rem;border-radius:9999px;font-size:0.7rem;font-weight:800;background:#22c55e;color:#052e16;text-decoration:none;text-transform:uppercase;letter-spacing:0.06em;box-shadow:0 4px 14px rgba(34,197,94,0.3);">
                        <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Review Gyms
                    </a>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             MAIN DASHBOARD GRID
        ══════════════════════════════════════════════════════════ --}}
        <div class="fl-dash-grid">

            {{-- ══════════════════════
                 LEFT COLUMN
            ══════════════════════ --}}
            <div style="display:flex;flex-direction:column;gap:1.1rem;min-width:0;">

                {{-- ── Revenue / Bar Chart Card ── --}}
                <div class="fl-card" style="padding:1.5rem;">
                    {{-- Header Row --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;">
                        <div>
                            <span style="font-size:0.68rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;display:block;margin-bottom:0.5rem;">{{ $data['mainMetricLabel'] }}</span>
                            <div style="display:flex;align-items:baseline;gap:0.75rem;flex-wrap:wrap;">
                                <span style="font-size:2rem;font-weight:800;color:#ffffff;letter-spacing:-0.03em;line-height:1;font-family:'Barlow Condensed',sans-serif;">
                                    {{ $data['mainMetricValue'] }}
                                </span>
                                <span style="display:inline-flex;align-items:center;padding:0.2rem 0.65rem;border-radius:9999px;font-size:0.68rem;font-weight:700;background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.25);">
                                    {{ $data['mainMetricBadge'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Duration Selector --}}
                        <div style="position:relative;flex-shrink:0;">
                            <select wire:model.live="duration"
                                style="appearance:none;padding:0.4rem 2rem 0.4rem 0.85rem;border-radius:9999px;font-size:0.68rem;font-weight:600;background:#1e2634;color:#94a3b8;border:1px solid rgba(255,255,255,0.07);cursor:pointer;outline:none;">
                                <option value="3m"  style="background:#161c26;color:#fff;">3 Months</option>
                                <option value="6m"  style="background:#161c26;color:#fff;">6 Months</option>
                                <option value="12m" style="background:#161c26;color:#fff;">12 Months</option>
                            </select>
                            <svg style="width:0.75rem;height:0.75rem;color:#64748b;position:absolute;right:0.6rem;top:50%;transform:translateY(-50%);pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Bar Chart Canvas --}}
                    <div style="position:relative;">
                        {{-- Y-axis baseline --}}
                        <div style="position:absolute;bottom:2rem;left:0;right:0;height:1px;background:rgba(255,255,255,0.05);z-index:0;"></div>

                        {{-- Bars Row --}}
                        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:0.4rem;height:120px;position:relative;z-index:1;">
                            @foreach ($data['chartMonths'] as $bar)
                                @php $pct = max(18, min(100, (int) ($bar['percentage'] ?? 45))); @endphp
                                <div class="fl-bar-col" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;position:relative;">
                                    {{-- Tooltip --}}
                                    <div class="fl-bar-tip" style="position:absolute;top:-1.6rem;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:700;color:#22c55e;white-space:nowrap;opacity:0;pointer-events:none;font-family:'JetBrains Mono',monospace;">
                                        &#8369;{{ number_format($bar['value'], 0) }}
                                    </div>
                                    {{-- Bar --}}
                                    <div class="fl-bar" style="width:100%;max-width:38px;height:{{ $pct }}%;min-height:16px;border-radius:8px 8px 4px 4px;overflow:hidden;position:relative;">
                                        <div style="position:absolute;top:0;left:0;right:0;height:36%;background:#22c55e;"></div>
                                        <div style="position:absolute;top:36%;left:0;right:0;bottom:0;background:linear-gradient(180deg,rgba(34,197,94,0.4) 0%,rgba(34,197,94,0.1) 100%);"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Month Labels --}}
                        <div style="display:flex;justify-content:space-between;gap:0.4rem;margin-top:0.6rem;padding:0 2px;">
                            @foreach ($data['chartMonths'] as $bar)
                                <div style="flex:1;text-align:center;font-size:0.65rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.04em;">
                                    {{ $bar['label'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── Bottom Two Metric Cards ── --}}
                <div class="fl-subcards-grid">

                    {{-- Active Members --}}
                    <div class="fl-card" style="padding:1.35rem;display:flex;flex-direction:column;justify-content:space-between;gap:1rem;">
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                                <span style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:#64748b;">{{ $data['stat1Label'] }}</span>
                                @if ($tenant)
                                    <a href="{{ route('filament.admin.resources.members.index', ['tenant' => $tenant->slug]) }}"
                                        style="display:inline-flex;width:1.75rem;height:1.75rem;align-items:center;justify-content:center;border-radius:9999px;color:#475569;background:rgba(255,255,255,0.04);text-decoration:none;"
                                        aria-label="View members">
                                        <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                    </a>
                                @endif
                            </div>

                            <div style="display:flex;align-items:baseline;gap:0.6rem;flex-wrap:wrap;">
                                <span style="font-size:2.5rem;font-weight:800;color:#ffffff;letter-spacing:-0.04em;line-height:1;font-family:'Barlow Condensed',sans-serif;">
                                    {{ $data['stat1Value'] }}
                                </span>
                                <span style="display:inline-flex;align-items:center;padding:0.18rem 0.55rem;border-radius:9999px;font-size:0.65rem;font-weight:700;background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.2);">
                                    +12%
                                </span>
                            </div>

                            <p style="font-size:0.7rem;color:#475569;margin-top:0.5rem;font-weight:500;">
                                Target: {{ str_replace(['of ', ' members', ' of '], [' / ', '', ' / '], $data['stat1TargetLabel'] ?? '50 members') }}
                            </p>
                        </div>

                        {{-- Progress Bar --}}
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                                <span style="font-size:0.63rem;font-weight:600;color:#475569;">Progress to target</span>
                                <span style="font-size:0.63rem;font-weight:700;color:#22c55e;">{{ $data['stat1Target'] }}%</span>
                            </div>
                            <div style="width:100%;height:0.5rem;background:#0c0f17;border-radius:9999px;overflow:hidden;" role="progressbar" aria-valuenow="{{ $data['stat1Target'] }}" aria-valuemin="0" aria-valuemax="100">
                                <div style="height:100%;width:{{ $data['stat1Target'] }}%;background:linear-gradient(90deg,#15803d,#22c55e);border-radius:9999px;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Check-Ins Today --}}
                    @php
                        $ciPct = (int) min(100, max(0, round(((int)$data['stat2Value'] / max(1, (int)$data['stat1Value'])) * 100)));
                    @endphp
                    <div class="fl-card" style="padding:1.35rem;display:flex;flex-direction:column;justify-content:space-between;gap:1rem;">
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                                <span style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:#64748b;">{{ $data['stat2Label'] }}</span>
                                @if ($tenant)
                                    <a href="{{ route('filament.admin.resources.check-ins.index', ['tenant' => $tenant->slug]) }}"
                                        style="display:inline-flex;width:1.75rem;height:1.75rem;align-items:center;justify-content:center;border-radius:9999px;color:#475569;background:rgba(255,255,255,0.04);text-decoration:none;"
                                        aria-label="View today's check-ins">
                                        <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                    </a>
                                @elseif ($data['isPlatformAdmin'])
                                    <span style="padding:0.18rem 0.55rem;border-radius:9999px;font-size:0.65rem;font-weight:700;background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.2);">
                                        {{ $data['stat2Badge'] }}
                                    </span>
                                @endif
                            </div>

                            <div style="display:flex;align-items:baseline;gap:0.6rem;flex-wrap:wrap;">
                                <span style="font-size:2.5rem;font-weight:800;color:#ffffff;letter-spacing:-0.04em;line-height:1;font-family:'Barlow Condensed',sans-serif;">
                                    {{ $data['stat2Value'] }}
                                </span>
                                <span style="display:inline-flex;align-items:center;padding:0.18rem 0.55rem;border-radius:9999px;font-size:0.65rem;font-weight:700;background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.2);">
                                    +7%
                                </span>
                            </div>

                            @if ($tenant)
                                <p style="display:flex;align-items:center;gap:0.4rem;font-size:0.7rem;color:#475569;margin-top:0.5rem;font-weight:500;">
                                    <span style="display:inline-block;width:0.45rem;height:0.45rem;border-radius:9999px;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,0.15);flex-shrink:0;"></span>
                                    {{ $data['stat2Sub'] }}
                                </p>
                            @else
                                <p style="font-size:0.7rem;color:#475569;margin-top:0.5rem;font-weight:500;">{{ $data['stat2Sub'] }}</p>
                            @endif
                        </div>

                        {{-- Check-in Rate Bar --}}
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                                <span style="font-size:0.63rem;font-weight:600;color:#475569;">Daily rate</span>
                                <span style="font-size:0.63rem;font-weight:700;color:#22c55e;">{{ $ciPct }}%</span>
                            </div>
                            <div style="width:100%;height:0.5rem;background:#0c0f17;border-radius:9999px;overflow:hidden;" role="progressbar" aria-valuenow="{{ $ciPct }}" aria-valuemin="0" aria-valuemax="100">
                                <div style="height:100%;width:{{ $ciPct }}%;background:linear-gradient(90deg,#15803d,#22c55e);border-radius:9999px;"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ══════════════════════
                 RIGHT COLUMN
            ══════════════════════ --}}
            <div style="display:flex;flex-direction:column;gap:1.1rem;min-width:0;">

                {{-- ── Top Membership Plans ── --}}
                <div class="fl-card" style="padding:1.35rem;">
                    <h3 style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:#64748b;margin-bottom:1rem;">
                        {{ $data['isPlatformAdmin'] ? 'Platform Subscription Tiers' : 'Top Membership Plans' }}
                    </h3>

                    <div class="fl-plans-grid">
                        @foreach ($data['topItems'] as $idx => $item)
                            <div class="fl-inner-card" style="padding:1rem;display:flex;flex-direction:column;gap:0.6rem;">
                                <div style="width:2.25rem;height:2.25rem;border-radius:9999px;display:flex;align-items:center;justify-content:center;{{ $idx === 0 ? 'background:rgba(34,197,94,0.15);color:#22c55e;' : 'background:rgba(255,122,0,0.15);color:#FF7A00;' }}">
                                    @if ($idx === 0)
                                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    @else
                                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>

                                <div>
                                    <span style="font-size:0.65rem;font-weight:600;color:#64748b;display:block;">{{ $item['name'] }}</span>
                                    <span style="font-size:1.1rem;font-weight:800;color:#ffffff;display:block;letter-spacing:-0.02em;font-family:'Barlow Condensed',sans-serif;line-height:1.2;margin-top:0.15rem;">{{ $item['value'] }}</span>
                                </div>

                                <div style="padding-top:0.5rem;border-top:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:space-between;">
                                    <span style="font-size:0.65rem;color:#475569;font-weight:500;">{{ $item['desc'] }}</span>
                                    <svg style="width:0.75rem;height:0.75rem;color:#334155;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── Collections / Radial Ring ── --}}
                <div class="fl-card" style="padding:1.35rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;overflow:hidden;">
                    <div style="flex:1;min-width:0;">
                        <span style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:#64748b;display:block;margin-bottom:0.5rem;">{{ $data['ringLabel'] }}</span>
                        <span style="font-size:1.1rem;font-weight:800;color:#ffffff;display:block;letter-spacing:-0.02em;font-family:'Barlow Condensed',sans-serif;line-height:1.25;word-break:break-word;">
                            {{ $data['ringValue'] }}
                        </span>
                        <span style="font-size:0.68rem;color:#475569;font-weight:500;display:block;margin-top:0.35rem;">
                            {{ $data['ringSubtitle'] }}
                        </span>
                    </div>

                    {{-- Radial Ring --}}
                    <div style="position:relative;width:5rem;height:5rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:100%;height:100%;transform:rotate(-90deg);" viewBox="0 0 36 36">
                            <path stroke-width="3" stroke="rgba(255,255,255,0.08)" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path stroke-linecap="round" stroke-width="3" stroke="#22c55e" fill="none"
                                stroke-dasharray="{{ max(5, $data['ringPercentage']) }}, 100"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span style="position:absolute;font-size:0.6rem;font-weight:800;color:#ffffff;font-family:'Barlow Condensed',sans-serif;line-height:1;text-align:center;">
                            {{ $data['ringPercentage'] }}%
                        </span>
                    </div>
                </div>

                {{-- ── Feature Banner ── --}}
                <div class="fl-card" style="padding:1.35rem;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;min-height:155px;background:linear-gradient(145deg,#182622 0%,#161c26 60%,#131720 100%) !important;">
                    <div style="position:absolute;right:-2rem;top:-2rem;width:9rem;height:9rem;border-radius:9999px;background:rgba(34,197,94,0.12);filter:blur(40px);pointer-events:none;"></div>
                    <div style="position:absolute;left:-2rem;bottom:-2rem;width:7rem;height:7rem;border-radius:9999px;background:rgba(255,122,0,0.08);filter:blur(36px);pointer-events:none;"></div>

                    <div style="position:relative;z-index:1;">
                        <h4 style="font-size:1.3rem;font-weight:800;color:#ffffff;letter-spacing:-0.01em;line-height:1.2;text-transform:uppercase;font-family:'Barlow Condensed',sans-serif;max-width:16rem;">
                            {{ $data['bannerTitle'] }}
                        </h4>
                        <p style="font-size:0.7rem;color:#94a3b8;margin-top:0.45rem;line-height:1.5;max-width:17rem;font-weight:400;">
                            {{ $data['bannerSubtitle'] }}
                        </p>
                    </div>

                    <div style="margin-top:1rem;position:relative;z-index:1;">
                        <a href="{{ $data['bannerButtonUrl'] }}"
                            style="display:inline-flex;align-items:center;padding:0.5rem 1.1rem;border-radius:9999px;font-size:0.7rem;font-weight:700;background:#f8fafc;color:#0c0f17;text-decoration:none;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
                            {{ $data['bannerButtonText'] }}
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <style>
        .fl-dash-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.1rem;
            width: 100%;
            align-items: start;
        }
        @media (min-width: 1200px) {
            .fl-dash-grid { grid-template-columns: minmax(0, 1.7fr) minmax(17rem, 1fr); }
        }
        .fl-subcards-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.1rem;
        }
        @media (min-width: 580px) {
            .fl-subcards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .fl-plans-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 480px) {
            .fl-plans-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .fl-card {
            background: #161c26 !important;
            border: none !important;
            border-radius: 1.1rem !important;
            box-shadow: 0 8px 24px -4px rgba(0,0,0,0.5), 0 3px 8px -2px rgba(0,0,0,0.3) !important;
            position: relative;
            min-width: 0;
        }
        .fl-inner-card {
            background: #1c2331 !important;
            border: none !important;
            border-radius: 0.85rem !important;
            transition: background 0.2s, transform 0.2s;
        }
        .fl-inner-card:hover {
            background: #232c3d !important;
            transform: translateY(-1px);
        }
        .fl-bar-col:hover .fl-bar-tip { opacity: 1 !important; }
        .fl-bar-col:hover .fl-bar { transform: scaleY(1.05); transform-origin: bottom; }
        .fl-bar { transition: transform 0.2s ease; }
    </style>
</x-filament-widgets::widget>
