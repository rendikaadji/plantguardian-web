/**
 * Home Module (Navigation Hub, Wallet Balance & Daily Mission Display)
 * Rujuk docs/architecture.md §2.2, §4.9 & docs/design.md §3.1
 */

import apiClient from '../api-client.js';

export class HomeModule {
  constructor(options = {}) {
    this.expElement = options.expElement || document.querySelector('#user-exp');
    this.coinElement = options.coinElement || document.querySelector('#user-coin');
    
    this.missionCountElement = document.querySelector('#daily-mission-count');
    this.missionProgressBar = document.querySelector('#daily-mission-progress-bar');
    this.missionProgressText = document.querySelector('#daily-mission-progress-text');
    this.missionActionElement = document.querySelector('#daily-mission-action');
  }

  /**
   * Helper getter for current locale translations object
   */
  get t() {
    return window.translations || {};
  }

  /**
   * Fetch current wallet balance (EXP & Coin)
   */
  async loadWalletBalance() {
    try {
      const response = await apiClient.get('/wallet/balance');
      const data = response.data;
      if (data) {
        if (this.expElement) this.expElement.textContent = data.exp || 0;
        if (this.coinElement) this.coinElement.textContent = data.coin || 0;
      }
    } catch (error) {
      // Handle unauthenticated guest access quietly
      if (error.status === 401) {
        if (this.expElement) this.expElement.textContent = 0;
        if (this.coinElement) this.coinElement.textContent = 0;
        return;
      }
      console.warn('Gagal memuat saldo wallet:', error.message);
    }
  }

  /**
   * Fetch and display Daily Mission status (auto-reset on new calendar day)
   */
  async loadDailyMission() {
    if (!this.missionCountElement) return;

    try {
      const response = await apiClient.get('/daily-mission');
      if (response && response.data) {
        this.renderDailyMission(response.data);
      }
    } catch (error) {
      console.warn('Gagal memuat status misi harian:', error.message);
    }
  }

  /**
   * Render daily mission state to UI
   */
  renderDailyMission(data) {
    const { current_count, target_count, percentage, is_completed, is_claimed, reward } = data;
    const t = this.t;

    if (this.missionCountElement) {
      this.missionCountElement.textContent = `${current_count} / ${target_count}`;
    }

    if (this.missionProgressBar) {
      this.missionProgressBar.style.width = `${percentage}%`;
    }

    if (this.missionProgressText) {
      const progressLabel = t.progress || 'Progress';
      this.missionProgressText.textContent = `${progressLabel}: ${percentage}% • Resets 00:00`;
    }

    if (this.missionActionElement) {
      if (is_claimed) {
        const completedText = t.mission_completed_claimed || 'Misi Harian Selesai & Hadiah Diklaim Hari Ini';
        const autoResetText = t.auto_reset_tomorrow || 'Teriset otomatis besok';
        this.missionActionElement.innerHTML = `
          <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#E2E1C4]/40 border border-[#1F3D20]/10">
            <span class="text-xs font-baloo font-bold text-[#1F3D20] flex items-center gap-1.5">
              <span>✅</span> ${completedText}
            </span>
            <span class="text-[10px] font-baloo font-bold text-[#6B6B55]">${autoResetText}</span>
          </div>
        `;
      } else if (is_completed) {
        let btnText = t.btn_claim_reward || 'Klaim Hadiah Misi (+:exp EXP & 🪙 :coin NC)';
        btnText = btnText.replace(':exp', reward.exp).replace(':coin', reward.coin);
        this.missionActionElement.innerHTML = `
          <button id="btn-claim-daily-mission" class="w-full py-2.5 px-4 rounded-xl bg-[#1F3D20] hover:bg-[#2D4A2E] text-[#F5F4DA] font-baloo font-extrabold text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2 cursor-pointer transform hover:-translate-y-0.5">
            <span>✨</span>
            <span>${btnText}</span>
          </button>
        `;
        const btnClaim = this.missionActionElement.querySelector('#btn-claim-daily-mission');
        if (btnClaim) {
          btnClaim.addEventListener('click', () => this.claimDailyMissionReward());
        }
      } else {
        const remaining = target_count - current_count;
        let hintText = t.remaining_hint || '💡 Temukan :count marker tumbuhan lagi hari ini di Peta untuk mengklaim reward!';
        hintText = hintText.replace(':count', `<span>${remaining}</span>`);
        this.missionActionElement.innerHTML = `
          <div class="text-[11px] font-baloo font-bold text-[#6B6B55] bg-[#E2E1C4]/20 p-2 rounded-lg text-center">
            ${hintText}
          </div>
        `;
      }
    }
  }

  /**
   * Claim reward for completed daily mission
   */
  async claimDailyMissionReward() {
    const btnClaim = this.missionActionElement?.querySelector('#btn-claim-daily-mission');
    const t = this.t;

    if (btnClaim) {
      btnClaim.disabled = true;
      btnClaim.innerText = t.claiming || 'Mengklaim...';
    }

    try {
      const response = await apiClient.post('/daily-mission/claim');
      if (response && response.success) {
        if (window.showToast) {
          window.showToast(`✨ ${response.message}`, 'success');
        }

        if (response.data && response.data.user) {
          if (window.updateUserExp) window.updateUserExp(response.data.user.exp);
          if (window.updateUserCoin) window.updateUserCoin(response.data.user.coin);
        }

        if (response.data && response.data.status) {
          this.renderDailyMission(response.data.status);
        }
      }
    } catch (error) {
      if (window.showToast) {
        window.showToast(error.message || t.claim_failed || 'Gagal mengklaim hadiah misi harian.', 'error');
      }
      if (btnClaim) {
        btnClaim.disabled = false;
        let btnText = t.btn_claim_reward || 'Klaim Hadiah Misi';
        btnText = btnText.replace(':exp', 150).replace(':coin', 50);
        btnClaim.innerText = btnText;
      }
    }
  }
}

export default HomeModule;
