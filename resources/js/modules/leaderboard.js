/**
 * Leaderboard Frontend Module
 * Rujuk docs/design.md §3.8 & docs/architecture.md §4.6
 */

import apiClient from '../api-client.js';

export class LeaderboardManager {
  /**
   * Fetch current ongoing weekly real-time rankings
   */
  async getCurrentLeaderboard() {
    return await apiClient.get('/leaderboard/current');
  }

  /**
   * Fetch historical weekly leaderboard snapshots and rewards
   */
  async getLeaderboardHistory() {
    return await apiClient.get('/leaderboard/history');
  }
}

export default new LeaderboardManager();
