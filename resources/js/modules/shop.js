/**
 * Shop Module (Toko Benih, Alat & Material Kebun)
 * Plant Guardian Design System
 */

import apiClient from '../api-client.js';

export class ShopModule {
  constructor(options = {}) {
    this.containerElement = options.containerElement || document.querySelector('#shop-container');
    this.catalog = [];
    this.inventory = [];
    this.userCoin = 0;
    this.activeCategory = 'all';
    this.currentAvatar = 'default';
  }

  async init() {
    await this.loadShopData();
    this.bindEvents();
  }

  async loadShopData() {
    try {
      const response = await apiClient.get('/shop');
      this.catalog = response.catalog || [];
      this.inventory = response.inventory || [];
      this.userCoin = response.user_coin || 0;
      this.currentAvatar = response.current_avatar || 'default';

      // Synchronize all coin displays on page
      if (typeof window.updateUserCoin === 'function') {
        window.updateUserCoin(this.userCoin);
      }

      this.render();
    } catch (error) {
      console.error('Gagal memuat data Shop:', error);
      if (this.containerElement) {
        this.containerElement.innerHTML = `
          <div class="text-center py-12 text-[#6B6B55]">
            <p>Gagal memuat katalog toko. Silakan coba lagi nanti.</p>
          </div>
        `;
      }
    }
  }

  bindEvents() {
    if (!this.containerElement) return;

    this.containerElement.addEventListener('click', async (e) => {
      // Category Filter Buttons
      const filterBtn = e.target.closest('[data-category]');
      if (filterBtn) {
        this.activeCategory = filterBtn.dataset.category;
        this.renderCategoryFilters();
        this.renderItems();
        return;
      }

      // Buy Button
      const buyBtn = e.target.closest('.buy-btn');
      if (buyBtn) {
        const itemCode = buyBtn.dataset.itemCode;
        await this.buyItem(itemCode, buyBtn);
        return;
      }

      // Equip Avatar Button
      const equipBtn = e.target.closest('.equip-avatar-btn');
      if (equipBtn) {
        const avatarKey = equipBtn.dataset.avatarKey;
        await this.equipAvatar(avatarKey, equipBtn);
      }
    });
  }

  async buyItem(itemCode, buttonElement) {
    if (!itemCode || buttonElement.disabled) return;

    const originalText = buttonElement.innerHTML;
    buttonElement.disabled = true;
    buttonElement.innerHTML = `<span class="animate-spin inline-block mr-1">⏳</span> Membeli...`;

    try {
      const response = await apiClient.post('/shop/buy', { item_code: itemCode });
      
      this.userCoin = response.user_coin;

      // Synchronize all coin displays (Header & Shop Card)
      if (typeof window.updateUserCoin === 'function') {
        window.updateUserCoin(this.userCoin);
      }

      // Update inventory record locally
      const updatedInvItem = response.inventory_item;
      const existingInvIndex = this.inventory.findIndex(i => i.item_code === itemCode);
      if (existingInvIndex >= 0) {
        this.inventory[existingInvIndex] = updatedInvItem;
      } else {
        this.inventory.push(updatedInvItem);
      }

      this.showToast(response.message || 'Pembelian berhasil!', 'success');
      this.render();
    } catch (error) {
      const errorMsg = error.response?.data?.message || error.message || 'Gagal membeli item.';
      this.showToast(errorMsg, 'error');
      buttonElement.disabled = false;
      buttonElement.innerHTML = originalText;
    }
  }

  async equipAvatar(avatarKey, buttonElement) {
    if (!avatarKey || buttonElement.disabled) return;

    const originalText = buttonElement.innerHTML;
    buttonElement.disabled = true;
    buttonElement.innerHTML = `<span class="animate-spin inline-block mr-1">⏳</span>...`;

    try {
      const response = await apiClient.post('/shop/equip-avatar', { avatar_code: avatarKey });
      this.currentAvatar = response.current_avatar;
      this.showToast(response.message || 'Foto profil diperbarui!', 'success');
      this.render();
    } catch (error) {
      const errorMsg = error.response?.data?.message || error.message || 'Gagal memperbarui foto profil.';
      this.showToast(errorMsg, 'error');
      buttonElement.disabled = false;
      buttonElement.innerHTML = originalText;
    }
  }

  showToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
      window.showToast(message, type);
    } else {
      alert(message);
    }
  }

  renderCategoryFilters() {
    // Categories filter removed per user requirement
  }

  renderItems() {
    const itemsGrid = document.querySelector('#shop-items-grid');
    if (!itemsGrid) return;

    const filteredItems = this.catalog;

    if (filteredItems.length === 0) {
      const t = window.translations || {};
      itemsGrid.innerHTML = `
        <div class="col-span-full text-center py-12 text-[#6B6B55]">
          <p class="font-baloo font-bold text-base">${t.out_of_stock || 'Stok Habis'}</p>
        </div>
      `;
      return;
    }

    itemsGrid.innerHTML = filteredItems.map(item => {
      const invItem = this.inventory.find(i => i.item_code === item.item_code || i.item_code === item.avatar_key);
      const ownedQty = invItem ? invItem.quantity : 0;
      const canAfford = this.userCoin >= item.price;
      const isEquipped = item.item_type === 'avatar' && this.currentAvatar === item.avatar_key;

      return `
        <div class="card-gg card-gg-hover p-5 flex flex-col justify-between relative group">
          <!-- Top Badge & Owned Counter -->
          <div>
            <div class="flex items-center justify-between mb-3">
              ${item.item_type === 'avatar' && item.image ? `
                <div class="w-14 h-14 rounded-full border-2 border-[#1F3D20] p-0.5 bg-[#FBFAF0] shadow-xs overflow-hidden shrink-0">
                  <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded-full" />
                </div>
              ` : `
                <span class="text-3xl p-2 rounded-2xl bg-[#E7E6BE]/60 border border-[#1F3D20]/10 shadow-xs inline-block">
                  ${item.icon}
                </span>
              `}

              ${isEquipped ? `
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-700 text-[#F5F4DA] text-[10px] font-baloo font-extrabold shadow-xs">
                  ✓ Dipakai
                </span>
              ` : ownedQty > 0 ? `
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[10px] font-baloo font-extrabold shadow-xs">
                  Dimiliki
                </span>
              ` : `
                <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#6B6B55] text-[10px] font-baloo font-extrabold">
                  Baru
                </span>
              `}
            </div>

            <h3 class="font-baloo font-bold text-base text-[#1F3D20] mb-1">
              ${item.name}
            </h3>

            <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed mb-2">
              ${item.description}
            </p>

            <div class="flex flex-wrap gap-1.5 mb-4">
              ${item.item_type === 'avatar' ? `
                <span class="px-2 py-0.5 rounded-md bg-[#D4E6C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  🖼️ Foto Profil Eksklusif
                </span>
              ` : ''}

              ${item.item_type === 'seed' && item.growth_duration_minutes ? `
                <span class="px-2 py-0.5 rounded-md bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ⏱️ ${item.growth_duration_minutes}m Tumbuh
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#D4E6C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ✨ +${item.exp_reward} EXP
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FEF0C7] text-[#8B5A2B] text-[10px] font-baloo font-bold flex items-center gap-1">
                  ${typeof window.getNcIconSvg === 'function' ? window.getNcIconSvg('w-3.5 h-3.5') : '🪙'} +${item.coin_reward} NC
                </span>
              ` : ''}

              ${item.item_type === 'tool' && item.time_reduction_minutes ? `
                <span class="px-2 py-0.5 rounded-md bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ⏱️ Potong -${item.time_reduction_minutes}m
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FADBD8] text-[#78281F] text-[10px] font-baloo font-bold">
                  🧪 Sekali Pakai (Kebun)
                </span>
              ` : ''}

              ${item.item_type === 'tool' && item.discount_percent ? `
                <span class="px-2 py-0.5 rounded-md bg-[#FEF0C7] text-[#8B5A2B] text-[10px] font-baloo font-bold">
                  ⛏️ Diskon ${item.discount_percent}% Lahan
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  📜 Alat Permanen (Kebun)
                </span>
              ` : ''}

              ${item.item_type === 'material' ? `
                <span class="px-2 py-0.5 rounded-md bg-[#D4E6C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ✨ +${item.exp_reward || 50} EXP Bonus
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FEF0C7] text-[#8B5A2B] text-[10px] font-baloo font-bold flex items-center gap-1">
                  ${typeof window.getNcIconSvg === 'function' ? window.getNcIconSvg('w-3.5 h-3.5') : '🪙'} +${item.coin_reward} NC
                </span>
              ` : ''}
            </div>
          </div>

          <!-- Price & Action Button -->
          <div class="pt-4 border-t border-[#1F3D20]/10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 font-baloo font-bold text-sm text-[#1F3D20]">
              ${typeof window.getNcIconSvg === 'function' ? window.getNcIconSvg('w-4 h-4') : '🪙'}
              <span>${item.price}</span>
              <span class="text-[10px] text-[#6B6B55]">NC</span>
            </div>

            ${item.item_type === 'avatar' && ownedQty > 0 ? `
              ${isEquipped ? `
                <button disabled class="btn-gg-secondary opacity-80 text-xs py-1.5 px-4 cursor-default">
                  ✓ Dipakai
                </button>
              ` : `
                <button 
                  data-avatar-key="${item.avatar_key}"
                  class="equip-avatar-btn btn-gg-secondary text-xs py-1.5 px-4 cursor-pointer"
                >
                  Gunakan
                </button>
              `}
            ` : `
              <button 
                data-item-code="${item.item_code}"
                class="buy-btn btn-gg-primary text-xs py-1.5 px-4 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${!canAfford ? 'bg-[#8B6A4C] opacity-60' : ''}"
                ${!canAfford ? 'title="Coin tidak cukup"' : ''}
              >
                ${canAfford ? 'Beli Item' : 'Coin Kurang'}
              </button>
            `}
          </div>
        </div>
      `;
    }).join('');
  }

  render() {
    this.renderCategoryFilters();
    this.renderItems();
  }
}
