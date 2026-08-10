@extends('layouts.admin')

@section('title', __('admin.user_management_title') . ' — Plant Guardian')
@section('header_title', __('admin.user_management_title'))

@section('content')
<div class="space-y-6 max-w-7xl mx-auto py-2">

    <!-- Alert Success Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-[#27AE60]/15 border border-[#27AE60]/30 text-[#1F3D20] font-baloo font-bold text-sm flex items-center gap-3 shadow-xs">
            <span class="text-xl">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Top Summary Metrics for User Page -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#1F3D20] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.total_users') }}</span>
            <div class="font-baloo font-extrabold text-2xl text-[#1F3D20]">{{ number_format($stats['total_users']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#4C8C4A] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.viewers') }}</span>
            <div class="font-baloo font-extrabold text-2xl text-[#4C8C4A]">{{ number_format($stats['total_viewers']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#8B6A4C] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.rangers') }}</span>
            <div class="font-baloo font-extrabold text-2xl text-[#8B6A4C]">{{ number_format($stats['total_rangers']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#FFD700] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">ADMINS</span>
            <div class="font-baloo font-extrabold text-2xl text-[#8B6A4C]">{{ number_format($stats['total_admins']) }}</div>
        </div>
    </div>

    <!-- Main Users Control Table Card -->
    <div class="card-gg p-6 space-y-5 bg-[#FBFAF0] border border-[#1F3D20]/15 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1F3D20]/10 pb-4">
            <div>
                <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                    <span>👥</span>
                    <span>{{ __('admin.user_management_title') }}</span>
                </h2>
                <p class="font-nunito text-xs text-[#6B6B55]">{{ __('admin.user_management_subtitle') }}</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('admin.search_placeholder') }}" class="px-4 py-2 rounded-xl border border-[#1F3D20]/20 bg-white font-nunito text-xs text-[#1F3D20] focus:outline-none focus:border-[#1F3D20] w-64 shadow-xs" />
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                    {{ __('admin.search_btn') }}
                </button>
                @if($search)
                    <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-xl bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20]/10">{{ __('admin.reset_btn') }}</a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto rounded-2xl border border-[#1F3D20]/10 shadow-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs tracking-wider">
                        <th class="py-3.5 px-4">{{ __('admin.col_user') }}</th>
                        <th class="py-3.5 px-4">{{ __('admin.col_current_role') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('admin.col_level_exp') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('admin.col_coin') }}</th>
                        <th class="py-3.5 px-4">{{ __('admin.col_joined') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('admin.col_role_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1F3D20]/10 font-nunito text-xs bg-white">
                    @forelse($users as $userItem)
                        <tr class="hover:bg-[#FBFAF0] transition-colors">
                            <!-- User Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#E2E1C4] font-baloo font-extrabold text-sm text-[#1F3D20] flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($userItem->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-baloo font-bold text-sm text-[#1F3D20] block leading-snug">{{ $userItem->name }}</span>
                                        <span class="text-[11px] text-[#6B6B55] block">{{ $userItem->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-4">
                                @if($userItem->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full bg-[#FFD700]/30 text-[#8B6A4C] font-baloo font-extrabold text-[11px] border border-[#FFD700] inline-flex items-center gap-1">
                                        👑 ADMIN
                                    </span>
                                @elseif($userItem->role === 'ranger')
                                    <span class="px-2.5 py-1 rounded-full bg-[#8B6A4C]/15 text-[#8B6A4C] font-baloo font-extrabold text-[11px] border border-[#8B6A4C]/30 inline-flex items-center gap-1">
                                        🌿 RANGER
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-[#4C8C4A]/15 text-[#4C8C4A] font-baloo font-extrabold text-[11px] border border-[#4C8C4A]/30 inline-flex items-center gap-1">
                                        🎒 VIEWER
                                    </span>
                                @endif
                            </td>

                            <!-- Level & EXP -->
                            <td class="py-3.5 px-4 text-center font-baloo font-bold text-[#1F3D20]">
                                <div>Lvl {{ $userItem->level }}</div>
                                <span class="text-[10px] text-[#6B6B55] font-extrabold block">{{ number_format($userItem->exp) }} EXP</span>
                            </td>

                            <!-- Coin -->
                            <td class="py-3.5 px-4 text-center font-baloo font-extrabold text-[#1F3D20]">
                                🪙 {{ number_format($userItem->coin) }} NC
                            </td>

                            <!-- Join Date -->
                            <td class="py-3.5 px-4 text-[#6B6B55] font-mono-code text-[11px]">
                                {{ $userItem->created_at ? $userItem->created_at->format('d M Y H:i') : '-' }}
                            </td>

                            <!-- Role Change Form & Detail Action -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" onclick='openUserDetailModal(@json($userItem))' class="px-2.5 py-1 rounded-lg bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer shadow-xs">
                                        {{ __('admin.detail_btn') }}
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.update-role', $userItem->id) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <select name="role" class="px-2 py-1 rounded-lg border border-[#1F3D20]/20 bg-[#FBFAF0] font-baloo font-bold text-xs text-[#1F3D20] focus:outline-none">
                                            <option value="viewer" {{ $userItem->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                            <option value="ranger" {{ $userItem->role === 'ranger' ? 'selected' : '' }}>Ranger</option>
                                            <option value="admin" {{ $userItem->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-[#1F3D20] hover:bg-[#2D4A2E] text-[#F5F4DA] font-baloo font-bold text-xs transition-colors cursor-pointer shadow-xs">
                                            {{ __('admin.save_btn') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-[#6B6B55] font-baloo font-bold">
                                {{ __('admin.no_users_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="pt-2">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- User Detail Modal Component -->
<div id="user-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="card-gg p-6 w-full max-w-lg space-y-5 bg-[#FBFAF0] border-2 border-[#1F3D20]/20 shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-3">
            <div class="flex items-center gap-3">
                <div id="modal-avatar" class="w-12 h-12 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-lg flex items-center justify-center shrink-0 shadow-xs">
                    US
                </div>
                <div>
                    <h3 id="modal-user-name" class="font-baloo font-extrabold text-lg text-[#1F3D20] leading-none">Nama Pengguna</h3>
                    <span id="modal-user-email" class="text-xs text-[#6B6B55] font-nunito">user@example.com</span>
                </div>
            </div>
            <button type="button" onclick="closeUserDetailModal()" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold flex items-center justify-center hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer">
                ✕
            </button>
        </div>

        <div class="space-y-4 font-nunito text-xs text-[#2A2A22]">
            <div class="flex items-center justify-between p-3 rounded-xl bg-[#E2E1C4]/40 border border-[#1F3D20]/10">
                <span class="font-baloo font-bold text-[#6B6B55]">Role Pengguna:</span>
                <span id="modal-user-role-badge" class="px-3 py-1 rounded-full font-baloo font-extrabold text-xs">
                    ROLE
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 space-y-1">
                    <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block">Level & EXP</span>
                    <div id="modal-user-level" class="font-baloo font-extrabold text-base text-[#1F3D20]">Lvl 1</div>
                    <div id="modal-user-exp" class="text-[11px] text-[#27AE60] font-bold">0 EXP</div>
                </div>

                <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 space-y-1">
                    <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block">Saldo Digital Coin</span>
                    <div id="modal-user-coin" class="font-baloo font-extrabold text-base text-[#8B6A4C]">🪙 0 NC</div>
                </div>
            </div>

            <div class="space-y-2 p-3 rounded-xl bg-white border border-[#1F3D20]/10">
                <div class="flex justify-between py-1 border-b border-[#1F3D20]/5">
                    <span class="text-[#6B6B55]">ID Pengguna:</span>
                    <span id="modal-user-id" class="font-mono-code font-bold text-[#1F3D20]">#1</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1F3D20]/5">
                    <span class="text-[#6B6B55]">Terdaftar Pada:</span>
                    <span id="modal-user-created" class="font-bold text-[#1F3D20]">-</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-[#6B6B55]">Preferensi Bahasa:</span>
                    <span id="modal-user-locale" class="font-bold uppercase text-[#1F3D20]">ID</span>
                </div>
            </div>

            <div class="space-y-2 p-3 rounded-xl bg-white border border-[#1F3D20]/10">
                <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-2">
                    <h4 id="modal-activity-title" class="font-baloo font-bold text-xs sm:text-sm text-[#1F3D20]">
                        Daftar Tanaman Terkait
                    </h4>
                    <span id="modal-activity-count" class="px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-[10px]">
                        0 Data
                    </span>
                </div>

                <div id="modal-activity-list" class="max-h-52 overflow-y-auto space-y-2 pr-1">
                    <div class="text-center py-4 text-[#6B6B55] font-nunito italic">
                        Memuat data tanaman...
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-[#1F3D20]/10 flex justify-end">
            <button type="button" onclick="closeUserDetailModal()" class="px-5 py-2 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openUserDetailModal(user) {
        document.getElementById('modal-avatar').textContent = user.name ? user.name.substring(0, 2).toUpperCase() : 'US';
        document.getElementById('modal-user-name').textContent = user.name || '-';
        document.getElementById('modal-user-email').textContent = user.email || '-';
        document.getElementById('modal-user-id').textContent = '#' + user.id;
        document.getElementById('modal-user-level').textContent = 'Lvl ' + (user.level || 1);
        document.getElementById('modal-user-exp').textContent = (user.exp || 0).toLocaleString() + ' EXP';
        document.getElementById('modal-user-coin').textContent = '🪙 ' + (user.coin || 0).toLocaleString() + ' NC';
        document.getElementById('modal-user-created').textContent = user.created_at ? new Date(user.created_at).toLocaleString('id-ID') : '-';
        document.getElementById('modal-user-locale').textContent = (user.locale || 'id').toUpperCase();

        const badge = document.getElementById('modal-user-role-badge');
        if (user.role === 'admin') {
            badge.className = 'px-3 py-1 rounded-full bg-[#FFD700]/30 text-[#8B6A4C] font-baloo font-extrabold text-xs border border-[#FFD700]';
            badge.textContent = '👑 ADMIN';
        } else if (user.role === 'ranger') {
            badge.className = 'px-3 py-1 rounded-full bg-[#8B6A4C]/15 text-[#8B6A4C] font-baloo font-extrabold text-xs border border-[#8B6A4C]/30';
            badge.textContent = '🌿 RANGER';
        } else {
            badge.className = 'px-3 py-1 rounded-full bg-[#4C8C4A]/15 text-[#4C8C4A] font-baloo font-extrabold text-xs border border-[#4C8C4A]/30';
            badge.textContent = '🎒 VIEWER';
        }

        document.getElementById('user-detail-modal').classList.remove('hidden');

        const activityTitle = document.getElementById('modal-activity-title');
        const activityCount = document.getElementById('modal-activity-count');
        const activityList = document.getElementById('modal-activity-list');

        activityList.innerHTML = '<div class="text-center py-4 text-[#6B6B55] font-nunito italic">Memuat data tanaman...</div>';

        fetch(`/admin/users/${user.id}/details`)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.activity) return;

                const act = data.activity;
                activityCount.textContent = act.total_count + ' Data';

                if (act.type === 'ranger') {
                    activityTitle.textContent = '📸 Tanaman Difoto & Dipindai Ranger';
                } else {
                    activityTitle.textContent = '🌿 Tanaman Ditemukan oleh Viewer';
                }

                if (!act.items || act.items.length === 0) {
                    activityList.innerHTML = `<div class="text-center py-4 text-[#6B6B55] font-nunito italic">${act.type === 'ranger' ? 'Belum ada tanaman yang difoto oleh Ranger ini.' : 'Belum ada tanaman yang ditemukan oleh Viewer ini.'}</div>`;
                    return;
                }

                let html = '';
                act.items.forEach(item => {
                    html += `
                        <div class="p-2.5 rounded-lg bg-[#FBFAF0] border border-[#1F3D20]/10 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                ${item.photo_url ? `<img src="${item.photo_url}" class="w-10 h-10 object-cover rounded-lg border border-[#1F3D20]/20 shrink-0">` : `<div class="w-10 h-10 rounded-lg bg-[#E2E1C4] flex items-center justify-center text-sm shrink-0">🌿</div>`}
                                <div class="overflow-hidden">
                                    <div class="font-baloo font-bold text-xs text-[#1F3D20] truncate">${item.species_name}</div>
                                    ${item.scientific_name ? `<div class="text-[10px] text-[#6B6B55] italic truncate">${item.scientific_name}</div>` : ''}
                                    <div class="text-[10px] text-[#27AE60] font-mono-code font-bold truncate">📍 ${item.location_text}</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[9px] text-[#6B6B55] block font-mono-code">${item.created_at || item.discovered_at}</span>
                            </div>
                        </div>
                    `;
                });
                activityList.innerHTML = html;
            })
            .catch(err => {
                activityList.innerHTML = '<div class="text-center py-4 text-red-500 font-nunito">Gagal memuat data tanaman.</div>';
            });
    }

    function closeUserDetailModal() {
        document.getElementById('user-detail-modal').classList.add('hidden');
    }
</script>
@endsection
