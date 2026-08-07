/**
 * MiniGame Module (Kebun Virtual Visual & Interaksi Lahan Tanam)
 * Plant Guardian RPG Visual Design System
 */

import apiClient from '../api-client.js';

export class MiniGameModule {
  constructor(options = {}) {
    this.containerElement = options.containerElement || document.querySelector('#garden-plots-container');
    this.plots = [];
    this.seeds = [];
    this.userCoin = 0;
    this.userExp = 0;
    this.updateTimer = null;
    this.selectedPlotForPlanting = null;
  }

  async init() {
    await this.loadPlots();
    this.startAutoRefresh();
  }

  startAutoRefresh() {
    if (this.updateTimer) clearInterval(this.updateTimer);
    this.updateTimer = setInterval(() => this.tickGrowthProgress(), 1000);
  }

  /**
   * Lightweight tick: updates only progress bars & countdowns in-place.
   * When a plant finishes growing, triggers a full re-render once.
   */
  tickGrowthProgress() {
    let needsFullRender = false;
    const now = Date.now();

    this.plots.forEach(plot => {
      const planting = plot.current_planting;
      if (!planting || planting.status !== 'growing') return;

      const plantedAt = planting.planted_at ? new Date(planting.planted_at).getTime() : now;
      const readyAt = planting.ready_at ? new Date(planting.ready_at).getTime() : now;
      const totalMs = Math.max(1, readyAt - plantedAt);
      const elapsedMs = Math.max(0, now - plantedAt);
      const remainingSec = Math.max(0, Math.ceil((readyAt - now) / 1000));

      if (remainingSec <= 0) {
        planting.status = 'ready';
        needsFullRender = true;
        return;
      }

      const percent = Math.min(99, Math.max(1, Math.floor((elapsedMs / totalMs) * 100)));
      const plotEl = this.containerElement?.querySelector(`[data-plot-slot="${plot.slot_number}"]`);
      if (!plotEl) return;

      // Update progress bar width
      const bar = plotEl.querySelector('.growth-bar-fill');
      if (bar) bar.style.width = `${percent}%`;

      // Update countdown text
      const countdown = plotEl.querySelector('.growth-countdown');
      if (countdown) countdown.textContent = this.formatTimeRemaining(remainingSec);

      // Update header time pill
      const pill = plotEl.querySelector('.growth-time-pill');
      if (pill) pill.textContent = `⏳ ${this.formatTimeRemaining(remainingSec)}`;

      // Update plant stage label
      const stageLabel = plotEl.querySelector('.growth-stage-label');
      if (stageLabel) {
        if (percent < 35) stageLabel.textContent = `Tunas Mungil (${percent}%)`;
        else if (percent < 75) stageLabel.textContent = `Tumbuh Berdaun (${percent}%)`;
        else stageLabel.textContent = `Hampir Matang (${percent}%)`;
      }
    });

    if (needsFullRender) this.loadPlots();
  }

  async loadPlots() {
    try {
      const response = await apiClient.get('/minigame/plots');
      this.plots = response.data || [];
      this.seeds = response.seeds || [];
      this.tools = response.tools || [];
      this.userCoin = response.user_coin || 0;
      this.userExp = response.user_exp || 0;

      if (typeof window.updateUserCoin === 'function') {
        window.updateUserCoin(this.userCoin);
      }
      if (typeof window.updateUserExp === 'function') {
        window.updateUserExp(this.userExp);
      }

      this.render();
    } catch (error) {
      console.error('Gagal memuat lahan kebun:', error);
      if (this.containerElement) {
        this.containerElement.innerHTML = `
          <div class="text-center py-12 text-[#F5F4DA]">
            <p>Gagal memuat lahan kebun. Silakan muat ulang halaman.</p>
          </div>
        `;
      }
    }
  }

  showToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
      window.showToast(message, type);
    } else {
      alert(message);
    }
  }

  getSeedDetails(seedCode) {
    const isEn = (window.translations && window.translations.title && window.translations.title.includes('Virtual Garden')) || document.documentElement.lang === 'en';
    const seedMap = {
      'seed_sunflower': { name: isEn ? 'Sunflower' : 'Bunga Matahari', icon: '🌻', duration: 6, exp: 50, coin: 70, price: 50 },
      'seed_tomato': { name: isEn ? 'Organic Tomato' : 'Tomat Organik', icon: '🍅', duration: 12, exp: 90, coin: 110, price: 75 },
      'seed_monstera': { name: isEn ? 'Monstera Deliciosa' : 'Monstera Deliciosa', icon: '🌿', duration: 21, exp: 160, coin: 180, price: 120 },
      'seed_orchid': { name: isEn ? 'Black Orchid' : 'Anggrek Hitam', icon: '🪻', duration: 36, exp: 300, coin: 310, price: 200 },
      'SEED_DEFAULT': { name: isEn ? 'Sunflower' : 'Bunga Matahari', icon: '🌻', duration: 6, exp: 50, coin: 70, price: 50 },
    };
    return seedMap[seedCode] || { name: isEn ? 'Species Seed' : 'Benih Spesies', icon: '🌱', duration: 6, exp: 50, coin: 70, price: 50 };
  }

  formatTimeRemaining(seconds) {
    const isEn = (window.translations && window.translations.title && window.translations.title.includes('Virtual Garden')) || document.documentElement.lang === 'en';
    if (seconds <= 0) return isEn ? 'Ready to harvest!' : 'Siap panen!';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    if (mins > 0) {
      return `${mins}m ${secs}s`;
    }
    return `${secs}s`;
  }

  renderPlantVisual(planting, progressPercent, seedInfo) {
    if (!planting) return '';

    const isReady = planting.status === 'ready' || (planting.ready_at && new Date() >= new Date(planting.ready_at));

    if (isReady) {
      return `
        <div class="relative flex flex-col items-center justify-center h-24 my-1 plant-glow-animation sway-animation">
          <span class="text-5xl filter drop-shadow-md cursor-pointer">${seedInfo.icon}</span>
          <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-[#FBFAF0] text-[#1F3D20] shadow-sm mt-1 border border-[#1F3D20]/20">
            ✨ Siap Panen!
          </span>
        </div>
      `;
    }

    if (progressPercent < 35) {
      return `
        <div class="relative flex flex-col items-center justify-center h-24 my-1 sway-animation">
          <div class="text-3xl filter drop-shadow-sm">🌱</div>
          <span class="growth-stage-label text-[9px] font-bold text-[#F5F4DA] bg-[#2B1B10]/80 px-2 py-0.5 rounded-full mt-1 border border-[#7A5840]/40">
            Tunas Mungil (${progressPercent}%)
          </span>
        </div>
      `;
    }

    if (progressPercent < 75) {
      return `
        <div class="relative flex flex-col items-center justify-center h-24 my-1 sway-animation">
          <div class="text-4xl filter drop-shadow-sm">🌿</div>
          <span class="growth-stage-label text-[9px] font-bold text-[#F5F4DA] bg-[#2B1B10]/80 px-2 py-0.5 rounded-full mt-1 border border-[#7A5840]/40">
            Tumbuh Berdaun (${progressPercent}%)
          </span>
        </div>
      `;
    }

    return `
      <div class="relative flex flex-col items-center justify-center h-24 my-1 sway-animation">
        <div class="text-4xl filter drop-shadow-sm">${seedInfo.icon}</div>
        <span class="growth-stage-label text-[9px] font-bold text-[#F5F4DA] bg-[#2B1B10]/90 px-2 py-0.5 rounded-full mt-1 border border-[#7A5840]/40">
          Hampir Matang (${progressPercent}%)
        </span>
      </div>
    `;
  }

  render() {
    if (!this.containerElement) return;

    if (this.plots.length === 0) {
      this.containerElement.innerHTML = `
        <div class="text-center py-12 text-[#F5F4DA]">
          <span class="animate-spin inline-block text-2xl mb-2">🌿</span>
          <p class="font-baloo font-bold">Membuat petak lahan kebun...</p>
        </div>
      `;
      return;
    }

    const t = window.translations || {};

    const plotsHtml = this.plots.map(plot => {
      const isUnlocked = plot.unlocked;
      const planting = plot.current_planting;

      // 1. VISUAL LOCKED PLOT CARD
      if (!isUnlocked) {
        const cost = plot.purchase_cost || 50;
        const canAfford = this.userCoin >= cost;
        const lockedText = (t.plot_locked || 'Lahan Terkunci #:number').replace(':number', plot.slot_number);
        const costText = (t.cost_label || 'Biaya: 🪙 :cost NC').replace(':cost', cost);
        const unlockBtnText = canAfford ? (t.unlock_btn || 'Buka Lahan') : (t.insufficient_coin || 'Coin Kurang');

        return `
          <div class="card-gg p-4 flex flex-col justify-between items-center text-center relative overflow-hidden group bg-[#FBFAF0] border-2 border-[#8B6A4C]/60 shadow-lg">
            <!-- Soil Bed Backdrop -->
            <div class="w-full rounded-2xl plot-soil-bed p-4 flex flex-col items-center justify-center text-center my-1 min-h-[140px] opacity-75">
              <div class="w-12 h-12 rounded-2xl bg-[#2B1B10]/80 border border-[#8B5A2B]/40 text-[#E7E6BE] flex items-center justify-center text-xl mb-2 shadow-inner">
                🔒
              </div>
              <span class="text-[11px] font-baloo font-bold text-[#F5F4DA] bg-[#2B1B10]/90 px-2.5 py-0.5 rounded-full border border-[#8B5A2B]/30">
                ${lockedText}
              </span>
            </div>

            <div class="w-full pt-3 border-t border-[#1F3D20]/10">
              <div class="flex items-center justify-center gap-1 font-baloo font-bold text-xs text-[#1F3D20] mb-2">
                <span>${costText}</span>
              </div>
              <button 
                data-plot-id="${plot.id}"
                data-cost="${cost}"
                class="unlock-btn w-full btn-gg-primary text-xs py-2 px-3 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                ${!canAfford ? 'disabled title="Coin tidak mencukupi"' : ''}
              >
                ${unlockBtnText}
              </button>
            </div>
          </div>
        `;
      }

      // 2. VISUAL EMPTY UNLOCKED SOIL PLOT CARD
      if (!planting) {
        const totalSeedsCount = this.seeds.reduce((acc, s) => acc + s.quantity, 0);
        const soilPlotText = (t.soil_plot || 'Tanah Gembur #:number').replace(':number', plot.slot_number);
        const readyToPlantText = t.plot_ready_to_plant || 'Lahan Siap Ditanami';
        const plantSeedText = (t.plant_seed_btn || 'Tanam Benih (:count)').replace(':count', totalSeedsCount);
        const buySeedsText = t.buy_seeds_shop || 'Beli Benih di Shop';

        return `
          <div class="card-gg card-gg-hover p-4 flex flex-col justify-between items-center text-center relative bg-[#FBFAF0] border-2 border-[#8B6A4C] shadow-lg">
            <!-- Visual Earth Soil Mound -->
            <div class="w-full rounded-2xl plot-soil-bed p-3 flex flex-col items-center justify-center text-center my-1 min-h-[140px] relative group-hover:border-[#7A5840] transition-colors">
              <div class="w-12 h-12 rounded-2xl bg-[#362215]/80 border border-[#7A5840]/50 text-[#E2E1C4] flex items-center justify-center text-2xl mb-1 shadow-inner">
                ⛏️
              </div>
              <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#264225]/90 px-2.5 py-0.5 rounded-full border border-[#436B42]/40">
                ${soilPlotText}
              </span>
              <p class="text-[10px] text-[#E7E6BE] mt-1 font-nunito opacity-80">${readyToPlantText}</p>
            </div>

            <div class="w-full pt-3 border-t border-[#1F3D20]/10">
              ${totalSeedsCount > 0 ? `
                <button 
                  data-plot-id="${plot.id}"
                  class="open-seed-modal-btn w-full btn-gg-primary text-xs py-2 px-3 cursor-pointer flex items-center justify-center gap-1.5"
                >
                  <span>🌱</span> ${plantSeedText}
                </button>
              ` : `
                <a 
                  href="/shop" 
                  class="w-full inline-block text-center rounded-full bg-[#8B6A4C] hover:bg-[#72553B] text-[#F5F4DA] font-baloo font-bold text-xs py-2 px-3 transition-colors shadow-xs"
                >
                  🛒 ${buySeedsText}
                </a>
              `}
            </div>
          </div>
        `;
      }

      // 3. VISUAL READY TO HARVEST PLANT CARD
      const now = new Date();
      const readyAt = planting.ready_at ? new Date(planting.ready_at) : now;
      const isReady = planting.status === 'ready' || now >= readyAt;
      const seedCode = planting.seed_code || 'seed_sunflower';
      const seedInfo = this.getSeedDetails(seedCode);
      const speciesName = planting.plant_species ? planting.plant_species.common_name : seedInfo.name;
      const plotLabel = (t.soil_plot || 'Lahan #:number').replace(':number', plot.slot_number);
      const readyBadge = t.ready_to_harvest_badge || 'SIAP PANEN';

      if (isReady) {
        return `
          <div class="card-gg p-4 flex flex-col justify-between items-center text-center bg-[#FBFAF0] border-2 border-[#1F3D20] shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between w-full mb-1">
              <span class="text-[10px] font-baloo font-extrabold text-[#1F3D20]">${plotLabel}</span>
              <span class="px-2 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[9px] font-baloo font-extrabold shadow-xs animate-pulse">
                🌾 ${readyBadge}
              </span>
            </div>

            <!-- Soil Bed & Blooming Plant Visual -->
            <div class="w-full rounded-2xl plot-soil-bed p-2 flex flex-col items-center justify-center text-center my-1 min-h-[140px] relative border-2 border-[#FFD700]/60 shadow-lg">
              ${this.renderPlantVisual(planting, 100, seedInfo)}
              <h4 class="font-baloo font-extrabold text-xs text-[#F5F4DA] drop-shadow-md bg-[#2B1B10]/80 px-2 py-0.5 rounded-md mt-1">
                ${speciesName}
              </h4>
            </div>

            <div class="w-full pt-2 border-t border-[#1F3D20]/10">
              <button 
                data-planting-id="${planting.id}"
                class="harvest-btn w-full btn-gg-primary text-xs py-2 px-3 cursor-pointer flex items-center justify-center gap-1.5 shadow-md"
              >
                <span>🌾</span> ${t.harvest || 'Panen'} (+${seedInfo.exp} EXP, ${typeof window.getNcIconSvg === 'function' ? window.getNcIconSvg('w-3.5 h-3.5') : '🪙'} +${seedInfo.coin} NC)
              </button>
            </div>
          </div>
        `;
      }

      // 4. VISUAL GROWING PLANT CARD
      const plantedAt = planting.planted_at ? new Date(planting.planted_at) : now;
      const totalDurationMs = Math.max(1, readyAt.getTime() - plantedAt.getTime());
      const elapsedMs = Math.max(0, now.getTime() - plantedAt.getTime());
      const progressPercent = Math.min(99, Math.max(5, Math.floor((elapsedMs / totalDurationMs) * 100)));
      const remainingSeconds = Math.max(0, Math.ceil((readyAt.getTime() - now.getTime()) / 1000));
      const waitingHarvestText = t.waiting_harvest || 'Menunggu Panen:';

      return `
        <div class="card-gg p-4 flex flex-col justify-between items-center text-center bg-[#FBFAF0] border-2 border-[#8B6A4C] shadow-lg" data-plot-slot="${plot.slot_number}">
          <div class="flex items-center justify-between w-full mb-1">
            <span class="text-[10px] font-baloo font-bold text-[#1F3D20]">${plotLabel}</span>
            <span class="growth-time-pill px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[9px] font-baloo font-bold">
              ⏳ ${this.formatTimeRemaining(remainingSeconds)}
            </span>
          </div>

          <!-- Soil Bed & Growing Plant Visual Stage -->
          <div class="w-full rounded-2xl plot-soil-bed p-2 flex flex-col items-center justify-center text-center my-1 min-h-[140px] relative">
            ${this.renderPlantVisual(planting, progressPercent, seedInfo)}
            <h4 class="font-baloo font-bold text-xs text-[#F5F4DA] drop-shadow-md bg-[#2B1B10]/80 px-2 py-0.5 rounded-md mt-1">
              ${speciesName}
            </h4>
          </div>

          <!-- Growth Progress Bar & Countdown Label -->
          <div class="w-full space-y-1 my-1">
            <div class="flex justify-between items-center text-[10px] font-baloo font-bold text-[#1F3D20] px-0.5">
              <span>⏳ ${waitingHarvestText}</span>
              <span class="growth-countdown text-[#8B5A2B] font-extrabold">${this.formatTimeRemaining(remainingSeconds)}</span>
            </div>
            <div class="w-full bg-[#E2E1C4] rounded-full h-3 overflow-hidden border border-[#1F3D20]/10 p-0.5">
              <div class="growth-bar-fill bg-gradient-to-r from-[#1F3D20] to-[#27AE60] h-full rounded-full transition-[width] duration-1000 ease-linear" style="width: ${progressPercent}%;"></div>
            </div>
          </div>

          <!-- Growth Action Buttons -->
          <div class="w-full pt-2 border-t border-[#1F3D20]/10 flex flex-col gap-1.5">
            ${(() => {
              const wateringCanItem = this.tools.find(t => t.item_code === 'tool_watering_can' && t.quantity > 0);
              const waterQty = wateringCanItem ? wateringCanItem.quantity : 0;
              const fertilizerItem = this.tools.find(t => t.item_code === 'tool_fertilizer' && t.quantity > 0);
              const fertQty = fertilizerItem ? fertilizerItem.quantity : 0;

              if (waterQty === 0 && fertQty === 0) {
                const naturalGrowthText = t.natural_growth || 'Tumbuh Secara Alami';
                const buyToolsText = t.buy_tools_speedup || 'Beli Alat di Shop untuk Mempercepat';
                return `
                  <div class="w-full text-center py-1 bg-[#E2E1C4]/40 rounded-xl p-2 border border-[#1F3D20]/10">
                    <span class="text-[10px] font-baloo font-bold text-[#6B6B55] block">🌱 ${naturalGrowthText}</span>
                    <a href="/shop" class="text-[10px] font-baloo font-extrabold text-[#8B6A4C] hover:underline flex items-center justify-center gap-1 mt-0.5">
                      <span>🛒</span> ${buyToolsText}
                    </a>
                  </div>
                `;
              }

              const waterText = (t.water_auto || 'Siram Otomatis (-10m) [x:qty]').replace(':qty', waterQty);
              const fertText = (t.fertilize_organic || 'Pupuk Organik (-5m) [x:qty]').replace(':qty', fertQty);

              return `
                ${waterQty > 0 ? `
                  <button 
                    data-planting-id="${planting.id}"
                    class="water-btn w-full rounded-full bg-[#E2E1C4] hover:bg-[#1F3D20] text-[#1F3D20] hover:text-[#F5F4DA] font-baloo font-bold text-xs py-1.5 px-3 transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                  >
                    <span>💧</span> ${waterText}
                  </button>
                ` : ''}
                ${fertQty > 0 ? `
                  <button 
                    data-planting-id="${planting.id}"
                    class="fertilize-btn w-full rounded-full bg-[#27AE60] hover:bg-[#1E8449] text-white font-baloo font-bold text-xs py-1.5 px-3 transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-sm"
                  >
                    <span>🧪</span> ${fertText}
                  </button>
                ` : ''}
              `;
            })()}
          </div>
        </div>
      `;
    }).join('');

    const headerTitle = t.garden_field_header || 'LAHAN KEBUN VIRTUAL GUARDIAN';
    const headerSubtitle = t.garden_field_subtitle || '4 Petak Tanah Gembur';

    this.containerElement.innerHTML = `
      <div class="wooden-fence-header rounded-2xl px-5 py-3 flex items-center justify-between shadow-md mb-6 border-2 border-[#5C3A24]">
        <div class="flex items-center gap-2">
          <span class="text-xl">🌾</span>
          <h3 class="font-baloo font-extrabold text-base sm:text-lg text-[#F5F4DA] tracking-wide">${headerTitle}</h3>
        </div>
        <div class="flex items-center gap-2 text-xs font-baloo font-bold text-[#E7E6BE]">
          <span>${headerSubtitle}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        ${plotsHtml}
      </div>
    `;

    this.attachEventListeners();
  }

  attachEventListeners() {
    if (!this.containerElement) return;

    this.containerElement.querySelectorAll('.unlock-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const plotId = btn.dataset.plotId;
        const cost = parseInt(btn.dataset.cost || '50');
        await this.unlockPlot(plotId, cost);
      });
    });

    this.containerElement.querySelectorAll('.open-seed-modal-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const plotId = btn.dataset.plotId;
        this.openSeedSelectorModal(plotId);
      });
    });

    this.containerElement.querySelectorAll('.water-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const plantingId = btn.dataset.plantingId;
        await this.waterPlant(plantingId);
      });
    });

    this.containerElement.querySelectorAll('.fertilize-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const plantingId = btn.dataset.plantingId;
        await this.applyFertilizer(plantingId);
      });
    });

    this.containerElement.querySelectorAll('.harvest-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const plantingId = btn.dataset.plantingId;
        await this.harvestPlant(plantingId);
      });
    });
  }

  openSeedSelectorModal(plotId) {
    this.selectedPlotForPlanting = plotId;

    let modal = document.querySelector('#seed-selector-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'seed-selector-modal';
      modal.className = 'fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4';
      document.body.appendChild(modal);
    }

    const availableSeeds = this.seeds.filter(s => s.quantity > 0);

    const t = window.translations || {};
    const modalTitle = t.select_seed_title || 'Pilih Benih untuk Ditanam';
    const plantBtnText = t.plant_btn || 'Tanam';
    const buyMoreText = t.buy_more_seeds || 'Beli Benih Lain di Shop';

    modal.innerHTML = `
      <div class="card-gg max-w-md w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0]">
        <div class="flex justify-between items-center border-b border-[#1F3D20]/10 pb-3">
            <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20]">${modalTitle}</h3>
            <button id="close-seed-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
        </div>

        <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
          ${availableSeeds.map(s => {
            const seedInfo = this.getSeedDetails(s.item_code);
            const stockText = (t.stock_label || 'Stok: x:qty').replace(':qty', s.quantity);
            return `
              <div class="p-3 rounded-2xl bg-[#F5F4DA] border border-[#1F3D20]/10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <span class="text-2xl p-2 rounded-xl bg-[#E2E1C4]">${seedInfo.icon}</span>
                  <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">${seedInfo.name}</h4>
                    <div class="flex items-center gap-2 text-[10px] text-[#4A5D4E] font-extrabold my-0.5">
                      <span>⏱️ ${seedInfo.duration}m</span>
                      <span>✨ +${seedInfo.exp} EXP</span>
                      <span class="flex items-center gap-0.5">${typeof window.getNcIconSvg === 'function' ? window.getNcIconSvg('w-3.5 h-3.5') : '🪙'} +${seedInfo.coin} NC</span>
                    </div>
                    <span class="text-[10px] text-[#6B6B55]">${stockText}</span>
                  </div>
                </div>
                <button 
                  data-seed-code="${s.item_code}"
                  class="select-seed-btn btn-gg-primary text-xs py-1.5 px-3 cursor-pointer"
                >
                  ${plantBtnText}
                </button>
              </div>
            `;
          }).join('')}
        </div>

        <div class="pt-2 text-center border-t border-[#1F3D20]/10">
          <a href="/shop" class="text-xs font-baloo font-bold text-[#8B6A4C] hover:underline">
            🛒 ${buyMoreText}
          </a>
        </div>
      </div>
    `;

    modal.classList.remove('hidden');

    modal.querySelector('#close-seed-modal-btn').addEventListener('click', () => {
      modal.classList.add('hidden');
    });

    modal.querySelectorAll('.select-seed-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const seedCode = btn.dataset.seedCode;
        modal.classList.add('hidden');
        await this.plantSeed(this.selectedPlotForPlanting, seedCode);
      });
    });
  }

  async unlockPlot(plotId, cost) {
    try {
      const res = await apiClient.post(`/minigame/plots/${plotId}/unlock`);
      this.showToast(res.message || 'Lahan tanam berhasil dibuka!', 'success');
      await this.loadPlots();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Gagal membuka lahan.';
      this.showToast(msg, 'error');
    }
  }

  async plantSeed(gardenPlotId, seedCode) {
    try {
      const res = await apiClient.post('/minigame/plant', {
        garden_plot_id: gardenPlotId,
        seed_code: seedCode,
      });
      const seedInfo = this.getSeedDetails(seedCode);
      this.showToast(`Benih ${seedInfo.name} berhasil ditanam di tanah gembur!`, 'success');
      await this.loadPlots();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Gagal menanam benih.';
      this.showToast(msg, 'error');
    }
  }

  async waterPlant(plantingId) {
    try {
      const res = await apiClient.post('/minigame/water', {
        planting_id: plantingId,
      });
      this.showToast(res.message || 'Tanaman disiram! Pertumbuhan dipercepat 💧', 'success');
      await this.loadPlots();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Gagal menyiram tanaman.';
      this.showToast(msg, 'error');
    }
  }

  async applyFertilizer(plantingId) {
    try {
      const res = await apiClient.post('/minigame/fertilize', {
        planting_id: plantingId,
      });
      this.showToast(res.message || 'Pupuk Organik Super digunakan! Pertumbuhan dipotong 15 menit 🧪', 'success');
      await this.loadPlots();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Gagal menggunakan pupuk.';
      this.showToast(msg, 'error');
    }
  }

  async harvestPlant(plantingId) {
    try {
      const res = await apiClient.post('/minigame/harvest', {
        planting_id: plantingId,
      });
      const expEarned = res.data?.exp_earned || 50;
      const coinEarned = res.data?.coin_earned || 20;
      this.showToast(`🎉 Panen Berhasil! +${expEarned} EXP & ${typeof window.getNcIconSvg === 'function' ? window.getNcIconSvg('w-3.5 h-3.5') : '🪙'} +${coinEarned} NC!`, 'success');
      await this.loadPlots();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Gagal memanen tanaman.';
      this.showToast(msg, 'error');
    }
  }
}

export default MiniGameModule;
