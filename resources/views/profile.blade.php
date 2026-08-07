@extends('layouts.app')

@section('title', __('profile.title'))

@push('scripts')
<script>
    window.translations = Object.assign(window.translations || {}, @json(__('profile')));
</script>
@endpush

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
                    {{ __('profile.ranger_role') }}
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
                {{ __('profile.ranger_responsibilities_title') }}
            </h3>
            <p class="text-xs text-[#5C574C] leading-relaxed">
                {{ __('profile.ranger_responsibilities_desc') }}
            </p>

            <div class="pt-2">
                <a href="{{ route('peta') }}" class="w-full py-3 px-4 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-sm hover:bg-[#8B6A4C] transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <span>{{ __('profile.open_ranger_map') }}</span>
                </a>
            </div>
        </div>

        <!-- Account Settings & Change Password Card for Ranger -->
        <div class="card-gg p-5 space-y-4 bg-[#FBFAF0]">
            <h3 class="font-baloo font-bold text-base text-[#1F3D20] flex items-center gap-2">
                <span>⚙️</span> {{ __('profile.account_settings') }}
            </h3>
            
            <div class="text-xs text-[#6B6B55] space-y-1 font-nunito border-t border-[#1F3D20]/10 pt-2">
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                <p><strong>Peran Utama:</strong> <span class="uppercase font-bold text-[#1F3D20]">{{ auth()->user()->role }}</span></p>
            </div>

            <!-- Change Password Form Section -->
            <div class="border-t border-[#1F3D20]/10 pt-3 space-y-3">
                <h4 class="font-baloo font-bold text-sm text-[#1F3D20] flex items-center gap-1.5">
                    <span>{{ __('profile.change_password_title') }}</span>
                </h4>

                @if (session('status'))
                    <div class="p-3 rounded-xl bg-emerald-100 border border-emerald-400 text-emerald-800 text-xs font-nunito font-bold">
                        ✓ {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-2.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-baloo font-bold text-[#1F3D20] mb-0.5">{{ __('profile.current_password_label') }}</label>
                        <div class="relative">
                            <input type="password" id="ranger_current_password" name="current_password" required placeholder="{{ __('profile.current_password_placeholder') }}" class="w-full py-2.5 pl-3 pr-10 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                            <button type="button" onclick="togglePasswordVisibility('ranger_current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#5C574C] hover:text-[#1F3D20] transition-colors p-1 cursor-pointer focus:outline-none flex items-center justify-center" title="Tampilkan/Sembunyikan Kata Sandi">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <span class="text-[10px] text-red-600 font-nunito font-bold block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-baloo font-bold text-[#1F3D20] mb-0.5">{{ __('profile.new_password_label') }}</label>
                        <div class="relative">
                            <input type="password" id="ranger_password" name="password" required placeholder="{{ __('profile.new_password_placeholder') }}" class="w-full py-2.5 pl-3 pr-10 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                            <button type="button" onclick="togglePasswordVisibility('ranger_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#5C574C] hover:text-[#1F3D20] transition-colors p-1 cursor-pointer focus:outline-none flex items-center justify-center" title="Tampilkan/Sembunyikan Kata Sandi">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-[10px] text-red-600 font-nunito font-bold block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-baloo font-bold text-[#1F3D20] mb-0.5">{{ __('profile.confirm_new_password_label') }}</label>
                        <div class="relative">
                            <input type="password" id="ranger_password_confirmation" name="password_confirmation" required placeholder="{{ __('profile.confirm_new_password_placeholder') }}" class="w-full py-2.5 pl-3 pr-10 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                            <button type="button" onclick="togglePasswordVisibility('ranger_password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#5C574C] hover:text-[#1F3D20] transition-colors p-1 cursor-pointer focus:outline-none flex items-center justify-center" title="Tampilkan/Sembunyikan Kata Sandi">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="w-full py-2 rounded-xl bg-[#1F3D20] hover:bg-[#142815] text-[#F5F4DA] font-baloo font-bold text-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                            <span>🔑 {{ __('profile.update_password_button') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="pt-2 border-t border-[#1F3D20]/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#C0392B] hover:bg-[#A93226] text-white font-baloo font-bold text-xs transition-colors cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>{{ __('profile.logout') }}</span>
                    </button>
                </form>
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
            <div class="flex items-center justify-between">
                <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20]">
                    Alliance Friends
                </h2>
                <span id="incoming-requests-count" class="px-2.5 py-0.5 rounded-full bg-amber-500 text-white font-baloo font-extrabold text-xs shadow-xs hidden" title="Permintaan pertemanan masuk">
                    0
                </span>
            </div>

            <!-- Incoming Item Requests Notification Box -->
            <div id="incoming-item-requests-container"></div>

            <!-- Dynamic Friends List Container -->
            <div id="friends-list-container" class="space-y-2.5">
                <div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">
                    Memuat daftar aliansi...
                </div>
            </div>

            <!-- Expand Alliance Button -->
            <button id="open-add-friend-modal-btn" onclick="document.querySelector('#add-friend-modal').classList.remove('hidden')" class="w-full py-3.5 rounded-2xl border-2 border-dashed border-[#1F3D20]/30 text-[#1F3D20] font-baloo font-extrabold text-sm flex items-center justify-center gap-2 hover:bg-[#1F3D20]/5 transition-colors cursor-pointer shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Expand Alliance (Tambah Aliansi)</span>
            </button>
        </div>

        <!-- 6. Account & Settings Options Card -->
        <div class="card-gg p-5 space-y-4 bg-[#FBFAF0]">
            <h3 class="font-baloo font-bold text-base text-[#1F3D20] flex items-center gap-2">
                <span>⚙️</span> {{ __('profile.account_settings') }}
            </h3>
            
            <div class="text-xs text-[#6B6B55] space-y-1 font-nunito border-t border-[#1F3D20]/10 pt-2">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Peran Utama:</strong> <span class="uppercase font-bold text-[#1F3D20]">{{ $user->role }}</span></p>
            </div>

            <!-- Change Password Form Section -->
            <div class="border-t border-[#1F3D20]/10 pt-3 space-y-3">
                <h4 class="font-baloo font-bold text-sm text-[#1F3D20] flex items-center gap-1.5">
                    <span>{{ __('profile.change_password_title') }}</span>
                </h4>

                @if (session('status'))
                    <div class="p-3 rounded-xl bg-emerald-100 border border-emerald-400 text-emerald-800 text-xs font-nunito font-bold">
                        ✓ {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-2.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-baloo font-bold text-[#1F3D20] mb-0.5">{{ __('profile.current_password_label') }}</label>
                        <div class="relative">
                            <input type="password" id="viewer_current_password" name="current_password" required placeholder="{{ __('profile.current_password_placeholder') }}" class="w-full py-2.5 pl-3 pr-10 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                            <button type="button" onclick="togglePasswordVisibility('viewer_current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#5C574C] hover:text-[#1F3D20] transition-colors p-1 cursor-pointer focus:outline-none flex items-center justify-center" title="Tampilkan/Sembunyikan Kata Sandi">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <span class="text-[10px] text-red-600 font-nunito font-bold block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-baloo font-bold text-[#1F3D20] mb-0.5">{{ __('profile.new_password_label') }}</label>
                        <div class="relative">
                            <input type="password" id="viewer_password" name="password" required placeholder="{{ __('profile.new_password_placeholder') }}" class="w-full py-2.5 pl-3 pr-10 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                            <button type="button" onclick="togglePasswordVisibility('viewer_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#5C574C] hover:text-[#1F3D20] transition-colors p-1 cursor-pointer focus:outline-none flex items-center justify-center" title="Tampilkan/Sembunyikan Kata Sandi">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-[10px] text-red-600 font-nunito font-bold block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-baloo font-bold text-[#1F3D20] mb-0.5">{{ __('profile.confirm_new_password_label') }}</label>
                        <div class="relative">
                            <input type="password" id="viewer_password_confirmation" name="password_confirmation" required placeholder="{{ __('profile.confirm_new_password_placeholder') }}" class="w-full py-2.5 pl-3 pr-10 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                            <button type="button" onclick="togglePasswordVisibility('viewer_password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#5C574C] hover:text-[#1F3D20] transition-colors p-1 cursor-pointer focus:outline-none flex items-center justify-center" title="Tampilkan/Sembunyikan Kata Sandi">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="w-full py-2 rounded-xl bg-[#1F3D20] hover:bg-[#142815] text-[#F5F4DA] font-baloo font-bold text-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                            <span>🔑 {{ __('profile.update_password_button') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="pt-2 border-t border-[#1F3D20]/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#C0392B] hover:bg-[#A93226] text-white font-baloo font-bold text-xs transition-colors cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>{{ __('profile.logout') }}</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>

<!-- Modal 1: Tambah Aliansi / Cari Teman & Permintaan Masuk -->
<div id="add-friend-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="card-gg p-6 w-full max-w-lg space-y-4 bg-[#FBFAF0] border-2 border-[#1F3D20]/20 shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span class="text-[10px] font-baloo font-extrabold px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] uppercase">TAMBAH ALIANSI</span>
                <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20] mt-1">Cari & Hubungkan Teman</h3>
            </div>
            <button onclick="document.querySelector('#add-friend-modal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold flex items-center justify-center hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Search Input -->
        <div class="relative">
            <input type="text" id="friend-search-input" placeholder="Cari nama atau email pengguna lain..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#1F3D20]/20 bg-white font-nunito text-xs text-[#1F3D20] focus:outline-none focus:border-[#1F3D20]" />
            <span class="absolute left-3 top-2.5 text-base">🔍</span>
        </div>

        <!-- Search Results List Container -->
        <div id="search-results-container" class="max-h-48 overflow-y-auto space-y-2 pr-1">
            <div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">
                Ketik nama atau email pengguna untuk mulai mencari...
            </div>
        </div>

        <!-- Incoming Friend Requests List -->
        <div class="border-t border-[#1F3D20]/10 pt-3 space-y-2">
            <h4 class="font-baloo font-bold text-xs text-[#1F3D20]">📩 Permintaan Pertemanan Masuk</h4>
            <div id="incoming-requests-container" class="max-h-36 overflow-y-auto space-y-2 pr-1">
                <div class="text-center py-2 text-[#6B6B55] font-nunito text-xs italic">
                    Tidak ada permintaan masuk.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Minta Barang Shop dari Teman -->
<div id="request-item-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="card-gg p-6 w-full max-w-md space-y-4 bg-[#FBFAF0] border-2 border-[#1F3D20]/20 shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span class="text-[10px] font-baloo font-extrabold px-2.5 py-0.5 rounded-full bg-[#8B6A4C] text-[#F5F4DA] uppercase">MINTA BARANG SHOP</span>
                <h3 class="font-baloo font-extrabold text-lg text-[#1F3D20] mt-1">Minta Barang ke <span id="req-friend-name">Teman</span></h3>
            </div>
            <button onclick="window.friendsApp.closeRequestItemModal()" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold flex items-center justify-center hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer">
                ✕
            </button>
        </div>

        <form onsubmit="window.friendsApp.submitItemRequest(event)" class="space-y-3">
            <input type="hidden" id="req-friend-id" value="" />

            <div>
                <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Pilih Barang Shop <span class="text-red-600">*</span></label>
                <select id="req-item-code" required class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]">
                    <option value="seed_sunflower">🌻 Benih Bunga Matahari (Sunflower)</option>
                    <option value="seed_tomato">🍅 Benih Tomat Manis (Tomato)</option>
                    <option value="seed_monstera">🌿 Benih Monstera Deliciosa</option>
                    <option value="seed_orchid">🌸 Benih Anggrek Langka (Orchid)</option>
                    <option value="tool_fertilizer">🧪 Pupuk Organik Super (Speedup 5m)</option>
                    <option value="tool_watering_can">🚿 Penyiram Otomatis (Speedup 10m)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Pesan Permintaan (Opsional)</label>
                <input type="text" id="req-note" placeholder="Contoh: Boleh minta pupuk organik 1 dong..." class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
            </div>

            <div class="pt-2">
                <button type="submit" id="req-submit-btn" class="w-full btn-gg-primary py-2.5 rounded-full text-xs cursor-pointer shadow-xs">
                    📩 Kirim Permintaan Barang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Beri Barang Shop ke Teman (Transfer dari Inventaris) -->
<div id="gift-item-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="card-gg p-6 w-full max-w-md space-y-4 bg-[#FBFAF0] border-2 border-[#1F3D20]/20 shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span class="text-[10px] font-baloo font-extrabold px-2.5 py-0.5 rounded-full bg-[#27AE60] text-white uppercase">HADIAHKAN BARANG</span>
                <h3 class="font-baloo font-extrabold text-lg text-[#1F3D20] mt-1">Beri Barang ke <span id="gift-friend-name">Teman</span></h3>
            </div>
            <button onclick="window.friendsApp.closeGiftItemModal()" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold flex items-center justify-center hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer">
                ✕
            </button>
        </div>

        <form onsubmit="window.friendsApp.submitGiftItem(event)" class="space-y-3">
            <input type="hidden" id="gift-friend-id" value="" />

            <div>
                <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Pilih Barang dari Inventaris Anda <span class="text-red-600">*</span></label>
                <select id="gift-item-code" required class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]">
                    <!-- Rendered dynamically by friends.js -->
                </select>
            </div>

            <div class="pt-2">
                <button type="submit" id="gift-submit-btn" class="w-full btn-gg-primary py-2.5 rounded-full text-xs cursor-pointer shadow-xs">
                    🎁 Kirimkan Barang ke Teman
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const eyeOpen = btn.querySelector('.eye-open');
        const eyeClosed = btn.querySelector('.eye-closed');

        if (input.type === 'password') {
            input.type = 'text';
            if (eyeOpen) eyeOpen.classList.add('hidden');
            if (eyeClosed) eyeClosed.classList.remove('hidden');
        } else {
            input.type = 'password';
            if (eyeOpen) eyeOpen.classList.remove('hidden');
            if (eyeClosed) eyeClosed.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
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

        // Initialize FriendsModule
        if (window.FriendsModule) {
            window.friendsApp = new window.FriendsModule();
            window.friendsApp.init();
        }
    });
</script>
@endpush
