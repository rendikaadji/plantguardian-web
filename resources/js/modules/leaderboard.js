/**
 * Leaderboard Frontend Module (Garden Guardian Design System)
 * Rujuk docs/design.md §3.8 & docs/architecture.md §4.6
 */

import apiClient from '../api-client.js';

export class LeaderboardManager {
  constructor() {
    this.currentListContainer = document.querySelector('#leaderboard-current-list');
    this.podiumContainer = document.querySelector('#leaderboard-podium');
    this.userStatusContainer = document.querySelector('#leaderboard-user-status');
    this.historyContainer = document.querySelector('#leaderboard-history-list');

    this.tabCurrentBtn = document.querySelector('#tab-btn-current');
    this.tabHistoryBtn = document.querySelector('#tab-btn-history');
    this.tabCurrentContent = document.querySelector('#tab-content-current');
    this.tabHistoryContent = document.querySelector('#tab-content-history');
  }

  /**
   * Initialize module events & fetch rankings
   */
  init(currentUserId) {
    this.currentUserId = currentUserId;
    this.bindTabs();
    this.loadCurrentLeaderboard();
  }

  bindTabs() {
    if (!this.tabCurrentBtn || !this.tabHistoryBtn) return;

    this.tabCurrentBtn.addEventListener('click', () => {
      this.setActiveTab('current');
    });

    this.tabHistoryBtn.addEventListener('click', () => {
      this.setActiveTab('history');
      this.loadLeaderboardHistory();
    });
  }

  setActiveTab(tabName) {
    if (tabName === 'current') {
      this.tabCurrentBtn.classList.add('bg-[#1F3D20]', 'text-[#F5F4DA]');
      this.tabCurrentBtn.classList.remove('bg-transparent', 'text-[#6B6B55]');

      this.tabHistoryBtn.classList.remove('bg-[#1F3D20]', 'text-[#F5F4DA]');
      this.tabHistoryBtn.classList.add('bg-transparent', 'text-[#6B6B55]');

      if (this.tabCurrentContent) this.tabCurrentContent.classList.remove('hidden');
      if (this.tabHistoryContent) this.tabHistoryContent.classList.add('hidden');
    } else {
      this.tabHistoryBtn.classList.add('bg-[#1F3D20]', 'text-[#F5F4DA]');
      this.tabHistoryBtn.classList.remove('bg-transparent', 'text-[#6B6B55]');

      this.tabCurrentBtn.classList.remove('bg-[#1F3D20]', 'text-[#F5F4DA]');
      this.tabCurrentBtn.classList.add('bg-transparent', 'text-[#6B6B55]');

      if (this.tabHistoryContent) this.tabHistoryContent.classList.remove('hidden');
      if (this.tabCurrentContent) this.tabCurrentContent.classList.add('hidden');
    }
  }

  /**
   * Fetch current ongoing weekly real-time rankings
   */
  async getCurrentLeaderboard() {
    return await apiClient.get('/leaderboard/current');
  }

  /**
   * Fetch historical weekly leaderboard snapshots
   */
  async getLeaderboardHistory() {
    return await apiClient.get('/leaderboard/history');
  }

  /**
   * Load and render current leaderboard
   */
  async loadCurrentLeaderboard() {
    try {
      const response = await this.getCurrentLeaderboard();
      const rankings = (response && response.data) ? response.data : [];

      this.renderPodium(rankings.slice(0, 3));
      this.renderList(rankings);
      this.renderUserStatus(rankings);
    } catch (error) {
      console.warn('Gagal memuat leaderboard saat ini:', error.message);
      if (this.currentListContainer) {
        this.currentListContainer.innerHTML = `
          <div class="p-6 text-center text-xs font-nunito text-[#6B6B55]">
            Gagal memuat papan peringkat. Silakan coba beberapa saat lagi.
          </div>
        `;
      }
    }
  }

