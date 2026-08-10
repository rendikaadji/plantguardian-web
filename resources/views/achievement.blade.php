@extends('layouts.app')

@section('title', __('achievement.title'))

@push('scripts')
<script>
    window.translations = Object.assign(window.translations || {}, @json(__('achievement')));
</script>
@endpush

@section('content')
<div class="space-y-6 max-w-4xl mx-auto py-2">

    <!-- Top Banner (Trophy & Achievement Overview) -->
@php
    $isRanger = auth()->check() && in_array(auth()->user()->role, ['ranger', 'admin']);
@endphp

    <!-- Top Banner (Trophy & Achievement Overview) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1F3D20] via-[#2D4A2E] to-[#152B16] text-[#F5F4DA] p-6 sm:p-8 shadow-xl border border-[#E2E1C4]/20">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FFD700]/20 text-[#FFD700] text-xs font-baloo font-bold backdrop-blur-md border border-[#FFD700]/30">
                    <span>🏆</span>
                    <span>{{ $isRanger ? __('achievement.ranger_badge_header') : __('achievement.badge_header') }}</span>
                </div>

                <h1 class="font-baloo font-extrabold text-3xl sm:text-4xl text-[#F5F4DA] tracking-tight">
                    {{ $isRanger ? __('achievement.ranger_heading') : __('achievement.heading') }}
                </h1>

                <p class="text-xs sm:text-sm text-[#F5F4DA]/80 font-nunito max-w-xl leading-relaxed">
                    {{ $isRanger ? __('achievement.ranger_subtitle') : __('achievement.subtitle') }}
                </p>
            </div>

            <!-- Progress Summary Box -->
            <div class="bg-[#FBFAF0]/10 border border-[#F5F4DA]/20 p-4 rounded-2xl shrink-0 text-center space-y-1 backdrop-blur-md min-w-[150px]">
                <span class="text-[10px] font-baloo font-bold text-[#F5F4DA]/70 uppercase tracking-wider block">{{ __('achievement.total_unlocked') }}</span>
                <span id="achievement-ratio-text" class="font-baloo font-extrabold text-3xl text-[#FFD700] block leading-none">{{ $unlockedCount ?? 0 }} / {{ $totalCount ?? 24 }}</span>
                <span class="text-[10px] font-baloo font-bold text-[#27AE60] bg-[#27AE60]/20 px-2 py-0.5 rounded-full inline-block mt-1">{{ $completionPercentage ?? 0 }}% {{ __('achievement.completed') }}</span>
            </div>
        </div>

        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#FFD700]/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <button onclick="filterAchievements('all')" class="ach-tab-btn px-4 py-2 rounded-full font-baloo font-bold text-xs bg-[#1F3D20] text-[#F5F4DA] cursor-pointer shadow-xs transition-all shrink-0 active-tab" data-cat="all">
            {{ $isRanger ? __('achievement.ranger_tabs.all') : __('achievement.tabs.all') }}
        </button>
        <button onclick="filterAchievements('exploration')" class="ach-tab-btn px-4 py-2 rounded-full font-baloo font-bold text-xs bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] cursor-pointer transition-all shrink-0" data-cat="exploration">
            {{ $isRanger ? __('achievement.ranger_tabs.exploration') : __('achievement.tabs.exploration') }}
        </button>
        <button onclick="filterAchievements('garden')" class="ach-tab-btn px-4 py-2 rounded-full font-baloo font-bold text-xs bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] cursor-pointer transition-all shrink-0" data-cat="garden">
            {{ $isRanger ? __('achievement.ranger_tabs.garden') : __('achievement.tabs.garden') }}
        </button>
        <button onclick="filterAchievements('shop')" class="ach-tab-btn px-4 py-2 rounded-full font-baloo font-bold text-xs bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] cursor-pointer transition-all shrink-0" data-cat="shop">
            {{ $isRanger ? __('achievement.ranger_tabs.shop') : __('achievement.tabs.shop') }}
        </button>
        <button onclick="filterAchievements('social')" class="ach-tab-btn px-4 py-2 rounded-full font-baloo font-bold text-xs bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] cursor-pointer transition-all shrink-0" data-cat="social">
            {{ $isRanger ? __('achievement.ranger_tabs.social') : __('achievement.tabs.social') }}
        </button>
    </div>

    <!-- Achievements Grid Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                <span>🏅</span>
                <span>{{ $isRanger ? __('achievement.ranger_heading') : __('achievement.heading') }}</span>
            </h2>
            <span class="text-xs text-[#6B6B55] font-baloo font-bold">{{ __('achievement.live_update') }}</span>
        </div>

        <div id="achievements-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @php
                if ($isRanger) {
                    $items = [
                        // Category 1: Field Mapping
                        ['code' => 'ranger_first_scan', 'cat' => 'exploration', 'icon' => '📷', 'exp' => 200, 'coin' => 100],
                        ['code' => 'ranger_field_mapper', 'cat' => 'exploration', 'icon' => '🗺️', 'exp' => 400, 'coin' => 200],
                        ['code' => 'ranger_master_mapper', 'cat' => 'exploration', 'icon' => '📡', 'exp' => 800, 'coin' => 400],
                        ['code' => 'ranger_ar_specialist', 'cat' => 'exploration', 'icon' => '👁️', 'exp' => 500, 'coin' => 250],
                        ['code' => 'ranger_survey_veteran', 'cat' => 'exploration', 'icon' => '🧭', 'exp' => 1200, 'coin' => 600],
                        ['code' => 'ranger_legendary_mapper', 'cat' => 'exploration', 'icon' => '🌐', 'exp' => 2500, 'coin' => 1250],

                        // Category 2: Care & Education
                        ['code' => 'ranger_care_guide', 'cat' => 'garden', 'icon' => '🌱', 'exp' => 300, 'coin' => 150],
                        ['code' => 'ranger_botanical_educator', 'cat' => 'garden', 'icon' => '📖', 'exp' => 600, 'coin' => 300],
                        ['code' => 'ranger_conservation_guardian', 'cat' => 'garden', 'icon' => '🛡️', 'exp' => 750, 'coin' => 375],
                        ['code' => 'ranger_herbarium_curator', 'cat' => 'garden', 'icon' => '🔬', 'exp' => 1000, 'coin' => 500],
                        ['code' => 'ranger_species_sentinel', 'cat' => 'garden', 'icon' => '🌳', 'exp' => 1500, 'coin' => 750],
                        ['code' => 'ranger_master_taxonomist', 'cat' => 'garden', 'icon' => '🎓', 'exp' => 3000, 'coin' => 1500],

                        // Category 3: Field Logistics
                        ['code' => 'ranger_logistics_master', 'cat' => 'shop', 'icon' => '📦', 'exp' => 350, 'coin' => 175],
                        ['code' => 'ranger_seed_collector', 'cat' => 'shop', 'icon' => '🌸', 'exp' => 500, 'coin' => 250],
                        ['code' => 'ranger_equipment_expert', 'cat' => 'shop', 'icon' => '🛠️', 'exp' => 750, 'coin' => 375],
                        ['code' => 'ranger_supplies_master', 'cat' => 'shop', 'icon' => '🏬', 'exp' => 1500, 'coin' => 750],
                        ['code' => 'ranger_seed_vault_keeper', 'cat' => 'shop', 'icon' => '💎', 'exp' => 1200, 'coin' => 600],
                        ['code' => 'ranger_field_quartermaster', 'cat' => 'shop', 'icon' => '🚜', 'exp' => 3000, 'coin' => 1500],

                        // Category 4: Ranger Prestige
                        ['code' => 'ranger_alliance_leader', 'cat' => 'social', 'icon' => '🤝', 'exp' => 350, 'coin' => 175],
                        ['code' => 'ranger_field_dispatcher', 'cat' => 'social', 'icon' => '🎁', 'exp' => 450, 'coin' => 225],
                        ['code' => 'ranger_patrol_officer', 'cat' => 'social', 'icon' => '🎖️', 'exp' => 600, 'coin' => 300],
                        ['code' => 'ranger_senior_officer', 'cat' => 'social', 'icon' => '🏅', 'exp' => 1200, 'coin' => 600],
                        ['code' => 'ranger_master_synergy', 'cat' => 'social', 'icon' => '💖', 'exp' => 1000, 'coin' => 500],
                        ['code' => 'ranger_supreme_sentinel', 'cat' => 'social', 'icon' => '👑', 'exp' => 5000, 'coin' => 2500],
                    ];
                } else {
                    $items = [
                        // Category 1: Exploration
                        ['code' => 'flora_explorer', 'cat' => 'exploration', 'icon' => '🌱', 'exp' => 150, 'coin' => 75],
                        ['code' => 'region_mapper', 'cat' => 'exploration', 'icon' => '🗺️', 'exp' => 300, 'coin' => 150],
                        ['code' => 'seedex_expert', 'cat' => 'exploration', 'icon' => '📚', 'exp' => 500, 'coin' => 250],
                        ['code' => 'master_botanist', 'cat' => 'exploration', 'icon' => '🔬', 'exp' => 1000, 'coin' => 500],
                        ['code' => 'ranger_pioneer', 'cat' => 'exploration', 'icon' => '📡', 'exp' => 400, 'coin' => 200],
                        ['code' => 'legendary_explorer', 'cat' => 'exploration', 'icon' => '🧭', 'exp' => 2000, 'coin' => 1000],

                        // Category 2: Garden
                        ['code' => 'digital_farmer', 'cat' => 'garden', 'icon' => '🌻', 'exp' => 200, 'coin' => 100],
                        ['code' => 'harvest_master', 'cat' => 'garden', 'icon' => '🌾', 'exp' => 600, 'coin' => 300],
                        ['code' => 'hydrator_master', 'cat' => 'garden', 'icon' => '🚿', 'exp' => 300, 'coin' => 150],
                        ['code' => 'super_fertilizer', 'cat' => 'garden', 'icon' => '🧪', 'exp' => 400, 'coin' => 200],
                        ['code' => 'green_thumb', 'cat' => 'garden', 'icon' => '🏡', 'exp' => 800, 'coin' => 400],
                        ['code' => 'agrarian_legend', 'cat' => 'garden', 'icon' => '🚜', 'exp' => 2500, 'coin' => 1250],

                        // Category 3: Shop & Items
                        ['code' => 'loyal_shopper', 'cat' => 'shop', 'icon' => '🛒', 'exp' => 350, 'coin' => 175],
                        ['code' => 'rare_seed_collector', 'cat' => 'shop', 'icon' => '🌸', 'exp' => 500, 'coin' => 250],
                        ['code' => 'botanical_investor', 'cat' => 'shop', 'icon' => '💰', 'exp' => 750, 'coin' => 375],
                        ['code' => 'shop_tycoon', 'cat' => 'shop', 'icon' => '🏬', 'exp' => 1500, 'coin' => 750],
                        ['code' => 'seed_hoarder', 'cat' => 'shop', 'icon' => '💎', 'exp' => 1200, 'coin' => 600],
                        ['code' => 'equipment_master', 'cat' => 'shop', 'icon' => '🛠️', 'exp' => 3000, 'coin' => 1500],

                        // Category 4: Social & Guardian Titles
                        ['code' => 'alliance_guardian', 'cat' => 'social', 'icon' => '🤝', 'exp' => 350, 'coin' => 175],
                        ['code' => 'alliance_veteran', 'cat' => 'social', 'icon' => '🛡️', 'exp' => 800, 'coin' => 400],
                        ['code' => 'alliance_courier', 'cat' => 'social', 'icon' => '🎁', 'exp' => 450, 'coin' => 225],
                        ['code' => 'social_philanthropist', 'cat' => 'social', 'icon' => '💖', 'exp' => 1000, 'coin' => 500],
                        ['code' => 'ecosystem_master', 'cat' => 'social', 'icon' => '👑', 'exp' => 1200, 'coin' => 600],
                        ['code' => 'ancient_legend', 'cat' => 'social', 'icon' => '🏆', 'exp' => 5000, 'coin' => 2500],
                    ];
                }
            @endphp

            @foreach($items as $item)
                @php
                    $code = $item['code'];
                    $achData = $achievements[$code] ?? null;
                    $isClaimed = $achData['is_claimed'] ?? false;
                    $isCompleted = $achData['is_completed'] ?? false;
                    $canClaim = $achData['can_claim'] ?? false;
                    $current = $achData['current'] ?? 0;
                    $target = $achData['target'] ?? 1;
                @endphp

                <div class="ach-card card-gg card-gg-hover p-5 flex items-start gap-4 bg-[#FBFAF0] border-l-4 {{ $isClaimed || $isCompleted ? 'border-l-[#27AE60]' : ($current > 0 ? 'border-l-amber-500' : 'border-l-gray-400') }}" data-category="{{ $item['cat'] }}">
                    <div class="w-14 h-14 rounded-2xl {{ $isClaimed || $isCompleted ? 'bg-[#1F3D20]' : 'bg-gray-600' }} text-3xl flex items-center justify-center shrink-0 shadow-md">
                        {{ $item['icon'] }}
                    </div>
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="font-baloo font-bold text-base text-[#1F3D20]">{{ __('achievement.cards.'.$code.'.title') }}</h3>

                            @if($isClaimed || $canClaim)
                                <span class="px-2.5 py-0.5 rounded-full bg-[#27AE60]/20 text-[#27AE60] font-baloo font-extrabold text-[10px]">
                                    {{ __('achievement.status.completed') }}
                                </span>
                            @elseif($current > 0)
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-baloo font-extrabold text-[10px]">
                                    {{ __('achievement.status.in_progress') }} ({{ $current }}/{{ $target }})
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-gray-200 text-gray-700 font-baloo font-extrabold text-[10px]">
                                    {{ __('achievement.status.locked') }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
                            {{ __('achievement.cards.'.$code.'.desc') }}
                        </p>

                        <div class="pt-1 flex items-center justify-between text-[11px] font-baloo font-bold text-[#1F3D20]">
                            <span>{{ __('achievement.reward_label', ['exp' => $item['exp'], 'coin' => $item['coin']]) }}</span>

                            @if($isClaimed)
                                <span class="text-[#27AE60] font-baloo font-bold text-xs">{{ __('achievement.status.claimed') }}</span>
                            @elseif($canClaim)
                                <button onclick="window.claimAchievement('{{ $code }}', this)" class="claim-btn px-3.5 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-xs font-baloo font-bold hover:bg-[#2D4A2E] cursor-pointer shadow-xs transition-transform active:scale-95">
                                    {{ __('achievement.status.claim_btn') }}
                                </button>
                            @elseif($current > 0)
                                <span class="text-amber-700 bg-amber-100/60 px-2 py-0.5 rounded-md text-[10px]">({{ $current }}/{{ $target }}) {{ __('achievement.status.in_progress') }}</span>
                            @else
                                <span class="text-gray-400">{{ __('achievement.status.locked') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {

        window.filterAchievements = function(cat) {
            document.querySelectorAll('.ach-tab-btn').forEach(btn => {
                if (btn.dataset.cat === cat) {
                    btn.classList.remove('bg-[#E2E1C4]', 'text-[#1F3D20]');
                    btn.classList.add('bg-[#1F3D20]', 'text-[#F5F4DA]');
                } else {
                    btn.classList.remove('bg-[#1F3D20]', 'text-[#F5F4DA]');
                    btn.classList.add('bg-[#E2E1C4]', 'text-[#1F3D20]');
                }
            });

            document.querySelectorAll('.ach-card').forEach(card => {
                if (cat === 'all' || card.dataset.category === cat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        };

        window.claimAchievement = async function(code, btnElement) {
            if (!btnElement || btnElement.disabled) return;

            const originalText = btnElement.innerHTML;
            const t = window.translations || {};
            btnElement.disabled = true;
            btnElement.innerHTML = `⏳ ${t.status?.claiming || 'Mengklaim...'}`;

            try {
                const client = window.apiClient;
                const response = await client.post('/achievements/claim', {
                    achievement_code: code
                });

                if (typeof window.updateUserCoin === 'function') {
                    window.updateUserCoin(response.user_coin);
                }
                if (typeof window.updateUserExp === 'function') {
                    window.updateUserExp(response.user_exp);
                }

                if (typeof window.showToast === 'function') {
                    window.showToast(response.message || 'Hadiah berhasil diklaim!', 'success');
                } else {
                    alert(response.message || 'Hadiah berhasil diklaim!');
                }

                // Replace button with claimed text badge
                const container = btnElement.parentElement;
                btnElement.remove();
                const claimedBadge = document.createElement('span');
                claimedBadge.className = 'text-[#27AE60] font-baloo font-bold text-xs';
                claimedBadge.textContent = t.status?.claimed || '✓ Telah Diklaim';
                container.appendChild(claimedBadge);

            } catch (error) {
                console.error('Gagal mengklaim achievement:', error);
                const errorMsg = error.response?.data?.message || error.message || 'Gagal mengklaim hadiah.';
                if (typeof window.showToast === 'function') {
                    window.showToast(errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
                btnElement.disabled = false;
                btnElement.innerHTML = originalText;
            }
        };

    });
</script>
@endpush
@endsection
