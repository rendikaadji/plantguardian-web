@extends('layouts.app')

@section('title', __('shop.title'))

@push('scripts')
<script>
    window.translations = Object.assign(window.translations || {}, @json(__('shop')));
</script>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-xs font-semibold mb-2 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>{{ __('shop.official_store') }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-[#1F3D20] tracking-tight font-baloo">{{ __('shop.heading') }}</h1>
            <p class="text-xs text-[#6B6B55] mt-1 font-nunito">{{ __('shop.subtitle') }}</p>
        </div>

        <!-- Wallet Card Summary -->
        <div class="card-gg p-4 flex items-center gap-3 bg-[#FBFAF0] shadow-xs self-start md:self-auto">
            <div class="w-10 h-10 rounded-full bg-[#1F3D20] flex items-center justify-center shadow-xs shrink-0">
                <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" fill="#F4C430" stroke="#B8860B" stroke-width="1.5"/>
                    <circle cx="12" cy="12" r="7.5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                    <path d="M12 6.5c-3 3.5-3.5 7.5-1.2 10.5 3-3.5 3.5-7.5 1.2-10.5z" fill="#1F3D20"/>
                    <path d="M12 6.5c3 3.5 3.5 7.5 1.2 10.5-3-3.5-3.5-7.5-1.2-10.5z" fill="#27AE60"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-bold text-[#6B6B55] block leading-none">{{ __('shop.coin_balance') }}</span>
                <span class="font-baloo font-extrabold text-xl text-[#1F3D20]">
                    <span id="shop-user-coin">{{ auth()->user()->coin ?? 0 }}</span> <span class="text-xs font-bold text-[#6B6B55]">NC</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Shop Container & Items Grid -->
    <div id="shop-container" class="space-y-6">
        <div id="shop-items-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Rendered dynamically by shop.js -->
            <div class="col-span-full text-center py-12 text-[#6B6B55]">
                <span class="animate-pulse inline-block text-2xl mb-2">🌿</span>
                <p class="font-baloo font-bold">Memuat katalog toko...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        if (window.ShopModule) {
            const shop = new window.ShopModule({
                containerElement: document.querySelector('#shop-container')
            });
            await shop.init();
        }
    });
</script>
@endpush