  /**
   * Render Top 3 Podium Cards
   */
  renderPodium(topThree) {
    if (!this.podiumContainer) return;

    if (topThree.length === 0) {
      this.podiumContainer.innerHTML = '';
      return;
    }

    const first = topThree[0] || null;
    const second = topThree[1] || null;
    const third = topThree[2] || null;

    let html = '<div class="grid grid-cols-3 gap-2 sm:gap-4 items-end pt-4 pb-2 max-w-xl mx-auto">';

    // 2nd Place (Left)
    if (second) {
      html += `
        <div class="flex flex-col items-center card-gg p-3 sm:p-4 border-2 border-[#C0C0C0]/50 bg-[#FBFAF0]">
          <div class="relative mb-1 sm:mb-2">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-[#E2E1C4] border-2 border-[#C0C0C0] flex items-center justify-center font-baloo font-extrabold text-base sm:text-xl text-[#1F3D20] shadow-sm">
              ${second.user_name.substring(0, 2).toUpperCase()}
            </div>
            <span class="absolute -top-2 -right-1 text-lg">🥈</span>
          </div>
          <span class="font-baloo font-bold text-xs sm:text-sm text-[#2D4A2E] truncate max-w-full">${second.user_name}</span>
          <span class="text-[10px] font-baloo font-extrabold text-[#6B6B55] mt-0.5">${second.exp_earned} EXP</span>
          <span class="mt-2 text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full bg-[#C0C0C0]/20 text-[#2D4A2E]">JUARA 2</span>
        </div>
      `;
    } else {
      html += `<div></div>`;
    }

    // 1st Place (Center - Taller)
    if (first) {
      html += `
        <div class="flex flex-col items-center card-gg p-3 sm:p-5 border-2 border-[#FFD700] bg-[#FFFDF0] transform -translate-y-2 shadow-md">
          <div class="relative mb-1 sm:mb-2">
            <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full bg-[#FFD700]/30 border-3 border-[#FFD700] flex items-center justify-center font-baloo font-extrabold text-lg sm:text-2xl text-[#1F3D20] shadow-md">
              ${first.user_name.substring(0, 2).toUpperCase()}
            </div>
            <span class="absolute -top-3 -right-1 text-2xl animate-bounce">👑</span>
          </div>
          <span class="font-baloo font-extrabold text-xs sm:text-base text-[#1F3D20] truncate max-w-full">${first.user_name}</span>
          <span class="text-xs font-baloo font-extrabold text-[#D96C63] mt-0.5">${first.exp_earned} EXP</span>
          <span class="mt-2 text-[10px] font-baloo font-extrabold px-2.5 py-0.5 rounded-full bg-[#FFD700] text-[#1F3D20] shadow-xs">🥇 JUARA 1</span>
        </div>
      `;
    } else {
      html += `<div></div>`;
    }

    // 3rd Place (Right)
    if (third) {
      html += `
        <div class="flex flex-col items-center card-gg p-3 sm:p-4 border-2 border-[#CD7F32]/40 bg-[#FBFAF0]">
          <div class="relative mb-1 sm:mb-2">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-[#E2E1C4] border-2 border-[#CD7F32] flex items-center justify-center font-baloo font-extrabold text-base sm:text-xl text-[#1F3D20] shadow-sm">
              ${third.user_name.substring(0, 2).toUpperCase()}
            </div>
            <span class="absolute -top-2 -right-1 text-lg">🥉</span>
          </div>
          <span class="font-baloo font-bold text-xs sm:text-sm text-[#2D4A2E] truncate max-w-full">${third.user_name}</span>
          <span class="text-[10px] font-baloo font-extrabold text-[#6B6B55] mt-0.5">${third.exp_earned} EXP</span>
          <span class="mt-2 text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full bg-[#CD7F32]/20 text-[#2D4A2E]">JUARA 3</span>
        </div>
      `;
    } else {
      html += `<div></div>`;
    }

    html += '</div>';
    this.podiumContainer.innerHTML = html;
  }

  /**
   * Render Leaderboard List (Rankings 1 to N)
   */
  renderList(rankings) {
    if (!this.currentListContainer) return;

    if (rankings.length === 0) {
      this.currentListContainer.innerHTML = `
        <div class="p-8 text-center space-y-2">
          <span class="text-3xl">🌱</span>
          <p class="font-baloo font-bold text-sm text-[#2D4A2E]">Belum Ada Perolehan EXP Minggu Ini</p>
          <p class="text-xs text-[#6B6B55]">Jadilah yang pertama menemukan tumbuhan di Peta untuk meraih posisi puncak!</p>
        </div>
      `;
      return;
    }

    let html = '<div class="space-y-2 sm:space-y-3">';

    rankings.forEach((item) => {
      const isSelf = this.currentUserId && (item.user_id === parseInt(this.currentUserId));
      
      let rankBadge = `<span class="w-7 h-7 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-xs shrink-0">${item.rank}</span>`;
      if (item.rank === 1) rankBadge = `<span class="w-7 h-7 rounded-full bg-[#FFD700] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-xs shrink-0 shadow-xs">🥇</span>`;
      if (item.rank === 2) rankBadge = `<span class="w-7 h-7 rounded-full bg-[#C0C0C0] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-xs shrink-0 shadow-xs">🥈</span>`;
      if (item.rank === 3) rankBadge = `<span class="w-7 h-7 rounded-full bg-[#CD7F32] text-white flex items-center justify-center font-baloo font-extrabold text-xs shrink-0 shadow-xs">🥉</span>`;

      html += `
        <div class="card-gg p-3.5 sm:p-4 flex items-center justify-between transition-all ${
          isSelf 
            ? 'bg-[#1F3D20] text-[#F5F4DA] border-2 border-[#E2E1C4]/40 shadow-md ring-2 ring-[#1F3D20]/20' 
            : 'bg-[#FBFAF0] text-[#2D4A2E] hover:bg-[#F5F4DA]'
        }">
          <div class="flex items-center gap-3 min-w-0">
            ${rankBadge}
            <div class="w-9 h-9 rounded-full ${isSelf ? 'bg-[#F5F4DA] text-[#1F3D20]' : 'bg-[#1F3D20] text-[#F5F4DA]'} flex items-center justify-center font-baloo font-extrabold text-xs shrink-0">
              ${item.user_name.substring(0, 2).toUpperCase()}
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <span class="font-baloo font-bold text-sm truncate ${isSelf ? 'text-[#F5F4DA]' : 'text-[#2D4A2E]'}">
                  ${item.user_name}
                </span>
                ${isSelf ? '<span class="px-2 py-0.2 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[9px] font-baloo font-extrabold">KAMU</span>' : ''}
              </div>
              <span class="text-[10px] font-nunito ${isSelf ? 'text-[#F5F4DA]/80' : 'text-[#6B6B55]'} capitalize">
                ${item.user_role} Guardian
              </span>
            </div>
          </div>

          <div class="text-right shrink-0">
            <span class="font-baloo font-extrabold text-sm sm:text-base ${isSelf ? 'text-[#FFD700]' : 'text-[#1F3D20]'}">
              +${item.exp_earned} EXP
            </span>
          </div>
        </div>
      `;
    });

    html += '</div>';
    this.currentListContainer.innerHTML = html;
  }

