/**
 * Friends & Alliance Shop Item Transfer Module
 * Plant Guardian Design System
 */

import apiClient from '../api-client.js';

export class FriendsModule {
  constructor() {
    this.friends = [];
    this.incomingRequests = [];
    this.itemRequests = [];
    this.inventory = [];
  }

  async init() {
    await this.loadData();
    this.bindEvents();
  }

  async loadData() {
    try {
      const res = await apiClient.get('/friends');
      if (res.success || res.friends) {
        this.friends = res.friends || [];
        this.incomingRequests = res.incoming_requests || [];
        this.itemRequests = res.item_requests || [];
        this.inventory = res.inventory || [];
        this.renderFriendsList();
        this.renderIncomingRequests();
        this.renderItemRequests();
      }
    } catch (err) {
      console.warn('[FriendsModule] Gagal memuat data aliansi teman:', err);
    }
  }

  bindEvents() {
    // Search input event
    const searchInput = document.querySelector('#friend-search-input');
    if (searchInput) {
      let timeout = null;
      searchInput.addEventListener('input', (e) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => this.searchUsers(e.target.value), 350);
      });
    }
  }

  renderFriendsList() {
    const container = document.querySelector('#friends-list-container');
    if (!container) return;

    if (this.friends.length === 0) {
      container.innerHTML = `
        <div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">
          Belum ada teman dalam Aliansi Anda. Klik "+ Tambah Aliansi" untuk memperluas koneksi!
        </div>
      `;
      return;
    }

    container.innerHTML = this.friends.map(friend => `
      <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-sm flex items-center justify-center shrink-0">
            ${friend.name ? friend.name.substring(0, 2).toUpperCase() : 'US'}
          </div>
          <div>
            <span class="font-baloo font-bold text-sm text-[#1F3D20] block leading-snug">${friend.name}</span>
            <span class="text-[11px] text-[#6B6B55] block font-nunito">Lvl ${friend.level || 1} • ${friend.email}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
          <button onclick="window.friendsApp.openRequestItemModal(${friend.id}, '${friend.name}')" class="px-3 py-1.5 rounded-xl bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer flex items-center gap-1 shadow-xs">
            <span>📩</span>
            <span>Minta Barang</span>
          </button>

          <button onclick="window.friendsApp.openGiftItemModal(${friend.id}, '${friend.name}')" class="px-3 py-1.5 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer flex items-center gap-1 shadow-xs">
            <span>🎁</span>
            <span>Beri Barang</span>
          </button>
        </div>
      </div>
    `).join('');
  }

  renderIncomingRequests() {
    const container = document.querySelector('#incoming-requests-container');
    const badge = document.querySelector('#incoming-requests-count');
    if (!container) return;

    if (badge) {
      badge.textContent = this.incomingRequests.length;
      badge.style.display = this.incomingRequests.length > 0 ? 'inline-block' : 'none';
    }

    if (this.incomingRequests.length === 0) {
      container.innerHTML = `
        <div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">
          Tidak ada permintaan pertemanan masuk.
        </div>
      `;
      return;
    }

    container.innerHTML = this.incomingRequests.map(req => `
      <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 flex items-center justify-between gap-3">
        <div>
          <span class="font-baloo font-bold text-xs text-[#1F3D20] block">${req.name}</span>
          <span class="text-[10px] text-[#6B6B55] block">${req.email} • ${req.created_at}</span>
        </div>
        <div class="flex items-center gap-1.5">
          <button onclick="window.friendsApp.acceptFriend(${req.requester_id})" class="px-2.5 py-1 rounded-lg bg-[#27AE60] text-white font-baloo font-bold text-xs hover:bg-[#219653] transition-colors cursor-pointer">
            Terima
          </button>
          <button onclick="window.friendsApp.removeFriend(${req.requester_id})" class="px-2 py-1 rounded-lg bg-gray-200 text-gray-700 font-baloo font-bold text-xs hover:bg-gray-300 transition-colors cursor-pointer">
            Tolak
          </button>
        </div>
      </div>
    `).join('');
  }

  renderItemRequests() {
    const container = document.querySelector('#incoming-item-requests-container');
    if (!container) return;

    if (this.itemRequests.length === 0) {
      container.style.display = 'none';
      return;
    }

    container.style.display = 'block';
    container.innerHTML = `
      <div class="p-4 rounded-2xl bg-[#FFD700]/15 border border-[#FFD700]/40 space-y-3">
        <h4 class="font-baloo font-extrabold text-sm text-[#1F3D20] flex items-center gap-1.5">
          <span>📩</span>
          <span>Permintaan Barang Shop dari Teman (${this.itemRequests.length})</span>
        </h4>
        <div class="space-y-2">
          ${this.itemRequests.map(req => `
            <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 flex items-center justify-between gap-3">
              <div>
                <span class="font-baloo font-bold text-xs text-[#1F3D20] block">${req.sender_name} meminta item:</span>
                <span class="font-baloo font-extrabold text-xs text-[#8B6A4C] block">📦 ${req.item_code}</span>
                ${req.note ? `<span class="text-[10px] text-[#6B6B55] italic block">"${req.note}"</span>` : ''}
              </div>
              <button onclick="window.friendsApp.giftItem(${req.sender_id}, '${req.item_code}', ${req.id})" class="px-3 py-1.5 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer shadow-xs">
                Kirimkan Barang
              </button>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  async searchUsers(query) {
    const container = document.querySelector('#search-results-container');
    if (!container) return;

    if (!query || query.trim().length < 2) {
      container.innerHTML = '<div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">Ketik nama atau email untuk mencari pengguna...</div>';
      return;
    }

    container.innerHTML = '<div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">Mencari pengguna...</div>';

    try {
      const res = await apiClient.get(`/friends/search?q=${encodeURIComponent(query)}`);
      const results = res.results || [];

      if (results.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">Tidak ada pengguna yang cocok ditemukan.</div>';
        return;
      }

      container.innerHTML = results.map(u => `
        <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 flex items-center justify-between gap-3">
          <div>
            <span class="font-baloo font-bold text-xs text-[#1F3D20] block">${u.name}</span>
            <span class="text-[10px] text-[#6B6B55] block">${u.email} • Lvl ${u.level}</span>
          </div>
          <div>
            ${u.friendship_status === 'accepted' 
              ? `<span class="text-[10px] font-baloo font-bold text-[#27AE60] bg-[#27AE60]/10 px-2 py-0.5 rounded-full">✓ Teman</span>`
              : u.friendship_status === 'pending'
                ? `<span class="text-[10px] font-baloo font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">${u.is_requester ? 'Menunggu Konfirmasi' : 'Permintaan Masuk'}</span>`
                : `<button onclick="window.friendsApp.sendFriendRequest(${u.id})" class="px-3 py-1 rounded-lg bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                    + Tambah Teman
                   </button>`
            }
          </div>
        </div>
      `).join('');
    } catch (err) {
      container.innerHTML = '<div class="text-center py-4 text-red-500 font-nunito text-xs">Gagal mencari pengguna.</div>';
    }
  }

  async sendFriendRequest(friendId) {
    try {
      const res = await apiClient.post('/friends/add', { friend_id: friendId });
      alert(res.message || 'Permintaan pertemanan berhasil dikirim!');
      await this.loadData();
      const searchInput = document.querySelector('#friend-search-input');
      if (searchInput && searchInput.value) this.searchUsers(searchInput.value);
    } catch (err) {
      alert(err.response?.data?.message || err.message || 'Gagal mengirim permintaan pertemanan.');
    }
  }

  async acceptFriend(requesterId) {
    try {
      const res = await apiClient.post('/friends/accept', { requester_id: requesterId });
      alert(res.message || 'Permintaan pertemanan diterima!');
      await this.loadData();
    } catch (err) {
      alert(err.response?.data?.message || err.message || 'Gagal menerima pertemanan.');
    }
  }

  async removeFriend(friendId) {
    try {
      const res = await apiClient.post('/friends/remove', { friend_id: friendId });
      alert(res.message || 'Berhasil memperbarui pertemanan.');
      await this.loadData();
    } catch (err) {
      alert(err.response?.data?.message || err.message || 'Gagal memperbarui pertemanan.');
    }
  }

  openRequestItemModal(friendId, friendName) {
    document.querySelector('#req-friend-id').value = friendId;
    document.querySelector('#req-friend-name').textContent = friendName;
    document.querySelector('#request-item-modal').classList.remove('hidden');
  }

  closeRequestItemModal() {
    document.querySelector('#request-item-modal').classList.add('hidden');
  }

  async submitItemRequest(event) {
    event.preventDefault();
    const friendId = document.querySelector('#req-friend-id').value;
    const itemCode = document.querySelector('#req-item-code').value;
    const note = document.querySelector('#req-note').value;
    const btn = document.querySelector('#req-submit-btn');

    btn.disabled = true;
    try {
      const res = await apiClient.post('/friends/request-item', {
        friend_id: friendId,
        item_code: itemCode,
        note: note
      });
      alert(res.message || 'Permintaan barang shop berhasil dikirim!');
      this.closeRequestItemModal();
      await this.loadData();
    } catch (err) {
      alert(err.response?.data?.message || err.message || 'Gagal mengirim permintaan barang.');
    } finally {
      btn.disabled = false;
    }
  }

  openGiftItemModal(friendId, friendName) {
    document.querySelector('#gift-friend-id').value = friendId;
    document.querySelector('#gift-friend-name').textContent = friendName;

    const select = document.querySelector('#gift-item-code');
    if (this.inventory.length === 0) {
      select.innerHTML = '<option value="" disabled selected>Inventaris Anda kosong (Beli item di Shop dulu)</option>';
    } else {
      select.innerHTML = this.inventory.map(inv => `
        <option value="${inv.item_code}">${inv.item_code} (Dimiliki: x${inv.quantity})</option>
      `).join('');
    }

    document.querySelector('#gift-item-modal').classList.remove('hidden');
  }

  closeGiftItemModal() {
    document.querySelector('#gift-item-modal').classList.add('hidden');
  }

  async submitGiftItem(event) {
    event.preventDefault();
    const friendId = document.querySelector('#gift-friend-id').value;
    const itemCode = document.querySelector('#gift-item-code').value;
    const btn = document.querySelector('#gift-submit-btn');

    if (!itemCode) {
      alert('Pilih barang shop yang akan diberikan.');
      return;
    }

    btn.disabled = true;
    try {
      const res = await apiClient.post('/friends/gift-item', {
        friend_id: friendId,
        item_code: itemCode
      });
      alert(res.message || 'Barang shop berhasil dikirimkan ke teman!');
      this.closeGiftItemModal();
      await this.loadData();
    } catch (err) {
      alert(err.response?.data?.message || err.message || 'Gagal mengirimkan barang.');
    } finally {
      btn.disabled = false;
    }
  }

  async giftItem(friendId, itemCode, requestId = null) {
    try {
      const res = await apiClient.post('/friends/gift-item', {
        friend_id: friendId,
        item_code: itemCode,
        request_id: requestId
      });
      alert(res.message || 'Barang shop berhasil dikirimkan ke teman!');
      await this.loadData();
    } catch (err) {
      alert(err.response?.data?.message || err.message || 'Gagal mengirimkan barang.');
    }
  }
}

export default FriendsModule;
