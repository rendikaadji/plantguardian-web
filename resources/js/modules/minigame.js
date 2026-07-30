/**
 * MiniGame Module (Kebun Virtual & Interaksi Lahan)
 * Rujuk docs/architecture.md §2.2 & docs/design.md §3.4
 */

import apiClient from '../api-client.js';

export class MiniGameModule {
  constructor(options = {}) {
    this.containerElement = options.containerElement || document.querySelector('#garden-plots-container');
    this.userCoins = 0;
    this.userExp = 0;
    this.plots = [];
  }

  /**
   * Initialize garden plots data
   */
  async init() {
    await this.loadPlots();
  }

  /**
   * Fetch plots from backend GET /api/minigame/plots
   */
  async loadPlots() {
    try {
      const response = await apiClient.get('/minigame/plots');
      this.plots = response.data || [];
      this.render();
    } catch (error) {
      console.error('Gagal memuat lahan kebun:', error);
    }
  }

  /**
   * Render grid of garden plots
   */
  render() {
    if (!this.containerElement) return;

    const plotsHtml = this.plots.map(plot => {
      const isUnlocked = plot.unlocked;
      const planting = plot.current_planting;

      if (!isUnlocked) {
        return `
          <div class="plot-card custom-card rounded-3xl p-6 flex flex-col items-center justify-center text-center border-amber-900/30">
            <div class="w-14 h-14 rounded-2xl bg-amber-950/60 border border-amber-800/40 flex items-center justify-center text-amber-400 mb-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h4 class="font-extrabold text-white text-sm mb-1">Slot Lahan #${plot.slot_number}</h4>
            <p class="text-xs text-amber-400 font-semibold mb-4">Biaya Buka: ${plot.purchase_cost || 100} Coin</p>
            <button class="unlock-btn w-full bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs py-3 rounded-xl shadow-md transition-all" data-plot-id="${plot.id}">
              Buka Slot Lahan
            </button>
          </div>
        `;
      }

      if (!planting) {
        return `
          <div class="plot-card custom-card custom-card-hover rounded-3xl p-6 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-950 border border-emerald-800/40 flex items-center justify-center text-emerald-400 mb-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>
            <h4 class="font-extrabold text-white text-sm mb-1">Lahan #${plot.slot_number}</h4>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-950 px-2.5 py-1 rounded-md border border-emerald-800/40 mb-4">Lahan Terbuka (Kosong)</span>
            <button class="plant-btn w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs py-3 rounded-xl shadow-md transition-all" data-plot-id="${plot.id}">
              Tanam Benih Spesies
            </button>
          </div>
        `;
      }

      const isReady = planting.status === 'ready' || (planting.ready_at && new Date() >= new Date(planting.ready_at));
      const speciesName = planting.plant_species ? planting.plant_species.common_name : 'Tanaman Konservasi';

      if (isReady) {
        return `
          <div class="plot-card custom-card rounded-3xl p-6 flex flex-col items-center justify-center text-center border-amber-500/50 bg-[#1A1A12]">
            <div class="w-14 h-14 rounded-2xl bg-amber-950 border border-amber-800/40 flex items-center justify-center text-amber-400 mb-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <h4 class="font-extrabold text-white text-sm mb-1">${speciesName}</h4>
            <span class="inline-block bg-amber-500/20 border border-amber-500/40 text-amber-300 font-extrabold text-[10px] uppercase tracking-wider px-3 py-1 rounded-md mb-4">Siap Dipanen!</span>
            <button class="harvest-btn w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs py-3 rounded-xl shadow-lg transition-all" data-planting-id="${planting.id}">
              Panen & Ambil Reward
            </button>
          </div>
        `;
      }

      return `
        <div class="plot-card custom-card custom-card-hover rounded-3xl p-6 flex flex-col items-center justify-center text-center border-emerald-800/40">
          <div class="w-14 h-14 rounded-2xl bg-emerald-950 border border-emerald-800/40 flex items-center justify-center text-emerald-400 mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <h4 class="font-extrabold text-white text-sm mb-1">${speciesName}</h4>
          <span class="inline-block bg-emerald-950 text-emerald-400 font-semibold text-[10px] uppercase tracking-wider px-3 py-1 rounded-md mb-4 border border-emerald-800/40">Sedang Tumbuh...</span>
          <button class="water-btn w-full bg-[#1B2C27] hover:bg-[#233832] text-emerald-400 border border-emerald-800/40 font-semibold text-xs py-3 rounded-xl transition-all" data-planting-id="${planting.id}">
            Siram (Bonus Tumbuh)
          </button>
        </div>
      `;
    }).join('');

    this.containerElement.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">${plotsHtml}</div>`;
    this.attachEventListeners();
  }

  /**
   * Event listeners for plot buttons
   */
  attachEventListeners() {
    this.containerElement.querySelectorAll('.unlock-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const plotId = btn.getAttribute('data-plot-id');
        await this.unlockPlot(plotId);
      });
    });

    this.containerElement.querySelectorAll('.plant-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const plotId = btn.getAttribute('data-plot-id');
        await this.plantSeed(plotId);
      });
    });

    this.containerElement.querySelectorAll('.water-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const plantingId = btn.getAttribute('data-planting-id');
        await this.waterPlant(plantingId);
      });
    });

    this.containerElement.querySelectorAll('.harvest-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const plantingId = btn.getAttribute('data-planting-id');
        await this.harvestPlant(plantingId);
      });
    });
  }

  /**
   * Unlock plot
   */
  async unlockPlot(plotId) {
    try {
      const res = await apiClient.post(`/minigame/plots/${plotId}/unlock`);
      alert(res.message || 'Lahan berhasil dibuka!');
      await this.loadPlots();
    } catch (err) {
      alert(err.message || 'Gagal membuka lahan.');
    }
  }

  /**
   * Plant seed
   */
  async plantSeed(gardenPlotId, seedCode = 'SEED_DEFAULT') {
    try {
      const res = await apiClient.post('/minigame/plant', {
        garden_plot_id: gardenPlotId,
        seed_code: seedCode,
      });
      alert(res.message || 'Benih berhasil ditanam!');
      await this.loadPlots();
    } catch (err) {
      alert(err.message || 'Gagal menanam benih.');
    }
  }

  /**
   * Water plant
   */
  async waterPlant(plantingId) {
    try {
      const res = await apiClient.post('/minigame/water', {
        planting_id: plantingId,
      });
      alert(res.message || 'Tanaman berhasil disiram!');
      await this.loadPlots();
    } catch (err) {
      alert(err.message || 'Gagal menyiram tanaman.');
    }
  }

  /**
   * Harvest plant
   */
  async harvestPlant(plantingId) {
    try {
      const res = await apiClient.post('/minigame/harvest', {
        planting_id: plantingId,
      });
      const expEarned = res.data?.exp_earned || 50;
      const coinEarned = res.data?.coin_earned || 20;
      alert(`🎉 Panen Berhasil! Anda mendapatkan +${expEarned} EXP dan +${coinEarned} Coin!`);
      await this.loadPlots();
    } catch (err) {
      alert(err.message || 'Gagal memanen tanaman.');
    }
  }
}

export default MiniGameModule;
