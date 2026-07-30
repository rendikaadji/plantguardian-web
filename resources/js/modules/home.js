/**
 * Home Module (Navigation Hub & Wallet Balance Display)
 * Rujuk docs/architecture.md §2.2 & docs/design.md §3.1
 */

import apiClient from '../api-client.js';

export class HomeModule {
  constructor(options = {}) {
    this.expElement = options.expElement || document.querySelector('#user-exp');
    this.coinElement = options.coinElement || document.querySelector('#user-coin');
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
}

export default HomeModule;
