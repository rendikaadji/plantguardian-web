/**
 * Shop Module (Toko Benih, Alat & Material Kebun)
 * Garden Guardian Design System
 */

import apiClient from '../api-client.js';

export class ShopModule {
  constructor(options = {}) {
    this.containerElement = options.containerElement || document.querySelector('#shop-container');
    this.catalog = [];
    this.inventory = [];
    this.userCoin = 0;
    this.activeCategory = 'all';
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

      // Update header coin display if available
      const coinElement = document.querySelector('#user-coin');
      if (coinElement) {
        coinElement.textContent = this.userCoin;
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

      // Update coin display
      const coinElement = document.querySelector('#user-coin');
      if (coinElement) {
        coinElement.textContent = this.userCoin;
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

  showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-5 right-5 z-50 px-4 py-3 rounded-2xl shadow-lg font-baloo font-bold text-sm transition-all duration-300 transform translate-y-0 ${
      type === 'success' 
        ? 'bg-[#1F3D20] text-[#F5F4DA] border border-[#E2E1C4]/20' 
        : 'bg-[#C0392B] text-white border border-red-400/20'
    }`;
    toast.innerHTML = `
      <div class="flex items-center gap-2">
        <span>${type === 'success' ? '✨' : '⚠️'}</span>
        <span>${message}</span>
      </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('opacity-0', '-translate-y-2');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  renderCategoryFilters() {
    const categories = [
      { id: 'all', label: 'Semua Item', icon: '🛒' },
      { id: 'seed', label: 'Benih Flora', icon: '🌱' },
      { id: 'tool', label: 'Alat & Perlengkapan', icon: '🛠️' },
      { id: 'material', label: 'Bahan Kompos', icon: '📦' },
    ];

    const filterContainer = document.querySelector('#shop-categories');
    if (!filterContainer) return;

    filterContainer.innerHTML = categories.map(cat => {
      const isActive = this.activeCategory === cat.id;
      return `
        <button data-category="${cat.id}" class="px-4 py-2 rounded-full font-baloo font-bold text-xs sm:text-sm transition-all cursor-pointer ${
          isActive 
            ? 'bg-[#1F3D20] text-[#F5F4DA] shadow-xs' 
            : 'bg-[#E7E6BE] text-[#1F3D20] hover:bg-[#1F3D20]/10'
        }">
          <span class="mr-1.5">${cat.icon}</span>${cat.label}
        </button>
      `;
    }).join('');
  }

  renderItems() {
    const itemsGrid = document.querySelector('#shop-items-grid');
    if (!itemsGrid) return;

    const filteredItems = this.catalog.filter(item => {
      if (this.activeCategory === 'all') return true;
      return item.item_type === this.activeCategory;
    });

    if (filteredItems.length === 0) {
      itemsGrid.innerHTML = `
        <div class="col-span-full text-center py-12 text-[#6B6B55]">
          <p class="font-baloo font-bold text-base">Tidak ada item di kategori ini.</p>
        </div>
      `;
      return;
    }

    itemsGrid.innerHTML = filteredItems.map(item => {
      const invItem = this.inventory.find(i => i.item_code === item.item_code);
      const ownedQty = invItem ? invItem.quantity : 0;
      const canAfford = this.userCoin >= item.price;

      return `
        <div class="card-gg card-gg-hover p-5 flex flex-col justify-between relative group">
          <!-- Top Badge & Owned Counter -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="text-3xl p-2 rounded-2xl bg-[#E7E6BE]/60 border border-[#1F3D20]/10 shadow-xs inline-block">
                ${item.icon}
              </span>
              ${ownedQty > 0 ? `
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[10px] font-baloo font-extrabold shadow-xs">
                  Dimiliki: x${ownedQty}
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

            <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed mb-4">
              ${item.description}
            </p>
          </div>

          <!-- Price & Action Button -->
          <div class="pt-4 border-t border-[#1F3D20]/10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 font-baloo font-bold text-sm text-[#1F3D20]">
              <span class="text-base">🪙</span>
              <span>${item.price}</span>
              <span class="text-[10px] text-[#6B6B55]">NC</span>
            </div>

            <button 
              data-item-code="${item.item_code}"
              class="buy-btn btn-gg-primary text-xs py-1.5 px-4 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${!canAfford ? 'bg-[#8B6A4C] opacity-60' : ''}"
              ${!canAfford ? 'title="Coin tidak cukup"' : ''}
            >
              ${canAfford ? 'Beli Item' : 'Coin Kurang'}
            </button>
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
