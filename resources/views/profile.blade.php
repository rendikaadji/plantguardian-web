@extends('layouts.app')

@section('title', 'Profil — Garden Guardian')

@section('content')
<div class="space-y-6 max-w-md mx-auto py-2">

    @if(auth()->check() && auth()->user()->role === 'ranger')
        <!-- RANGER DEDICATED PROFILE CARD -->
        <div class="flex flex-col items-center text-center space-y-4 pt-2">
            <div class="relative">
                <div class="w-32 h-32 rounded-full border-4 border-[#8B6A4C] p-1 bg-[#FBFAF0] shadow-md overflow-hidden">
                    <img src="{{ asset('images/guardian_avatar.png') }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                </div>
            </div>

            <div class="space-y-1">
                <span class="font-mono-code text-xs font-bold px-3 py-1 rounded-xs uppercase tracking-widest border border-[#8B6A4C]" style="font-family: 'IBM Plex Mono', monospace; background-color: #8B6A4C !important; color: #EDE6D3 !important;">
                    [ RANGER - DATA STEWARD ]
                </span>
                <h1 class="font-serif-headline font-extrabold text-2xl text-[#1F3D20] pt-2" style="font-family: 'Fraunces', Georgia, serif;">
                    {{ auth()->user()->name }}
                </h1>
                <p class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">
                    ID: {{ sprintf('RNG-%04d', auth()->id()) }} | {{ auth()->user()->email }}
                </p>
            </div>
        </div>

        <!-- Ranger Responsibilities & Quick Actions -->
        <div class="card-gg p-5 space-y-4 bg-[#EDE6D3] border-2 border-[#5C574C]/30 shadow-xs">
            <h3 class="font-serif-headline text-base font-bold text-[#2F4A3C]" style="font-family: 'Fraunces', Georgia, serif;">
                Peran & Responsibilitas Ranger
            </h3>
            <p class="text-xs text-[#5C574C] leading-relaxed">
                Anda terdaftar sebagai Ranger (Data Steward). Tugas utama Anda adalah mengambil foto tumbuhan nyata di lapangan dan langsung menginput Nama Umum, Nama Ilmiah, Risiko Konservasi, Deskripsi, serta Cara Merawat Pohon pada saat scan.
            </p>

            <div class="pt-2">
                <a href="{{ route('peta') }}" class="w-full py-3 px-4 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-sm hover:bg-[#8B6A4C] transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <span>🗺️ Buka Peta Lapangan & Scan AR Tumbuhan</span>
                </a>
            </div>
        </div>
    @else
        <!-- VIEWER PROFILE -->
        <!-- 1. Profile Avatar & User Header -->
        <div class="flex flex-col items-center text-center space-y-3 pt-2">
            <div class="relative">
                <div class="w-32 h-32 rounded-full border-4 border-[#1F3D20] p-1 bg-[#FBFAF0] shadow-md overflow-hidden">
                    <img src="{{ asset('images/guardian_avatar.png') }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                </div>
                <span class="absolute bottom-1 right-2 bg-[#1F3D20] text-[#F5F4DA] text-xs font-baloo font-extrabold px-2.5 py-0.5 rounded-full border-2 border-[#F5F4DA] shadow-xs">
                    LVL {{ $currentLevel }}
                </span>
            </div>

            <div>
                <h1 class="font-baloo font-extrabold text-2xl text-[#1F3D20] leading-tight">
                    {{ $user->name ?? 'Penjelajah Flora' }}
                </h1>
                <p class="font-baloo font-bold text-xs text-[#6B6B55] flex items-center justify-center gap-1.5 uppercase tracking-wider mt-0.5">
                    <svg class="w-4 h-4 text-[#1F3D20]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                    </svg>
                    <span>{{ $rankName }}</span>
                </p>
            </div>
        </div>

        <!-- 2. Category Selector Tabs (Flora, Fauna, Terra) -->
        <div class="space-y-3">
            <div class="grid grid-cols-3 gap-3 pt-2">
                <button id="tab-btn-flora" class="tab-btn card-gg p-3.5 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:bg-[#FBFAF0] transition-all" data-target="panel-flora">
                    <div class="w-8 h-8 rounded-full bg-[#1F3D20]/10 text-[#1F3D20] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="font-baloo font-bold text-xs">Flora</span>
                </button>

                <button id="tab-btn-fauna" class="tab-btn bg-[#1F3D20] text-[#F5F4DA] p-3.5 rounded-2xl flex flex-col items-center justify-center gap-1.5 shadow-sm cursor-pointer transition-all" data-target="panel-fauna">
                    <div class="w-8 h-8 rounded-full bg-[#FBFAF0]/20 text-[#F5F4DA] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-5l4.5 2.5-4.5 2.5z"/>
                        </svg>
                    </div>
                    <span class="font-baloo font-extrabold text-xs">Fauna</span>
                </button>

                <button id="tab-btn-terra" class="tab-btn card-gg p-3.5 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:bg-[#FBFAF0] transition-all" data-target="panel-terra">
                    <div class="w-8 h-8 rounded-full bg-[#1F3D20]/10 text-[#1F3D20] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V8.5dM12 12.5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </div>
                    <span class="font-baloo font-bold text-xs">Terra</span>
                </button>
            </div>

            <!-- Tab Panels Content -->
            <div id="panel-flora" class="tab-panel hidden card-gg p-4 bg-[#FBFAF0] space-y-2 border border-[#1F3D20]/10">
                <div class="flex items-center justify-between">
                    <span class="font-baloo font-bold text-xs text-[#1F3D20]">🌸 Total Tanaman Ditanam</span>
                    <span class="font-baloo font-extrabold text-sm text-[#1F3D20]">{{ $totalPlantings }} Pot</span>
                </div>
                <div class="flex items-center justify-between border-t border-[#1F3D20]/10 pt-2">
                    <span class="font-baloo font-bold text-xs text-[#1F3D20]">🌾 Panen Berhasil</span>
                    <span class="font-baloo font-extrabold text-sm text-[#27AE60]">{{ $harvestedCount }} Tanaman</span>
                </div>
            </div>

            <div id="panel-fauna" class="tab-panel card-gg p-4 bg-[#FBFAF0] space-y-2 border border-[#1F3D20]/10">
                <div class="flex items-center justify-between">
                    <span class="font-baloo font-bold text-xs text-[#1F3D20]">🐾 Fauna Terlindungi</span>
                    <span class="font-baloo font-extrabold text-sm text-[#1F3D20]">Harmonisasi Alam</span>
                </div>
                <div class="flex items-center justify-between border-t border-[#1F3D20]/10 pt-2">
                    <span class="font-baloo font-bold text-xs text-[#1F3D20]">🦋 Keanekaragaman Ekosistem</span>
                    <span class="font-baloo font-extrabold text-sm text-[#1F3D20]">Level {{ $currentLevel }} Active</span>
                </div>
            </div>

            <div id="panel-terra" class="tab-panel hidden card-gg p-4 bg-[#FBFAF0] space-y-2 border border-[#1F3D20]/10">
                <div class="flex items-center justify-between">
                    <span class="font-baloo font-bold text-xs text-[#1F3D20]">🍂 Pengolahan Sampah Kompos</span>
                    <span class="font-baloo font-extrabold text-sm text-[#8B6A4C]">{{ $totalComposts }} Kali</span>
                </div>
                <div class="flex items-center justify-between border-t border-[#1F3D20]/10 pt-2">
                    <span class="font-baloo font-bold text-xs text-[#1F3D20]">🌱 Kualitas Lahan Kebun</span>
                    <span class="font-baloo font-extrabold text-sm text-[#27AE60]">Gembur & Subur</span>
                </div>
            </div>
        </div>

        <!-- 3. Growth Synergy Progress Card -->
        <div class="card-gg p-5 space-y-4">
            <div class="flex items-center justify-between font-baloo font-extrabold text-sm text-[#1F3D20]">
                <span>Growth Synergy</span>
                <span class="text-xs text-[#1F3D20]">{{ number_format($exp) }} / {{ number_format($currentLevel * $expPerLevel) }} XP</span>
            </div>

            <div class="progress-bar-gg">
                <div class="progress-fill-gg" style="width: {{ $levelProgressPercent }}%;"></div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="bg-[#FBFAF0] p-3.5 rounded-2xl border border-[#D96C63]/20 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#D96C63]/15 text-[#D96C63] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.605 15.12a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block leading-none">HYDRATION</span>
                        <span class="font-baloo font-extrabold text-xl text-[#1F3D20]">{{ $hydrationPercent }}%</span>
                    </div>
                </div>

                <div class="bg-[#FBFAF0] p-3.5 rounded-2xl border border-[#2E6DA4]/20 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#2E6DA4]/15 text-[#2E6DA4] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block leading-none">VITALITY</span>
                        <span class="font-baloo font-extrabold text-xl text-[#1F3D20]">{{ $vitalityPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Green Contribution Badges -->
        <div class="space-y-3">
            <div class="flex items-center justify-between font-baloo font-extrabold text-lg text-[#1F3D20]">
                <span>Green Contribution Badges</span>
                <span class="text-xs text-[#6B6B55] font-bold">Terbuka ({{ count($badges) }})</span>
            </div>

            <div class="flex items-center justify-between gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach($badges as $badge)
                    <div class="badge-item flex flex-col items-center space-y-1.5 shrink-0 w-20 cursor-pointer group" data-title="{{ $badge['name'] }}" data-desc="{{ $badge['desc'] }}">
                        <div class="relative w-14 h-14 rounded-full border-2 border-[#1F3D20] {{ $badge['color'] }} text-[#F5F4DA] flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                            <span class="text-2xl">{{ $badge['icon'] }}</span>
                            <span class="absolute -top-1 -right-1 bg-[#8B6A4C] text-[#F5F4DA] text-[9px] font-baloo font-extrabold px-1.5 py-0.2 rounded-full border border-[#F5F4DA]">
                                x{{ $badge['count'] }}
                            </span>
                        </div>
                        <span class="font-baloo font-bold text-[11px] text-[#1F3D20] text-center leading-tight">{{ $badge['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 5. Alliance Friends Section -->
        <div class="space-y-3 pt-2">
            <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20]">
                Alliance Friends
            </h2>

            <div class="space-y-2.5">
                <!-- Friend 1 -->
                <div class="card-gg p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-sm shrink-0 border border-[#1F3D20]">
                            LM
                        </div>
                        <div>
                            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight">Lyra Meadow</h4>
                            <p class="font-nunito text-[11px] text-[#6B6B55]">LVL 38 • Terra Faction</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20" title="Online"></span>
                </div>

                <!-- Friend 2 -->
                <div class="card-gg p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-[#8B6A4C] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-sm shrink-0 border border-[#8B6A4C]">
                            OB
                        </div>
                        <div>
                            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight">Oaken Bark</h4>
                            <p class="font-nunito text-[11px] text-[#6B6B55]">LVL 51 • Flora Faction</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 rounded-full bg-slate-300" title="Offline"></span>
                </div>

                <!-- Friend 3 -->
                <div class="card-gg p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-[#D96C63] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-sm shrink-0 border border-[#D96C63]">
                            SE
                        </div>
                        <div>
                            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight">Sienna Ember</h4>
                            <p class="font-nunito text-[11px] text-[#6B6B55]">LVL 29 • Fauna Faction</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20" title="Online"></span>
                </div>
            </div>

            <!-- Expand Alliance Button -->
            <button id="expand-alliance-btn" class="w-full py-3.5 rounded-2xl border-2 border-dashed border-[#1F3D20]/30 text-[#1F3D20] font-baloo font-extrabold text-sm flex items-center justify-center gap-2 hover:bg-[#1F3D20]/5 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Expand Alliance (Tambah Aliansi)</span>
            </button>
        </div>

        <!-- 6. Account & Settings Options Card -->
        <div class="card-gg p-5 space-y-3 bg-[#FBFAF0]">
            <h3 class="font-baloo font-bold text-base text-[#1F3D20] flex items-center gap-2">
                <span>⚙️</span> Pengaturan Akun & Peran
            </h3>
            <div class="text-xs text-[#6B6B55] space-y-1 font-nunito border-t border-[#1F3D20]/10 pt-2">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Peran Utama:</strong> <span class="uppercase font-bold text-[#1F3D20]">{{ $user->role }}</span></p>
            </div>
            <div class="pt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#C0392B] hover:bg-[#A93226] text-white font-baloo font-bold text-xs transition-colors cursor-pointer flex items-center justify-center gap-2">
                        <span>🚪 Keluar dari Akun</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Alliance Modal -->
        <div id="alliance-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
            <div class="card-gg max-w-sm w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0]">
                <div class="flex justify-between items-center border-b border-[#1F3D20]/10 pb-3">
                    <h3 class="font-baloo font-extrabold text-lg text-[#1F3D20]">Aliansi Guardian Saya</h3>
                    <button id="close-alliance-modal" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
                </div>

                <div class="space-y-3 text-center">
                    <p class="text-xs text-[#6B6B55]">Bagikan Kode Aliansi Anda ke teman untuk bergabung:</p>
                    <div class="p-3 bg-[#E7E6BE] rounded-2xl font-baloo font-extrabold text-xl text-[#1F3D20] tracking-widest flex items-center justify-between px-4 shadow-inner">
                        <span id="alliance-code-val">{{ $allianceCode }}</span>
                        <button id="copy-code-btn" class="px-2.5 py-1 rounded-xl bg-[#1F3D20] text-[#F5F4DA] text-xs font-bold hover:bg-[#8B6A4C] cursor-pointer">
                            📋 Salin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Tab switching logic (Flora, Fauna, Terra)
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanels = document.querySelectorAll('.tab-panel');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.target;

                // Reset button styles
                tabBtns.forEach(b => {
                    b.className = 'tab-btn card-gg p-3.5 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:bg-[#FBFAF0] transition-all';
                    const iconWrap = b.querySelector('div');
                    const textWrap = b.querySelector('span');
                    if (iconWrap) iconWrap.className = 'w-8 h-8 rounded-full bg-[#1F3D20]/10 text-[#1F3D20] flex items-center justify-center';
                    if (textWrap) textWrap.className = 'font-baloo font-bold text-xs text-[#6B6B55]';
                });

                // Highlight active button
                btn.className = 'tab-btn bg-[#1F3D20] text-[#F5F4DA] p-3.5 rounded-2xl flex flex-col items-center justify-center gap-1.5 shadow-sm cursor-pointer transition-all';
                const activeIconWrap = btn.querySelector('div');
                const activeTextWrap = btn.querySelector('span');
                if (activeIconWrap) activeIconWrap.className = 'w-8 h-8 rounded-full bg-[#FBFAF0]/20 text-[#F5F4DA] flex items-center justify-center';
                if (activeTextWrap) activeTextWrap.className = 'font-baloo font-extrabold text-xs text-[#F5F4DA]';

                // Toggle panels
                tabPanels.forEach(panel => {
                    if (panel.id === targetId) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });
            });
        });

        // Badge Toast Interactivity
        document.querySelectorAll('.badge-item').forEach(badge => {
            badge.addEventListener('click', () => {
                const title = badge.dataset.title;
                const desc = badge.dataset.desc;
                if (window.showToast) {
                    window.showToast(`${title}: ${desc}`, 'success');
                } else {
                    alert(`${title}: ${desc}`);
                }
            });
        });

        // Alliance Modal Logic
        const expandBtn = document.querySelector('#expand-alliance-btn');
        const modal = document.querySelector('#alliance-modal');
        const closeModalBtn = document.querySelector('#close-alliance-modal');
        const copyBtn = document.querySelector('#copy-code-btn');
        const codeVal = document.querySelector('#alliance-code-val');

        if (expandBtn && modal) {
            expandBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        }
        if (closeModalBtn && modal) {
            closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
        }
        if (copyBtn && codeVal) {
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(codeVal.textContent.trim());
                if (window.showToast) {
                    window.showToast('Kode Aliansi berhasil disalin ke clipboard!', 'success');
                } else {
                    alert('Kode Aliansi berhasil disalin!');
                }
            });
        }
    });
</script>
@endpush
