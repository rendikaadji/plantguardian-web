import apiClient from './api-client.js';
import ArScanner from './modules/ar-scanner.js';
import GalleryModule from './modules/gallery.js';
import MiniGameModule from './modules/minigame.js';
import HomeModule from './modules/home.js';
import ThreeDCardTilt from './modules/card-tilt.js';
import LeaderboardManager from './modules/leaderboard.js';
import MapManager from './modules/map.js';

import { ShopModule } from './modules/shop.js';
import FriendsModule from './modules/friends.js';

window.apiClient = apiClient;
window.ArScanner = ArScanner;
window.GalleryModule = GalleryModule;
window.MiniGameModule = MiniGameModule;
window.HomeModule = HomeModule;
window.ThreeDCardTilt = ThreeDCardTilt;
window.LeaderboardManager = LeaderboardManager;
window.MapManager = MapManager;
window.ShopModule = ShopModule;
window.FriendsModule = FriendsModule;

/**
 * Global Non-Overlapping Toast Notification Manager
 */
window.showToast = function(message, type = 'success') {
  let container = document.querySelector('#toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-20 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-auto pointer-events-none';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `pointer-events-auto px-4 py-3 rounded-2xl shadow-xl font-baloo font-bold text-sm transition-all duration-300 transform translate-y-2 opacity-0 flex items-center gap-2 border ${
    type === 'success' 
      ? 'bg-[#1F3D20] text-[#F5F4DA] border-[#E2E1C4]/30' 
      : 'bg-[#C0392B] text-white border-red-400/30'
  }`;
  
  toast.innerHTML = `
    <span class="text-base">${type === 'success' ? '✨' : '⚠️'}</span>
    <span class="leading-tight">${message}</span>
  `;

  container.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.remove('translate-y-2', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
  });

  setTimeout(() => {
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.classList.add('opacity-0', '-translate-y-2');
    setTimeout(() => {
      toast.remove();
      if (container.children.length === 0) {
        container.remove();
      }
    }, 300);
  }, 3500);
};

/**
 * Global User Balance Display Updater (Synchronizes Top Nav Header & Shop Card)
 */
window.updateUserCoin = function(coin) {
  const elements = document.querySelectorAll('#user-coin, #shop-user-coin');
  elements.forEach(el => {
    el.textContent = coin;
  });
};

window.updateUserExp = function(exp) {
  const elements = document.querySelectorAll('#user-exp, #shop-user-exp');
  elements.forEach(el => {
    el.textContent = exp;
  });
};

/**
 * Global Nature Coin (NC) Vector Icon Generator
 */
window.getNcIconSvg = function(sizeClass = 'w-4 h-4') {
  return `<svg class="${sizeClass} shrink-0 inline-block align-middle" viewBox="0 0 24 24" fill="none">
    <circle cx="12" cy="12" r="10" fill="#F4C430" stroke="#B8860B" stroke-width="1.5"/>
    <circle cx="12" cy="12" r="7.5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
    <path d="M12 6.5c-3 3.5-3.5 7.5-1.2 10.5 3-3.5 3.5-7.5 1.2-10.5z" fill="#1F3D20"/>
    <path d="M12 6.5c3 3.5 3.5 7.5 1.2 10.5-3-3.5-3.5-7.5-1.2-10.5z" fill="#27AE60"/>
  </svg>`;
};