  /**
   * Render User's Own Status Card
   */
  renderUserStatus(rankings) {
    if (!this.userStatusContainer || !this.currentUserId) return;

    const userRank = rankings.find(r => r.user_id === parseInt(this.currentUserId));

    if (userRank) {
      this.userStatusContainer.innerHTML = `
        <div class="card-gg p-4 bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-between shadow-md border border-[#E2E1C4]/20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#F5F4DA] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-base">
              #${userRank.rank}
            </div>
            <div>
              <span class="text-[10px] font-baloo font-bold text-[#E2E1C4] uppercase tracking-wider">POSISI SAAT INI</span>
              <h4 class="font-baloo font-bold text-base text-[#F5F4DA] leading-none">${userRank.user_name}</h4>
            </div>
          </div>
          <div class="text-right">
            <span class="font-baloo font-extrabold text-lg text-[#FFD700]">+${userRank.exp_earned} EXP</span>
            <p class="text-[10px] text-[#E2E1C4] font-nunito">Minggu ini</p>
          </div>
        </div>
      `;
    } else {
      this.userStatusContainer.innerHTML = `
        <div class="card-gg p-4 bg-[#FBFAF0] flex items-center justify-between border border-[#1F3D20]/10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#E2E1C4] text-[#6B6B55] flex items-center justify-center font-baloo font-extrabold text-sm">
              -
            </div>
            <div>
              <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">POSISI SAAT INI</span>
              <h4 class="font-baloo font-bold text-sm text-[#2D4A2E] leading-none">Belum Masuk Peringkat Minggu Ini</h4>
            </div>
          </div>
          <span class="text-xs font-baloo font-bold text-[#1F3D20]">Dapatkan EXP sekarang &rarr;</span>
        </div>
      `;
    }
  }

  /**
   * Load and render historical leaderboard snapshots
   */
  async loadLeaderboardHistory() {
    if (!this.historyContainer) return;

    this.historyContainer.innerHTML = `
      <div class="p-6 text-center text-xs font-nunito text-[#6B6B55]">
        Memuat riwayat juara mingguan...
      </div>
    `;

    try {
      const response = await this.getLeaderboardHistory();
      const history = (response && response.data) ? response.data : [];

      if (history.length === 0) {
        this.historyContainer.innerHTML = `
          <div class="card-gg p-8 text-center space-y-2">
            <span class="text-3xl">🏆</span>
            <p class="font-baloo font-bold text-sm text-[#2D4A2E]">Belum Ada Riwayat Snapshot Juara</p>
            <p class="text-xs text-[#6B6B55] leading-relaxed">
              Snapshot leaderboard dihitung secara otomatis oleh sistem setiap akhir minggu (Senin 00:00).
            </p>
          </div>
        `;
        return;
      }

      let html = '<div class="space-y-3">';
      history.forEach((item) => {
        html += `
          <div class="card-gg p-4 bg-[#FBFAF0] space-y-2 border border-[#1F3D20]/10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[10px] font-baloo font-extrabold">
                  Peringkat #${item.rank}
                </span>
                <span class="text-xs font-baloo font-bold text-[#6B6B55]">
                  Minggu: ${item.week_start_date} s/d ${item.week_end_date}
                </span>
              </div>
              <span class="font-baloo font-extrabold text-sm text-[#1F3D20]">
                ${item.exp_earned} EXP
              </span>
            </div>
            ${item.reward_description ? `
              <p class="text-xs font-nunito text-[#2D4A2E] bg-[#E2E1C4]/40 p-2 rounded-lg font-semibold flex items-center gap-1.5">
                <span>🏅</span> ${item.reward_description}
              </p>
            ` : ''}
          </div>
        `;
      });
      html += '</div>';

      this.historyContainer.innerHTML = html;
    } catch (error) {
      console.warn('Gagal memuat riwayat leaderboard:', error.message);
      this.historyContainer.innerHTML = `
        <div class="p-6 text-center text-xs font-nunito text-red-600">
          Gagal memuat riwayat peringkat.
        </div>
      `;
    }
  }
}

export default new LeaderboardManager();
