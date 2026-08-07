var e=new class{constructor(){this.baseUrl=`/api`}getCsrfToken(){let e=document.querySelector(`meta[name="csrf-token"]`);return e?e.getAttribute(`content`):``}getHeaders(e=!1){let t={Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`,"X-CSRF-TOKEN":this.getCsrfToken()};return e||(t[`Content-Type`]=`application/json`),t}async handleResponse(e){let t=e.headers.get(`content-type`)?.includes(`application/json`)?await e.json():null;if(!e.ok){let n=t?.message||t?.error||`HTTP error ${e.status}`,r=Error(n);throw r.status=e.status,r.data=t,r}return t}async get(e,t={}){let n=new URL(`${this.baseUrl}${e}`,window.location.origin);Object.keys(t).forEach(e=>{t[e]!==null&&t[e]!==void 0&&n.searchParams.append(e,t[e])});let r=await fetch(n.toString(),{method:`GET`,headers:this.getHeaders(),credentials:`include`});return this.handleResponse(r)}async post(e,t={},n=!1){let r=`${this.baseUrl}${e}`,i={method:`POST`,headers:this.getHeaders(n),credentials:`include`,body:n?t:JSON.stringify(t)},a=await fetch(r,i);return this.handleResponse(a)}async put(e,t={},n=!1){let r=`${this.baseUrl}${e}`,i={method:`PUT`,headers:this.getHeaders(n),credentials:`include`,body:n?t:JSON.stringify(t)},a=await fetch(r,i);return this.handleResponse(a)}async delete(e){let t=`${this.baseUrl}${e}`,n=await fetch(t,{method:`DELETE`,headers:this.getHeaders(),credentials:`include`});return this.handleResponse(n)}},t=class{constructor(e={}){this.videoElement=e.videoElement||document.querySelector(`#ar-video`),this.canvasElement=e.canvasElement||document.createElement(`canvas`),this.stream=null,this.isScanning=!1}async init(){if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia){console.warn(`Akses kamera (getUserMedia) membutuhkan koneksi HTTPS atau host localhost.`);let e=document.querySelector(`#camera-warning`);e&&(e.classList.remove(`hidden`),e.textContent=`Kamera AR memerlukan protokol HTTPS (atau http://localhost:8000) sesuai aturan keamanan browser.`);return}try{this.videoElement&&(this.stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:`environment`,width:{ideal:1280},height:{ideal:720}},audio:!1}),this.videoElement.srcObject=this.stream,await this.videoElement.play())}catch(e){console.warn(`Gagal mengakses kamera:`,e)}}fetchCurrentLocation(){return new Promise(e=>{if(typeof navigator>`u`||!navigator.geolocation||typeof navigator.geolocation.getCurrentPosition!=`function`)return e({latitude:null,longitude:null});navigator.geolocation.getCurrentPosition(t=>{e({latitude:t.coords.latitude,longitude:t.coords.longitude})},t=>{console.warn(`Lokasi GPS tidak dapat diakses saat capture:`,t.message),e({latitude:null,longitude:null})},{enableHighAccuracy:!1,timeout:4e3})})}captureFrame(){if(!this.videoElement||this.videoElement.readyState!==4)throw Error(`Kamera belum siap atau tidak diizinkan di asal koneksi non-HTTPS.`);let e=this.videoElement.videoWidth||640,t=this.videoElement.videoHeight||480;return this.canvasElement.width=e,this.canvasElement.height=t,this.canvasElement.getContext(`2d`).drawImage(this.videoElement,0,0,e,t),this.canvasElement.toDataURL(`image/jpeg`,.85)}async performScan(){if(!this.isScanning){this.isScanning=!0;try{let t=this.captureFrame(),n=await this.fetchCurrentLocation(),r={image_base64:t,latitude:n.latitude,longitude:n.longitude},i=await e.post(`/scan`,r);return this.isScanning=!1,i}catch(e){throw this.isScanning=!1,e}}}stop(){this.stream&&=(this.stream.getTracks().forEach(e=>e.stop()),null)}},n=class{constructor(e={}){this.containerElement=e.containerElement||document.querySelector(`#gallery-container`),this.modalElement=e.modalElement||document.querySelector(`#gallery-modal`),this.progressTextElement=e.progressTextElement||document.querySelector(`#seedex-progress-text`),this.progressBarElement=e.progressBarElement||document.querySelector(`#seedex-progress-bar`),this.items=[],this.speciesCatalog=[],this.role=window.USER_ROLE||`viewer`}async loadGallery(){try{let t=await e.get(`/gallery`);this.items=t.data?.data||t.data||[],this.role=t.data?.role||this.role;try{let t=await e.get(`/ranger/species`);this.speciesCatalog=t.data?.data||t.data||[]}catch{this.speciesCatalog=[]}this.render()}catch(e){console.error(`[GalleryModule] Gagal memuat Seedex:`,e)}}render(){if(!this.containerElement)return;let e=[`ranger`,`admin`].includes(this.role),t=e?this.items.length:Math.max(this.speciesCatalog.length,12),n=this.items.length,r=e?n>0?100:0:Math.min(Math.round(n/t*100),100);if(this.progressTextElement)if(e){let e=window.translations?.ranger_uploaded||`Spesimen Di-upload`;this.progressTextElement.textContent=`${n} ${e}`}else{let e=window.translations?.discovered||`Ditemukan`;this.progressTextElement.textContent=`${n} / ${t} Seedex ${e}`}if(this.progressBarElement&&(this.progressBarElement.style.width=`${r}%`),this.items.length===0&&(e||this.speciesCatalog.length===0)){let t=e?window.translations?.ranger_empty_title||`Belum Ada Temuan Di-upload`:window.translations?.empty_gallery||`Seedex Masih Kosong`,n=e?window.translations?.ranger_empty_subtitle||`Gunakan Peta Digital atau Kamera AR untuk mulai mendokumentasikan tumbuhan di lapangan!`:`Jelajahi peta dan temukan marker spesies tumbuhan untuk mengumpulkan entri ke Seedex!`;this.containerElement.innerHTML=`
        <div class="card-gg p-12 text-center space-y-4 max-w-md mx-auto">
          <div class="w-16 h-16 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20]">${t}</h3>
          <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">${n}</p>
          <a href="/peta" class="btn-gg-primary inline-flex items-center gap-2 text-xs">Buka Peta Temuan</a>
        </div>
      `;return}let i=new Set,a=new Map;this.items.forEach(e=>{let t=e.sighting?.species||e.species||e.plant_species;t?.id&&(i.add(t.id),a.set(t.id,e))});let o=`<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">`;if(this.items.forEach(e=>{let t=e.sighting||e,n=t.species||e.plant_species,r=n?.common_name||`Spesimen Flora`,i=n?.scientific_name||``,a=t.photo_url||e.photo_url||``,s=n?.conservation_status||`Common`,c=`#9E9E8A`;s.toLowerCase().includes(`rare`)?c=`#7D5BA6`:s.toLowerCase().includes(`uncommon`)&&(c=`#4C8C4A`),o+=`
        <div class="seedex-card card-gg card-gg-hover p-3 rounded-2xl overflow-hidden cursor-pointer group" data-id="${e.id}">
          <div class="h-36 rounded-xl bg-[#E2E1C4] overflow-hidden relative mb-2.5">
            <img src="${a}" alt="${r}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
            <span class="absolute top-2 right-2 text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full text-[#FBFAF0]" style="background-color: ${c};">
              ${s.toUpperCase()}
            </span>
          </div>
          <div>
            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight truncate group-hover:text-[#4C8C4A] transition-colors">${r}</h4>
            <p class="font-nunito text-[11px] text-[#6B6B55] italic truncate">${i||`Ditemukan`}</p>
          </div>
        </div>
      `}),!e){let e=Math.max(t-n,4);for(let t=0;t<e;t++)o+=`
          <div class="card-gg p-3 rounded-2xl opacity-60 bg-[#E2E1C4]/40 flex flex-col justify-between">
            <div class="h-36 rounded-xl bg-[#E2E1C4] flex items-center justify-center text-[#6B6B55] mb-2.5">
              <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <div>
              <h4 class="font-baloo font-bold text-sm text-[#6B6B55]">???</h4>
              <p class="font-nunito text-[11px] text-[#6B6B55]">Locked / Belum Ditemukan</p>
            </div>
          </div>
        `}o+=`</div>`,this.containerElement.innerHTML=o,this.containerElement.querySelectorAll(`.seedex-card`).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.getAttribute(`data-id`);this.openDetail(t)})})}async openDetail(t){try{let n=await e.get(`/gallery/${t}`),r=n.data?.data||n.data;if(!r)return;let i=r.sighting||r,a=i.species||r.plant_species,o=a?.common_name||`Hasil Temuan`,s=a?.scientific_name||``,c=a?.description||`Deskripsi tanaman belum tersedia.`,l=a?.care_instructions||``,u=i.photo_url||r.photo_url;if(this.modalElement){this.modalElement.querySelector(`#modal-title`).textContent=o,this.modalElement.querySelector(`#modal-scientific`).textContent=s,this.modalElement.querySelector(`#modal-img`).src=u,this.modalElement.querySelector(`#modal-desc`).textContent=c;let t=this.modalElement.querySelector(`#modal-care-text`);t&&(t.textContent=l||`Belum ada petunjuk perawatan dari Ranger. Menyiram 2x sehari dan memberi kompos teratur disarankan.`);let n=this.modalElement.querySelector(`#toggle-care-edit-btn`),i=this.modalElement.querySelector(`#care-edit-form`),d=this.modalElement.querySelector(`#care-instructions-input`);if(i&&i.classList.add(`hidden`),this.role===`ranger`&&n&&a?.id){n.classList.remove(`hidden`),n.onclick=()=>{d&&(d.value=a.care_instructions||``),i&&i.classList.toggle(`hidden`)};let r=this.modalElement.querySelector(`#cancel-care-edit-btn`);r&&(r.onclick=()=>i.classList.add(`hidden`));let o=this.modalElement.querySelector(`#save-care-edit-btn`);o&&(o.onclick=async()=>{try{o.disabled=!0,o.textContent=`Menyimpan...`;let n=d.value.trim();await e.put(`/ranger/species/${a.id}`,{species_code:a.species_code,common_name:a.common_name,description:a.description,care_instructions:n}),a.care_instructions=n,t&&(t.textContent=n||`Belum ada petunjuk perawatan dari Ranger.`);let r=window.translations||{};i&&i.classList.add(`hidden`),alert(r.instructions_updated||`✨ Petunjuk perawatan pohon berhasil diperbarui!`)}catch(e){alert(`Gagal menyimpan petunjuk perawatan: `+(e.message||`Terjadi kesalahan`))}finally{o.disabled=!1,o.textContent=window.translations&&window.translations.save_instructions||`Simpan Petunjuk`}})}else n&&n.classList.add(`hidden`);let f=this.modalElement.querySelector(`#modal-delete-btn`);f&&(f.onclick=()=>this.deleteItem(r.id)),this.modalElement.classList.remove(`hidden`)}}catch(e){console.error(`[GalleryModule] Gagal memuat detail Seedex:`,e)}}async deleteItem(t){if(confirm(`Apakah kamu yakin ingin menghapus temuan ini dari Seedex?`))try{await e.delete(`/gallery/${t}`),this.modalElement&&this.modalElement.classList.add(`hidden`),await this.loadGallery()}catch{alert(`Gagal menghapus temuan.`)}}},r=class{constructor(e={}){this.containerElement=e.containerElement||document.querySelector(`#garden-plots-container`),this.plots=[],this.seeds=[],this.userCoin=0,this.userExp=0,this.updateTimer=null,this.selectedPlotForPlanting=null}async init(){await this.loadPlots(),this.startAutoRefresh()}startAutoRefresh(){this.updateTimer&&clearInterval(this.updateTimer),this.updateTimer=setInterval(()=>this.tickGrowthProgress(),1e3)}tickGrowthProgress(){let e=!1,t=Date.now();this.plots.forEach(n=>{let r=n.current_planting;if(!r||r.status!==`growing`)return;let i=r.planted_at?new Date(r.planted_at).getTime():t,a=r.ready_at?new Date(r.ready_at).getTime():t,o=Math.max(1,a-i),s=Math.max(0,t-i),c=Math.max(0,Math.ceil((a-t)/1e3));if(c<=0){r.status=`ready`,e=!0;return}let l=Math.min(99,Math.max(1,Math.floor(s/o*100))),u=this.containerElement?.querySelector(`[data-plot-slot="${n.slot_number}"]`);if(!u)return;let d=u.querySelector(`.growth-bar-fill`);d&&(d.style.width=`${l}%`);let f=u.querySelector(`.growth-countdown`);f&&(f.textContent=this.formatTimeRemaining(c));let p=u.querySelector(`.growth-time-pill`);p&&(p.textContent=`⏳ ${this.formatTimeRemaining(c)}`);let m=u.querySelector(`.growth-stage-label`);m&&(l<35?m.textContent=`Tunas Mungil (${l}%)`:l<75?m.textContent=`Tumbuh Berdaun (${l}%)`:m.textContent=`Hampir Matang (${l}%)`)}),e&&this.loadPlots()}async loadPlots(){try{let t=await e.get(`/minigame/plots`);this.plots=t.data||[],this.seeds=t.seeds||[],this.tools=t.tools||[],this.userCoin=t.user_coin||0,this.userExp=t.user_exp||0,typeof window.updateUserCoin==`function`&&window.updateUserCoin(this.userCoin),typeof window.updateUserExp==`function`&&window.updateUserExp(this.userExp),this.render()}catch(e){console.error(`Gagal memuat lahan kebun:`,e),this.containerElement&&(this.containerElement.innerHTML=`
          <div class="text-center py-12 text-[#F5F4DA]">
            <p>Gagal memuat lahan kebun. Silakan muat ulang halaman.</p>
          </div>
        `)}}showToast(e,t=`success`){typeof window.showToast==`function`?window.showToast(e,t):alert(e)}getSeedDetails(e){let t=window.translations&&window.translations.title&&window.translations.title.includes(`Virtual Garden`)||document.documentElement.lang===`en`;return{seed_sunflower:{name:t?`Sunflower`:`Bunga Matahari`,icon:`🌻`,duration:6,exp:50,coin:70,price:50},seed_tomato:{name:t?`Organic Tomato`:`Tomat Organik`,icon:`🍅`,duration:12,exp:90,coin:110,price:75},seed_monstera:{name:`Monstera Deliciosa`,icon:`🌿`,duration:21,exp:160,coin:180,price:120},seed_orchid:{name:t?`Black Orchid`:`Anggrek Hitam`,icon:`🪻`,duration:36,exp:300,coin:310,price:200},SEED_DEFAULT:{name:t?`Sunflower`:`Bunga Matahari`,icon:`🌻`,duration:6,exp:50,coin:70,price:50}}[e]||{name:t?`Species Seed`:`Benih Spesies`,icon:`🌱`,duration:6,exp:50,coin:70,price:50}}formatTimeRemaining(e){let t=window.translations&&window.translations.title&&window.translations.title.includes(`Virtual Garden`)||document.documentElement.lang===`en`;if(e<=0)return t?`Ready to harvest!`:`Siap panen!`;let n=Math.floor(e/60),r=e%60;return n>0?`${n}m ${r}s`:`${r}s`}renderPlantVisual(e,t,n){return e?e.status===`ready`||e.ready_at&&new Date>=new Date(e.ready_at)?`
        <div class="relative flex flex-col items-center justify-center h-24 my-1 plant-glow-animation sway-animation">
          <span class="text-5xl filter drop-shadow-md cursor-pointer">${n.icon}</span>
          <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-[#FBFAF0] text-[#1F3D20] shadow-sm mt-1 border border-[#1F3D20]/20">
            ✨ Siap Panen!
          </span>
        </div>
      `:t<35?`
        <div class="relative flex flex-col items-center justify-center h-24 my-1 sway-animation">
          <div class="text-3xl filter drop-shadow-sm">🌱</div>
          <span class="growth-stage-label text-[9px] font-bold text-[#F5F4DA] bg-[#2B1B10]/80 px-2 py-0.5 rounded-full mt-1 border border-[#7A5840]/40">
            Tunas Mungil (${t}%)
          </span>
        </div>
      `:t<75?`
        <div class="relative flex flex-col items-center justify-center h-24 my-1 sway-animation">
          <div class="text-4xl filter drop-shadow-sm">🌿</div>
          <span class="growth-stage-label text-[9px] font-bold text-[#F5F4DA] bg-[#2B1B10]/80 px-2 py-0.5 rounded-full mt-1 border border-[#7A5840]/40">
            Tumbuh Berdaun (${t}%)
          </span>
        </div>
      `:`
      <div class="relative flex flex-col items-center justify-center h-24 my-1 sway-animation">
        <div class="text-4xl filter drop-shadow-sm">${n.icon}</div>
        <span class="growth-stage-label text-[9px] font-bold text-[#F5F4DA] bg-[#2B1B10]/90 px-2 py-0.5 rounded-full mt-1 border border-[#7A5840]/40">
          Hampir Matang (${t}%)
        </span>
      </div>
    `:``}render(){if(!this.containerElement)return;if(this.plots.length===0){this.containerElement.innerHTML=`
        <div class="text-center py-12 text-[#F5F4DA]">
          <span class="animate-spin inline-block text-2xl mb-2">🌿</span>
          <p class="font-baloo font-bold">Membuat petak lahan kebun...</p>
        </div>
      `;return}let e=this.plots.map(e=>{let t=e.unlocked,n=e.current_planting;if(!t){let t=e.purchase_cost||50,n=this.userCoin>=t;return`
          <div class="card-gg p-4 flex flex-col justify-between items-center text-center relative overflow-hidden group bg-[#FBFAF0] border-2 border-[#8B6A4C]/60 shadow-lg">
            <!-- Soil Bed Backdrop -->
            <div class="w-full rounded-2xl plot-soil-bed p-4 flex flex-col items-center justify-center text-center my-1 min-h-[140px] opacity-75">
              <div class="w-12 h-12 rounded-2xl bg-[#2B1B10]/80 border border-[#8B5A2B]/40 text-[#E7E6BE] flex items-center justify-center text-xl mb-2 shadow-inner">
                🔒
              </div>
              <span class="text-[11px] font-baloo font-bold text-[#F5F4DA] bg-[#2B1B10]/90 px-2.5 py-0.5 rounded-full border border-[#8B5A2B]/30">
                Lahan Terkunci #${e.slot_number}
              </span>
            </div>

            <div class="w-full pt-3 border-t border-[#1F3D20]/10">
              <div class="flex items-center justify-center gap-1 font-baloo font-bold text-xs text-[#1F3D20] mb-2">
                <span>Biaya: 🪙 ${t} NC</span>
              </div>
              <button 
                data-plot-id="${e.id}"
                data-cost="${t}"
                class="unlock-btn w-full btn-gg-primary text-xs py-2 px-3 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                ${n?``:`disabled title="Coin tidak mencukupi"`}
              >
                ${n?`Buka Lahan`:`Coin Kurang`}
              </button>
            </div>
          </div>
        `}if(!n){let t=this.seeds.reduce((e,t)=>e+t.quantity,0);return`
          <div class="card-gg card-gg-hover p-4 flex flex-col justify-between items-center text-center relative bg-[#FBFAF0] border-2 border-[#8B6A4C] shadow-lg">
            <!-- Visual Earth Soil Mound -->
            <div class="w-full rounded-2xl plot-soil-bed p-3 flex flex-col items-center justify-center text-center my-1 min-h-[140px] relative group-hover:border-[#7A5840] transition-colors">
              <div class="w-12 h-12 rounded-2xl bg-[#362215]/80 border border-[#7A5840]/50 text-[#E2E1C4] flex items-center justify-center text-2xl mb-1 shadow-inner">
                ⛏️
              </div>
              <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#264225]/90 px-2.5 py-0.5 rounded-full border border-[#436B42]/40">
                Tanah Gembur #${e.slot_number}
              </span>
              <p class="text-[10px] text-[#E7E6BE] mt-1 font-nunito opacity-80">Lahan Siap Ditanami</p>
            </div>

            <div class="w-full pt-3 border-t border-[#1F3D20]/10">
              ${t>0?`
                <button 
                  data-plot-id="${e.id}"
                  class="open-seed-modal-btn w-full btn-gg-primary text-xs py-2 px-3 cursor-pointer flex items-center justify-center gap-1.5"
                >
                  <span>🌱</span> Tanam Benih (${t})
                </button>
              `:`
                <a 
                  href="/shop" 
                  class="w-full inline-block text-center rounded-full bg-[#8B6A4C] hover:bg-[#72553B] text-[#F5F4DA] font-baloo font-bold text-xs py-2 px-3 transition-colors shadow-xs"
                >
                  🛒 Beli Benih di Shop
                </a>
              `}
            </div>
          </div>
        `}let r=new Date,i=n.ready_at?new Date(n.ready_at):r,a=n.status===`ready`||r>=i,o=n.seed_code||`seed_sunflower`,s=this.getSeedDetails(o),c=n.plant_species?n.plant_species.common_name:s.name;if(a)return`
          <div class="card-gg p-4 flex flex-col justify-between items-center text-center bg-[#FBFAF0] border-2 border-[#1F3D20] shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between w-full mb-1">
              <span class="text-[10px] font-baloo font-extrabold text-[#1F3D20]">Lahan #${e.slot_number}</span>
              <span class="px-2 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[9px] font-baloo font-extrabold shadow-xs animate-pulse">
                🌾 SIAP PANEN
              </span>
            </div>

            <!-- Soil Bed & Blooming Plant Visual -->
            <div class="w-full rounded-2xl plot-soil-bed p-2 flex flex-col items-center justify-center text-center my-1 min-h-[140px] relative border-2 border-[#FFD700]/60 shadow-lg">
              ${this.renderPlantVisual(n,100,s)}
              <h4 class="font-baloo font-extrabold text-xs text-[#F5F4DA] drop-shadow-md bg-[#2B1B10]/80 px-2 py-0.5 rounded-md mt-1">
                ${c}
              </h4>
            </div>

            <div class="w-full pt-2 border-t border-[#1F3D20]/10">
              <button 
                data-planting-id="${n.id}"
                class="harvest-btn w-full btn-gg-primary text-xs py-2 px-3 cursor-pointer flex items-center justify-center gap-1.5 shadow-md"
              >
                <span>🌾</span> Panen (+${s.exp} EXP, ${typeof window.getNcIconSvg==`function`?window.getNcIconSvg(`w-3.5 h-3.5`):`🪙`} +${s.coin} NC)
              </button>
            </div>
          </div>
        `;let l=n.planted_at?new Date(n.planted_at):r,u=Math.max(1,i.getTime()-l.getTime()),d=Math.max(0,r.getTime()-l.getTime()),f=Math.min(99,Math.max(5,Math.floor(d/u*100))),p=Math.max(0,Math.ceil((i.getTime()-r.getTime())/1e3));return`
        <div class="card-gg p-4 flex flex-col justify-between items-center text-center bg-[#FBFAF0] border-2 border-[#8B6A4C] shadow-lg" data-plot-slot="${e.slot_number}">
          <div class="flex items-center justify-between w-full mb-1">
            <span class="text-[10px] font-baloo font-bold text-[#1F3D20]">Lahan #${e.slot_number}</span>
            <span class="growth-time-pill px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[9px] font-baloo font-bold">
              ⏳ ${this.formatTimeRemaining(p)}
            </span>
          </div>

          <!-- Soil Bed & Growing Plant Visual Stage -->
          <div class="w-full rounded-2xl plot-soil-bed p-2 flex flex-col items-center justify-center text-center my-1 min-h-[140px] relative">
            ${this.renderPlantVisual(n,f,s)}
            <h4 class="font-baloo font-bold text-xs text-[#F5F4DA] drop-shadow-md bg-[#2B1B10]/80 px-2 py-0.5 rounded-md mt-1">
              ${c}
            </h4>
          </div>

          <!-- Growth Progress Bar & Countdown Label -->
          <div class="w-full space-y-1 my-1">
            <div class="flex justify-between items-center text-[10px] font-baloo font-bold text-[#1F3D20] px-0.5">
              <span>⏳ Menunggu Panen:</span>
              <span class="growth-countdown text-[#8B5A2B] font-extrabold">${this.formatTimeRemaining(p)}</span>
            </div>
            <div class="w-full bg-[#E2E1C4] rounded-full h-3 overflow-hidden border border-[#1F3D20]/10 p-0.5">
              <div class="growth-bar-fill bg-gradient-to-r from-[#1F3D20] to-[#27AE60] h-full rounded-full transition-[width] duration-1000 ease-linear" style="width: ${f}%;"></div>
            </div>
          </div>

          <!-- Growth Action Buttons -->
          <div class="w-full pt-2 border-t border-[#1F3D20]/10 flex flex-col gap-1.5">
            ${(()=>{let e=this.tools.find(e=>e.item_code===`tool_watering_can`&&e.quantity>0),t=e?e.quantity:0,r=this.tools.find(e=>e.item_code===`tool_fertilizer`&&e.quantity>0),i=r?r.quantity:0;return t===0&&i===0?`
                  <div class="w-full text-center py-1 bg-[#E2E1C4]/40 rounded-xl p-2 border border-[#1F3D20]/10">
                    <span class="text-[10px] font-baloo font-bold text-[#6B6B55] block">🌱 Tumbuh Secara Alami</span>
                    <a href="/shop" class="text-[10px] font-baloo font-extrabold text-[#8B6A4C] hover:underline flex items-center justify-center gap-1 mt-0.5">
                      <span>🛒</span> Beli Alat di Shop untuk Mempercepat
                    </a>
                  </div>
                `:`
                ${t>0?`
                  <button 
                    data-planting-id="${n.id}"
                    class="water-btn w-full rounded-full bg-[#E2E1C4] hover:bg-[#1F3D20] text-[#1F3D20] hover:text-[#F5F4DA] font-baloo font-bold text-xs py-1.5 px-3 transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                  >
                    <span>💧</span> Siram Otomatis (-10m) [x${t}]
                  </button>
                `:``}
                ${i>0?`
                  <button 
                    data-planting-id="${n.id}"
                    class="fertilize-btn w-full rounded-full bg-[#27AE60] hover:bg-[#1E8449] text-white font-baloo font-bold text-xs py-1.5 px-3 transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-sm"
                  >
                    <span>🧪</span> Pupuk Organik (-5m) [x${i}]
                  </button>
                `:``}
              `})()}
          </div>
        </div>
      `}).join(``);this.containerElement.innerHTML=`
      <div class="wooden-fence-header rounded-2xl px-5 py-3 flex items-center justify-between shadow-md mb-6 border-2 border-[#5C3A24]">
        <div class="flex items-center gap-2">
          <span class="text-xl">🌾</span>
          <h3 class="font-baloo font-extrabold text-base sm:text-lg text-[#F5F4DA] tracking-wide">LAHAN KEBUN VIRTUAL GUARDIAN</h3>
        </div>
        <div class="flex items-center gap-2 text-xs font-baloo font-bold text-[#E7E6BE]">
          <span>4 Petak Tanah Gembur</span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        ${e}
      </div>
    `,this.attachEventListeners()}attachEventListeners(){this.containerElement&&(this.containerElement.querySelectorAll(`.unlock-btn`).forEach(e=>{e.addEventListener(`click`,async t=>{t.preventDefault();let n=e.dataset.plotId,r=parseInt(e.dataset.cost||`50`);await this.unlockPlot(n,r)})}),this.containerElement.querySelectorAll(`.open-seed-modal-btn`).forEach(e=>{e.addEventListener(`click`,t=>{t.preventDefault();let n=e.dataset.plotId;this.openSeedSelectorModal(n)})}),this.containerElement.querySelectorAll(`.water-btn`).forEach(e=>{e.addEventListener(`click`,async t=>{t.preventDefault();let n=e.dataset.plantingId;await this.waterPlant(n)})}),this.containerElement.querySelectorAll(`.fertilize-btn`).forEach(e=>{e.addEventListener(`click`,async t=>{t.preventDefault();let n=e.dataset.plantingId;await this.applyFertilizer(n)})}),this.containerElement.querySelectorAll(`.harvest-btn`).forEach(e=>{e.addEventListener(`click`,async t=>{t.preventDefault();let n=e.dataset.plantingId;await this.harvestPlant(n)})}))}openSeedSelectorModal(e){this.selectedPlotForPlanting=e;let t=document.querySelector(`#seed-selector-modal`);t||(t=document.createElement(`div`),t.id=`seed-selector-modal`,t.className=`fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4`,document.body.appendChild(t));let n=this.seeds.filter(e=>e.quantity>0);t.innerHTML=`
      <div class="card-gg max-w-md w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0]">
        <div class="flex justify-between items-center border-b border-[#1F3D20]/10 pb-3">
            <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20]">Pilih Benih untuk Ditanam</h3>
            <button id="close-seed-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
        </div>

        <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
          ${n.map(e=>{let t=this.getSeedDetails(e.item_code);return`
              <div class="p-3 rounded-2xl bg-[#F5F4DA] border border-[#1F3D20]/10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <span class="text-2xl p-2 rounded-xl bg-[#E2E1C4]">${t.icon}</span>
                  <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">${t.name}</h4>
                    <div class="flex items-center gap-2 text-[10px] text-[#4A5D4E] font-extrabold my-0.5">
                      <span>⏱️ ${t.duration}m</span>
                      <span>✨ +${t.exp} EXP</span>
                      <span class="flex items-center gap-0.5">${typeof window.getNcIconSvg==`function`?window.getNcIconSvg(`w-3.5 h-3.5`):`🪙`} +${t.coin} NC</span>
                    </div>
                    <span class="text-[10px] text-[#6B6B55]">Stok: x${e.quantity}</span>
                  </div>
                </div>
                <button 
                  data-seed-code="${e.item_code}"
                  class="select-seed-btn btn-gg-primary text-xs py-1.5 px-3 cursor-pointer"
                >
                  Tanam
                </button>
              </div>
            `}).join(``)}
        </div>

        <div class="pt-2 text-center border-t border-[#1F3D20]/10">
          <a href="/shop" class="text-xs font-baloo font-bold text-[#8B6A4C] hover:underline">
            🛒 Beli Benih Lain di Shop
          </a>
        </div>
      </div>
    `,t.classList.remove(`hidden`),t.querySelector(`#close-seed-modal-btn`).addEventListener(`click`,()=>{t.classList.add(`hidden`)}),t.querySelectorAll(`.select-seed-btn`).forEach(e=>{e.addEventListener(`click`,async()=>{let n=e.dataset.seedCode;t.classList.add(`hidden`),await this.plantSeed(this.selectedPlotForPlanting,n)})})}async unlockPlot(t,n){try{let n=await e.post(`/minigame/plots/${t}/unlock`);this.showToast(n.message||`Lahan tanam berhasil dibuka!`,`success`),await this.loadPlots()}catch(e){let t=e.response?.data?.message||e.message||`Gagal membuka lahan.`;this.showToast(t,`error`)}}async plantSeed(t,n){try{await e.post(`/minigame/plant`,{garden_plot_id:t,seed_code:n});let r=this.getSeedDetails(n);this.showToast(`Benih ${r.name} berhasil ditanam di tanah gembur!`,`success`),await this.loadPlots()}catch(e){let t=e.response?.data?.message||e.message||`Gagal menanam benih.`;this.showToast(t,`error`)}}async waterPlant(t){try{let n=await e.post(`/minigame/water`,{planting_id:t});this.showToast(n.message||`Tanaman disiram! Pertumbuhan dipercepat 💧`,`success`),await this.loadPlots()}catch(e){let t=e.response?.data?.message||e.message||`Gagal menyiram tanaman.`;this.showToast(t,`error`)}}async applyFertilizer(t){try{let n=await e.post(`/minigame/fertilize`,{planting_id:t});this.showToast(n.message||`Pupuk Organik Super digunakan! Pertumbuhan dipotong 15 menit 🧪`,`success`),await this.loadPlots()}catch(e){let t=e.response?.data?.message||e.message||`Gagal menggunakan pupuk.`;this.showToast(t,`error`)}}async harvestPlant(t){try{let n=await e.post(`/minigame/harvest`,{planting_id:t}),r=n.data?.exp_earned||50,i=n.data?.coin_earned||20;this.showToast(`🎉 Panen Berhasil! +${r} EXP & ${typeof window.getNcIconSvg==`function`?window.getNcIconSvg(`w-3.5 h-3.5`):`🪙`} +${i} NC!`,`success`),await this.loadPlots()}catch(e){let t=e.response?.data?.message||e.message||`Gagal memanen tanaman.`;this.showToast(t,`error`)}}},i=class{constructor(e={}){this.expElement=e.expElement||document.querySelector(`#user-exp`),this.coinElement=e.coinElement||document.querySelector(`#user-coin`),this.missionCountElement=document.querySelector(`#daily-mission-count`),this.missionProgressBar=document.querySelector(`#daily-mission-progress-bar`),this.missionProgressText=document.querySelector(`#daily-mission-progress-text`),this.missionActionElement=document.querySelector(`#daily-mission-action`)}get t(){return window.translations||{}}async loadWalletBalance(){try{let t=(await e.get(`/wallet/balance`)).data;t&&(this.expElement&&(this.expElement.textContent=t.exp||0),this.coinElement&&(this.coinElement.textContent=t.coin||0))}catch(e){if(e.status===401){this.expElement&&(this.expElement.textContent=0),this.coinElement&&(this.coinElement.textContent=0);return}console.warn(`Gagal memuat saldo wallet:`,e.message)}}async loadDailyMission(){if(this.missionCountElement)try{let t=await e.get(`/daily-mission`);t&&t.data&&this.renderDailyMission(t.data)}catch(e){console.warn(`Gagal memuat status misi harian:`,e.message)}}renderDailyMission(e){let{current_count:t,target_count:n,percentage:r,is_completed:i,is_claimed:a,reward:o}=e,s=this.t;if(this.missionCountElement&&(this.missionCountElement.textContent=`${t} / ${n}`),this.missionProgressBar&&(this.missionProgressBar.style.width=`${r}%`),this.missionProgressText){let e=s.progress||`Progress`;this.missionProgressText.textContent=`${e}: ${r}% • Resets 00:00`}if(this.missionActionElement)if(a){let e=s.mission_completed_claimed||`Misi Harian Selesai & Hadiah Diklaim Hari Ini`,t=s.auto_reset_tomorrow||`Teriset otomatis besok`;this.missionActionElement.innerHTML=`
          <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#E2E1C4]/40 border border-[#1F3D20]/10">
            <span class="text-xs font-baloo font-bold text-[#1F3D20] flex items-center gap-1.5">
              <span>✅</span> ${e}
            </span>
            <span class="text-[10px] font-baloo font-bold text-[#6B6B55]">${t}</span>
          </div>
        `}else if(i){let e=s.btn_claim_reward||`Klaim Hadiah Misi (+:exp EXP & 🪙 :coin NC)`;e=e.replace(`:exp`,o.exp).replace(`:coin`,o.coin),this.missionActionElement.innerHTML=`
          <button id="btn-claim-daily-mission" class="w-full py-2.5 px-4 rounded-xl bg-[#1F3D20] hover:bg-[#2D4A2E] text-[#F5F4DA] font-baloo font-extrabold text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2 cursor-pointer transform hover:-translate-y-0.5">
            <span>✨</span>
            <span>${e}</span>
          </button>
        `;let t=this.missionActionElement.querySelector(`#btn-claim-daily-mission`);t&&t.addEventListener(`click`,()=>this.claimDailyMissionReward())}else{let e=n-t,r=s.remaining_hint||`💡 Temukan :count marker tumbuhan lagi hari ini di Peta untuk mengklaim reward!`;r=r.replace(`:count`,`<span>${e}</span>`),this.missionActionElement.innerHTML=`
          <div class="text-[11px] font-baloo font-bold text-[#6B6B55] bg-[#E2E1C4]/20 p-2 rounded-lg text-center">
            ${r}
          </div>
        `}}async claimDailyMissionReward(){let t=this.missionActionElement?.querySelector(`#btn-claim-daily-mission`),n=this.t;t&&(t.disabled=!0,t.innerText=n.claiming||`Mengklaim...`);try{let t=await e.post(`/daily-mission/claim`);t&&t.success&&(window.showToast&&window.showToast(`✨ ${t.message}`,`success`),t.data&&t.data.user&&(window.updateUserExp&&window.updateUserExp(t.data.user.exp),window.updateUserCoin&&window.updateUserCoin(t.data.user.coin)),t.data&&t.data.status&&this.renderDailyMission(t.data.status))}catch(e){if(window.showToast&&window.showToast(e.message||n.claim_failed||`Gagal mengklaim hadiah misi harian.`,`error`),t){t.disabled=!1;let e=n.btn_claim_reward||`Klaim Hadiah Misi`;e=e.replace(`:exp`,150).replace(`:coin`,50),t.innerText=e}}}},a=class{constructor(e=`.js-tilt-3d`,t={}){this.elements=typeof e==`string`?document.querySelectorAll(e):e,this.maxTilt=t.maxTilt||15,this.perspective=t.perspective||1e3,this.scale=t.scale||1.03,this.init()}init(){this.elements.forEach(e=>{e.style.transformStyle=`preserve-3d`;let t=document.createElement(`div`);t.className=`glare-overlay pointer-events-none absolute inset-0 rounded-3xl opacity-0 transition-opacity duration-300`,t.style.background=`radial-gradient(circle at 50% 50%, rgba(52, 211, 153, 0.25) 0%, transparent 70%)`,e.appendChild(t);let n=!1,r=r=>{let i=e.getBoundingClientRect(),a=r.clientX-i.left,o=r.clientY-i.top,s=i.width/2,c=i.height/2,l=(a-s)/s,u=(-((o-c)/c)*this.maxTilt).toFixed(2),d=(l*this.maxTilt).toFixed(2);e.style.transform=`perspective(${this.perspective}px) rotateX(${u}deg) rotateY(${d}deg) scale3d(${this.scale}, ${this.scale}, ${this.scale})`,t.style.opacity=`1`,t.style.background=`radial-gradient(circle at ${a}px ${o}px, rgba(52, 211, 153, 0.2) 0%, transparent 60%)`,n=!1};e.addEventListener(`mousemove`,e=>{n||=(window.requestAnimationFrame(()=>r(e)),!0)}),e.addEventListener(`mouseleave`,()=>{e.style.transition=`transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1)`,e.style.transform=`perspective(${this.perspective}px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`,t.style.opacity=`0`,setTimeout(()=>{e.style.transition=``},500)}),e.addEventListener(`mouseenter`,()=>{e.style.transition=`none`})})}},o=new class{constructor(){this.currentListContainer=document.querySelector(`#leaderboard-current-list`),this.podiumContainer=document.querySelector(`#leaderboard-podium`),this.userStatusContainer=document.querySelector(`#leaderboard-user-status`),this.historyContainer=document.querySelector(`#leaderboard-history-list`),this.tabCurrentBtn=document.querySelector(`#tab-btn-current`),this.tabHistoryBtn=document.querySelector(`#tab-btn-history`),this.tabCurrentContent=document.querySelector(`#tab-content-current`),this.tabHistoryContent=document.querySelector(`#tab-content-history`)}init(e){this.currentUserId=e,this.bindTabs(),this.loadCurrentLeaderboard()}bindTabs(){!this.tabCurrentBtn||!this.tabHistoryBtn||(this.tabCurrentBtn.addEventListener(`click`,()=>{this.setActiveTab(`current`)}),this.tabHistoryBtn.addEventListener(`click`,()=>{this.setActiveTab(`history`),this.loadLeaderboardHistory()}))}setActiveTab(e){e===`current`?(this.tabCurrentBtn.classList.add(`bg-[#1F3D20]`,`text-[#F5F4DA]`),this.tabCurrentBtn.classList.remove(`bg-transparent`,`text-[#6B6B55]`),this.tabHistoryBtn.classList.remove(`bg-[#1F3D20]`,`text-[#F5F4DA]`),this.tabHistoryBtn.classList.add(`bg-transparent`,`text-[#6B6B55]`),this.tabCurrentContent&&this.tabCurrentContent.classList.remove(`hidden`),this.tabHistoryContent&&this.tabHistoryContent.classList.add(`hidden`)):(this.tabHistoryBtn.classList.add(`bg-[#1F3D20]`,`text-[#F5F4DA]`),this.tabHistoryBtn.classList.remove(`bg-transparent`,`text-[#6B6B55]`),this.tabCurrentBtn.classList.remove(`bg-[#1F3D20]`,`text-[#F5F4DA]`),this.tabCurrentBtn.classList.add(`bg-transparent`,`text-[#6B6B55]`),this.tabHistoryContent&&this.tabHistoryContent.classList.remove(`hidden`),this.tabCurrentContent&&this.tabCurrentContent.classList.add(`hidden`))}async getCurrentLeaderboard(){return await e.get(`/leaderboard/current`)}async getLeaderboardHistory(){return await e.get(`/leaderboard/history`)}async loadCurrentLeaderboard(){try{let e=await this.getCurrentLeaderboard(),t=e&&e.data?e.data:[];this.renderPodium(t.slice(0,3)),this.renderList(t),this.renderUserStatus(t)}catch(e){console.warn(`Gagal memuat leaderboard saat ini:`,e.message),this.currentListContainer&&(this.currentListContainer.innerHTML=`
          <div class="p-6 text-center text-xs font-nunito text-[#6B6B55]">
            Gagal memuat papan peringkat. Silakan coba beberapa saat lagi.
          </div>
        `)}}renderPodium(e){if(!this.podiumContainer)return;if(e.length===0){this.podiumContainer.innerHTML=``;return}let t=e[0]||null,n=e[1]||null,r=e[2]||null,i=`<div class="grid grid-cols-3 gap-2 sm:gap-4 items-end pt-4 pb-2 max-w-xl mx-auto">`,a=window.translations||{},o=a.rank_1||`JUARA 1`,s=a.rank_2||`JUARA 2`,c=a.rank_3||`JUARA 3`;n?i+=`
        <div class="flex flex-col items-center card-gg p-3 sm:p-4 border-2 border-[#C0C0C0]/50 bg-[#FBFAF0]">
          <div class="relative mb-1 sm:mb-2">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-[#E2E1C4] border-2 border-[#C0C0C0] flex items-center justify-center font-baloo font-extrabold text-base sm:text-xl text-[#1F3D20] shadow-sm">
              ${n.user_name.substring(0,2).toUpperCase()}
            </div>
            <span class="absolute -top-2 -right-1 text-lg">🥈</span>
          </div>
          <span class="font-baloo font-bold text-xs sm:text-sm text-[#2D4A2E] truncate max-w-full">${n.user_name}</span>
          <span class="text-[10px] font-baloo font-extrabold text-[#6B6B55] mt-0.5">${n.exp_earned} EXP</span>
          <span class="mt-2 text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full bg-[#C0C0C0]/20 text-[#2D4A2E]">${s}</span>
        </div>
      `:i+=`<div></div>`,t?i+=`
        <div class="flex flex-col items-center card-gg p-3 sm:p-5 border-2 border-[#FFD700] bg-[#FFFDF0] transform -translate-y-2 shadow-md">
          <div class="relative mb-1 sm:mb-2">
            <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full bg-[#FFD700]/30 border-3 border-[#FFD700] flex items-center justify-center font-baloo font-extrabold text-lg sm:text-2xl text-[#1F3D20] shadow-md">
              ${t.user_name.substring(0,2).toUpperCase()}
            </div>
            <span class="absolute -top-3 -right-1 text-2xl animate-bounce">👑</span>
          </div>
          <span class="font-baloo font-extrabold text-xs sm:text-base text-[#1F3D20] truncate max-w-full">${t.user_name}</span>
          <span class="text-xs font-baloo font-extrabold text-[#D96C63] mt-0.5">${t.exp_earned} EXP</span>
          <span class="mt-2 text-[10px] font-baloo font-extrabold px-2.5 py-0.5 rounded-full bg-[#FFD700] text-[#1F3D20] shadow-xs">🥇 ${o}</span>
        </div>
      `:i+=`<div></div>`,r?i+=`
        <div class="flex flex-col items-center card-gg p-3 sm:p-4 border-2 border-[#CD7F32]/40 bg-[#FBFAF0]">
          <div class="relative mb-1 sm:mb-2">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-[#E2E1C4] border-2 border-[#CD7F32] flex items-center justify-center font-baloo font-extrabold text-base sm:text-xl text-[#1F3D20] shadow-sm">
              ${r.user_name.substring(0,2).toUpperCase()}
            </div>
            <span class="absolute -top-2 -right-1 text-lg">🥉</span>
          </div>
          <span class="font-baloo font-bold text-xs sm:text-sm text-[#2D4A2E] truncate max-w-full">${r.user_name}</span>
          <span class="text-[10px] font-baloo font-extrabold text-[#6B6B55] mt-0.5">${r.exp_earned} EXP</span>
          <span class="mt-2 text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full bg-[#CD7F32]/20 text-[#2D4A2E]">${c}</span>
        </div>
      `:i+=`<div></div>`,i+=`</div>`,this.podiumContainer.innerHTML=i}renderList(e){if(!this.currentListContainer)return;if(e.length===0){this.currentListContainer.innerHTML=`
        <div class="p-8 text-center space-y-2">
          <span class="text-3xl">🌱</span>
          <p class="font-baloo font-bold text-sm text-[#2D4A2E]">Belum Ada Perolehan EXP Minggu Ini</p>
          <p class="text-xs text-[#6B6B55]">Jadilah yang pertama menemukan tumbuhan di Peta untuk meraih posisi puncak!</p>
        </div>
      `;return}let t=`<div class="space-y-2 sm:space-y-3">`;e.forEach(e=>{let n=this.currentUserId&&e.user_id===parseInt(this.currentUserId),r=`<span class="w-7 h-7 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-xs shrink-0">${e.rank}</span>`;e.rank===1&&(r=`<span class="w-7 h-7 rounded-full bg-[#FFD700] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-xs shrink-0 shadow-xs">🥇</span>`),e.rank===2&&(r=`<span class="w-7 h-7 rounded-full bg-[#C0C0C0] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-xs shrink-0 shadow-xs">🥈</span>`),e.rank===3&&(r=`<span class="w-7 h-7 rounded-full bg-[#CD7F32] text-white flex items-center justify-center font-baloo font-extrabold text-xs shrink-0 shadow-xs">🥉</span>`),t+=`
        <div class="card-gg p-3.5 sm:p-4 flex items-center justify-between transition-all ${n?`bg-[#1F3D20] text-[#F5F4DA] border-2 border-[#E2E1C4]/40 shadow-md ring-2 ring-[#1F3D20]/20`:`bg-[#FBFAF0] text-[#2D4A2E] hover:bg-[#F5F4DA]`}">
          <div class="flex items-center gap-3 min-w-0">
            ${r}
            <div class="w-9 h-9 rounded-full ${n?`bg-[#F5F4DA] text-[#1F3D20]`:`bg-[#1F3D20] text-[#F5F4DA]`} flex items-center justify-center font-baloo font-extrabold text-xs shrink-0">
              ${e.user_name.substring(0,2).toUpperCase()}
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <span class="font-baloo font-bold text-sm truncate ${n?`text-[#F5F4DA]`:`text-[#2D4A2E]`}">
                  ${e.user_name}
                </span>
                ${n?`<span class="px-2 py-0.2 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[9px] font-baloo font-extrabold">KAMU</span>`:``}
              </div>
              <span class="text-[10px] font-nunito ${n?`text-[#F5F4DA]/80`:`text-[#6B6B55]`} capitalize">
                ${e.user_role} Guardian
              </span>
            </div>
          </div>

          <div class="text-right shrink-0">
            <span class="font-baloo font-extrabold text-sm sm:text-base ${n?`text-[#FFD700]`:`text-[#1F3D20]`}">
              +${e.exp_earned} EXP
            </span>
          </div>
        </div>
      `}),t+=`</div>`,this.currentListContainer.innerHTML=t}renderUserStatus(e){if(!this.userStatusContainer||!this.currentUserId)return;let t=e.find(e=>e.user_id===parseInt(this.currentUserId));t?this.userStatusContainer.innerHTML=`
        <div class="card-gg p-4 bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-between shadow-md border border-[#E2E1C4]/20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#F5F4DA] text-[#1F3D20] flex items-center justify-center font-baloo font-extrabold text-base">
              #${t.rank}
            </div>
            <div>
              <span class="text-[10px] font-baloo font-bold text-[#E2E1C4] uppercase tracking-wider">POSISI SAAT INI</span>
              <h4 class="font-baloo font-bold text-base text-[#F5F4DA] leading-none">${t.user_name}</h4>
            </div>
          </div>
          <div class="text-right">
            <span class="font-baloo font-extrabold text-lg text-[#FFD700]">+${t.exp_earned} EXP</span>
            <p class="text-[10px] text-[#E2E1C4] font-nunito">Minggu ini</p>
          </div>
        </div>
      `:this.userStatusContainer.innerHTML=`
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
      `}async loadLeaderboardHistory(){if(this.historyContainer){this.historyContainer.innerHTML=`
      <div class="p-6 text-center text-xs font-nunito text-[#6B6B55]">
        Memuat riwayat juara mingguan...
      </div>
    `;try{let e=await this.getLeaderboardHistory(),t=e&&e.data?e.data:[];if(t.length===0){this.historyContainer.innerHTML=`
          <div class="card-gg p-8 text-center space-y-2">
            <span class="text-3xl">🏆</span>
            <p class="font-baloo font-bold text-sm text-[#2D4A2E]">Belum Ada Riwayat Snapshot Juara</p>
            <p class="text-xs text-[#6B6B55] leading-relaxed">
              Snapshot leaderboard dihitung secara otomatis oleh sistem setiap akhir minggu (Senin 00:00).
            </p>
          </div>
        `;return}let n=`<div class="space-y-3">`;t.forEach(e=>{n+=`
          <div class="card-gg p-4 bg-[#FBFAF0] space-y-2 border border-[#1F3D20]/10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[10px] font-baloo font-extrabold">
                  Peringkat #${e.rank}
                </span>
                <span class="text-xs font-baloo font-bold text-[#6B6B55]">
                  Minggu: ${e.week_start_date} s/d ${e.week_end_date}
                </span>
              </div>
              <span class="font-baloo font-extrabold text-sm text-[#1F3D20]">
                ${e.exp_earned} EXP
              </span>
            </div>
            ${e.reward_description?`
              <p class="text-xs font-nunito text-[#2D4A2E] bg-[#E2E1C4]/40 p-2 rounded-lg font-semibold flex items-center gap-1.5">
                <span>🏅</span> ${e.reward_description}
              </p>
            `:``}
          </div>
        `}),n+=`</div>`,this.historyContainer.innerHTML=n}catch(e){console.warn(`Gagal memuat riwayat leaderboard:`,e.message),this.historyContainer.innerHTML=`
        <div class="p-6 text-center text-xs font-nunito text-red-600">
          Gagal memuat riwayat peringkat.
        </div>
      `}}}},s=class{constructor(e,t={}){this.mapContainerId=e,this.userRole=(t.userRole||`viewer`).toLowerCase(),this.map=null,this.markersGroup=null,this.userLocationMarker=null,this.userLocationCircle=null,this.userLat=null,this.userLng=null,this.lastLat=null,this.lastLng=null,this.heading=0,this.smoothedHeading=0,this.isAutoFollow=!0,this.watchId=null}async init(){if(!document.getElementById(this.mapContainerId))return;this.map=L.map(this.mapContainerId,{preferCanvas:!0,zoomControl:!0,attributionControl:!0,zoomAnimation:!0,fadeAnimation:!0,markerZoomAnimation:!0,inertia:!0,inertiaDeceleration:3e3,easeLinearity:.25}).setView([-6.1754,106.8272],17),L.tileLayer(`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`,{maxZoom:19,attribution:`&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>`}).addTo(this.map),this.markersGroup=L.layerGroup().addTo(this.map),this.userLocationGroup=L.featureGroup().addTo(this.map),this.map.on(`dragstart`,()=>{this.setAutoFollow(!1)}),this.map.on(`zoomend viewreset resize`,()=>{if(this.userLat!==null&&this.userLng!==null&&this.userLocationCircle){let e=L.latLng(this.userLat,this.userLng);this.userLocationCircle.setLatLng(e),typeof this.userLocationCircle.redraw==`function`&&this.userLocationCircle.redraw()}});let e=document.getElementById(`recenter-gps-btn`);e&&e.addEventListener(`click`,()=>{this.recenterUser()}),this.startCompassListener(),this.startGpsTracking(),await this.refreshMarkers(),window.discoverPlantFromMap=async e=>{try{let t={plant_sighting_id:e};this.userLat!==null&&this.userLng!==null&&(t.latitude=this.userLat,t.longitude=this.userLng);let n=await window.apiClient.post(`/map/sightings/${e}/claim`,t),r=n.data||n;alert(r.message||`Selamat! Spesies tumbuhan berhasil kamu temukan dan masuk ke album Seedex!`),await this.refreshMarkers()}catch(e){alert(`Gagal mengklaim temuan: `+(e.response?.data?.message||e.message))}}}updateHeading(e){if(e==null||isNaN(e))return;let t=(e-this.smoothedHeading+540)%360-180;if(Math.abs(t)<2)return;this.smoothedHeading=(this.smoothedHeading+t*.2+360)%360,this.heading=this.smoothedHeading;let n=document.querySelector(`.user-gps-heading-arrow`);n&&(n.style.transform=`rotate(${Math.round(this.heading)}deg)`)}startCompassListener(){let e=e=>{let t=null;e.webkitCompassHeading!==void 0&&e.webkitCompassHeading!==null?t=e.webkitCompassHeading:e.alpha!==void 0&&e.alpha!==null&&(t=(360-e.alpha)%360),t!==null&&!isNaN(t)&&this.updateHeading(t)};window.DeviceOrientationEvent&&(typeof DeviceOrientationEvent.requestPermission==`function`?DeviceOrientationEvent.requestPermission().then(t=>{t===`granted`&&window.addEventListener(`deviceorientation`,e,!0)}).catch(console.warn):window.addEventListener(`deviceorientationabsolute`,e,!0)||window.addEventListener(`deviceorientation`,e,!0))}setAutoFollow(e){this.isAutoFollow=e;let t=document.getElementById(`recenter-gps-label`),n=document.getElementById(`recenter-gps-btn`);t&&(t.textContent=e?`Auto-Follow On`:`Ikuti Saya`),n&&(e?(n.classList.add(`bg-[#1F3D20]`,`border-[#F5F4DA]/40`),n.classList.remove(`bg-gray-800/80`,`border-gray-500/40`)):(n.classList.remove(`bg-[#1F3D20]`,`border-[#F5F4DA]/40`),n.classList.add(`bg-gray-800/80`,`border-gray-500/40`)))}recenterUser(){this.setAutoFollow(!0),this.userLat!=null&&this.userLng!=null&&this.map&&this.map.flyTo([this.userLat,this.userLng],17,{animate:!0,duration:.8})}calculateHeading(e,t,n,r){if(e==null||t==null||n==null||r==null)return 0;let i=(r-t)*Math.PI/180,a=e*Math.PI/180,o=n*Math.PI/180,s=Math.sin(i)*Math.cos(o),c=Math.cos(a)*Math.sin(o)-Math.sin(a)*Math.cos(o)*Math.cos(i);return(Math.atan2(s,c)*180/Math.PI+360)%360}startGpsTracking(){if(!navigator.geolocation)return;let e={enableHighAccuracy:!0,timeout:15e3,maximumAge:0},t=!1,n=e=>{let n=e.coords.latitude,r=e.coords.longitude,i=e.coords.heading;if(i!==null&&!isNaN(i)&&i!==0)this.updateHeading(i);else if(this.lastLat!==null&&this.lastLng!==null){let e=this.calculateDistanceMeters(this.lastLat,this.lastLng,n,r);if(e&&e>1.2){let e=this.calculateHeading(this.lastLat,this.lastLng,n,r);this.updateHeading(e)}}if(this.userLat=n,this.userLng=r,!t&&this.map&&(this.map.setView([n,r],17),t=!0),this.addUserMarker(n,r),this.lastLat!==null&&this.lastLng!==null){let e=this.calculateDistanceMeters(this.lastLat,this.lastLng,n,r);e&&e>3&&this.refreshMarkers()}this.lastLat=n,this.lastLng=r};navigator.geolocation.getCurrentPosition(e=>{n(e),this.refreshMarkers()},e=>console.warn(`Akses lokasi GPS ditolak/tidak tersedia:`,e.message),e),this.watchId=navigator.geolocation.watchPosition(e=>{n(e)},e=>console.warn(`GPS Watch Error:`,e.message),e)}addUserMarker(e,t){if(!this.map||e==null||t==null)return;let n=this.userLat===null||this.userLng===null;this.userLat=e,this.userLng=t;let r=(window.translations||{}).gps_active||`GPS Presisi Aktif`,i=Math.round(this.heading||0),a=L.latLng(e,t),o=L.divIcon({className:`user-gps-marker`,html:`
        <div style="position:relative;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
          <!-- Pulsing Radar Outer Ring -->
          <div style="position:absolute;inset:4px;background-color:#3B82F6;border-radius:9999px;opacity:0.3;animation:ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>

          <!-- Directional Cone Arrow Pointer -->
          <div class="user-gps-heading-arrow" style="position:absolute;inset:0;transform:rotate(${i}deg);transition:transform 0.2s ease-out;pointer-events:none;display:flex;align-items:center;justify-content:center;">
            <svg viewBox="0 0 40 40" style="width:40px;height:40px;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
              <path d="M20 3 L30 22 L20 17 L10 22 Z" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round" />
            </svg>
          </div>

          <!-- Geometrically Centered Blue Dot (Exact center at [20,20]) -->
          <div style="position:relative;width:16px;height:16px;background-color:#1D4ED8;border:3px solid #FFFFFF;border-radius:9999px;box-shadow:0 2px 8px rgba(0,0,0,0.4);z-index:2;"></div>
        </div>
      `,iconSize:[40,40],iconAnchor:[20,20]});if(this.userLocationGroup||=L.featureGroup().addTo(this.map),this.userLocationCircle?this.userLocationCircle.setLatLng(a):(this.userLocationCircle=L.circle(a,{radius:50,color:`#10B981`,fillColor:`#10B981`,fillOpacity:.25,weight:3,dashArray:`5, 5`}).bindPopup(`<b style="font-family:Baloo 2,sans-serif;font-size:12px;color:#065F46;">🎯 Zona Jangkauan Klaim Spesies (50 Meter)</b>`),this.userLocationGroup.addLayer(this.userLocationCircle)),this.userLocationMarker){this.userLocationMarker.setLatLng(a);let e=document.querySelector(`.user-gps-heading-arrow`);e?e.style.transform=`rotate(${i}deg)`:this.userLocationMarker.setIcon(o)}else this.userLocationMarker=L.marker(a,{icon:o,zIndexOffset:1e3}).bindPopup(`<b style="font-family:Baloo 2,sans-serif;">📍 ${r}</b>`),this.userLocationGroup.addLayer(this.userLocationMarker);this.isAutoFollow&&this.map&&(this.lastLat===null||this.calculateDistanceMeters(this.lastLat,this.lastLng,e,t)>1.5)&&this.map.setView(a,this.map.getZoom(),{animate:!1}),n&&this.refreshMarkers()}async refreshMarkers(){if(this.markersGroup){this.markersGroup.clearLayers();try{let e=this.userLat!==null&&this.userLng!==null?`?lat=${this.userLat}&lng=${this.userLng}`:``,t=await window.apiClient.get(`/plant-sightings/nearby${e}`);(Array.isArray(t)?t:t.data||[]).forEach(e=>{this.addSightingMarker(e)})}catch(e){console.warn(`Gagal memuat marker temuan:`,e.message)}}}calculateDistanceMeters(e,t,n,r){if(e==null||t==null||n==null||r==null)return null;let i=(n-e)*Math.PI/180,a=(r-t)*Math.PI/180,o=Math.sin(i/2)*Math.sin(i/2)+Math.cos(e*Math.PI/180)*Math.cos(n*Math.PI/180)*Math.sin(a/2)*Math.sin(a/2);return 6371e3*(2*Math.atan2(Math.sqrt(o),Math.sqrt(1-o)))}addSightingMarker(e){if(!this.map||!this.markersGroup||!e.latitude||!e.longitude)return;let t=window.translations||{},n=e.species?e.species.common_name:`Tumbuhan Nyata`,r=e.species?e.species.species_code:`FLORA`,i=e.photo_url||``,a=e.sudah_ditemukan,o=[`ranger`,`admin`].includes(this.userRole),s=(t.mystery_plant||`Tanaman Misterius`).replace(/^❓\s*/,``),c=o||a?n:s,l=a?`#1F3D20`:o?`#8B6A4C`:`#D96C63`,u=`
      <div style="background-color:#FBFAF0;border:2px solid ${l};padding:4px 10px;border-radius:9999px;font-family:'Baloo 2',sans-serif;font-size:11px;font-weight:bold;color:${l};box-shadow:0 3px 8px rgba(0,0,0,0.2);white-space:nowrap;display:inline-flex;align-items:center;gap:4px;max-width:200px;box-sizing:border-box;">
        <span style="flex-shrink:0;">${a?`🌿`:o?`📍`:`❓`}</span>
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:150px;display:inline-block;">${c}</span>
      </div>
    `,d=L.divIcon({className:`gg-map-marker`,html:u,iconSize:null,iconAnchor:[60,14]}),f=``;if(this.userRole===`viewer`){let o=t.verified_badge||`Spesies Terverifikasi`,c=t.discover_button||`✨ Temukan & Klaim!`,l=t.already_discovered||`✓ Sudah Ditemukan`,u=t.unclaimed_badge||`🔒 Belum Diklaim`,d=t.unclaimed_tree||`Pohon ini belum diklaim! Tekan tombol di bawah untuk membuka dan mengklaim.`,p=this.calculateDistanceMeters(this.userLat,this.userLng,parseFloat(e.latitude),parseFloat(e.longitude)),m=``,h=!0,g=c;if(p!==null){let e=Math.round(p);e>50?(h=!1,m=`<div style="font-size:10px;color:#DC2626;font-weight:bold;margin-bottom:6px;text-align:center;background-color:#FEE2E2;padding:2px 6px;border-radius:9999px;">📍 Jarak: ${e}m (Maks 50m)</div>`,g=`🔒 Terlalu Jauh (${e}m > 50m)`):m=`<div style="font-size:10px;color:#16A34A;font-weight:bold;margin-bottom:6px;text-align:center;background-color:#DCFCE7;padding:2px 6px;border-radius:9999px;">📍 Jarak: ${e}m (Dalam Jangkauan)</div>`}else m=`<div style="font-size:10px;color:#D97706;font-weight:bold;margin-bottom:6px;text-align:center;background-color:#FEF3C7;padding:2px 6px;border-radius:9999px;">📍 Aktifkan GPS untuk mengklaim (Maks 50m)</div>`;f=`
        <div style="font-family:Nunito,sans-serif;max-width:220px;color:#2A2A22;padding:4px;box-sizing:border-box;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="background-color:#E2E1C4;color:#1F3D20;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${a?r:`MYSTERY`}</span>
            <span style="font-size:10px;color:#6B6B55;font-weight:bold;">${a?`✓ `+o:u}</span>
          </div>

          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:2px 0 6px 0;color:#1F3D20;line-height:1.2;word-break:break-word;">
            ${a?n:`❓ `+s}
          </h4>

          ${a?i?`<img src="${i}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:8px;border:1.5px solid rgba(31,61,32,0.15);"/>`:``:`<div style="background-color:rgba(226,225,196,0.4);border:1.5px dashed #8B6A4C;border-radius:12px;padding:10px 12px;text-align:center;margin-bottom:8px;font-size:11px;color:#6B6B55;font-style:italic;word-break:break-word;overflow-wrap:break-word;box-sizing:border-box;">
                ${d}
               </div>`}

          ${a?``:m}

          ${a?`<button disabled style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:7px 0;border-radius:9999px;border:none;cursor:default;">${l}</button>`:h?`<button id="discover-btn-${e.id}" onclick="window.discoverPlantFromMap(${e.id})" style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:7px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 8px rgba(0,0,0,0.2);">${c}</button>`:`<button disabled style="width:100%;background-color:#9CA3AF;color:#FFFFFF;font-family:Baloo 2;font-weight:bold;font-size:11px;padding:7px 0;border-radius:9999px;border:none;cursor:not-allowed;box-shadow:none;">${g}</button>`}
        </div>
      `}else{let r=t.edit_data_button||`✏️ Edit Data Tumbuhan`,a=t.status_label||`Status`;f=`
        <div style="font-family:Nunito,sans-serif;max-width:210px;color:#2A2A22;padding:4px;box-sizing:border-box;">
          <span style="background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${this.userRole.toUpperCase()} SIGHTING</span>
          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:4px 0;color:#1F3D20;word-break:break-word;">${n}</h4>
          ${i?`<img src="${i}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:6px;"/>`:``}
          <p style="font-size:11px;color:#6B6B55;margin:0 0 6px 0;">${a}: <strong>${e.verification_status}</strong></p>
          <button onclick="window.openEditSightingModal(${e.id})" style="width:100%;background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2,sans-serif;font-weight:bold;font-size:12px;padding:6px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 6px rgba(0,0,0,0.15);">
            ${r}
          </button>
        </div>
      `}L.marker([e.latitude,e.longitude],{icon:d}).addTo(this.markersGroup).bindPopup(f)}},c=class{constructor(e={}){this.containerElement=e.containerElement||document.querySelector(`#shop-container`),this.catalog=[],this.inventory=[],this.userCoin=0,this.activeCategory=`all`,this.currentAvatar=`default`}async init(){await this.loadShopData(),this.bindEvents()}async loadShopData(){try{let t=await e.get(`/shop`);this.catalog=t.catalog||[],this.inventory=t.inventory||[],this.userCoin=t.user_coin||0,this.currentAvatar=t.current_avatar||`default`,typeof window.updateUserCoin==`function`&&window.updateUserCoin(this.userCoin),this.render()}catch(e){console.error(`Gagal memuat data Shop:`,e),this.containerElement&&(this.containerElement.innerHTML=`
          <div class="text-center py-12 text-[#6B6B55]">
            <p>Gagal memuat katalog toko. Silakan coba lagi nanti.</p>
          </div>
        `)}}bindEvents(){this.containerElement&&this.containerElement.addEventListener(`click`,async e=>{let t=e.target.closest(`[data-category]`);if(t){this.activeCategory=t.dataset.category,this.renderCategoryFilters(),this.renderItems();return}let n=e.target.closest(`.buy-btn`);if(n){let e=n.dataset.itemCode;await this.buyItem(e,n);return}let r=e.target.closest(`.equip-avatar-btn`);if(r){let e=r.dataset.avatarKey;await this.equipAvatar(e,r)}})}async buyItem(t,n){if(!t||n.disabled)return;let r=n.innerHTML;n.disabled=!0,n.innerHTML=`<span class="animate-spin inline-block mr-1">⏳</span> Membeli...`;try{let n=await e.post(`/shop/buy`,{item_code:t});this.userCoin=n.user_coin,typeof window.updateUserCoin==`function`&&window.updateUserCoin(this.userCoin);let r=n.inventory_item,i=this.inventory.findIndex(e=>e.item_code===t);i>=0?this.inventory[i]=r:this.inventory.push(r),this.showToast(n.message||`Pembelian berhasil!`,`success`),this.render()}catch(e){let t=e.response?.data?.message||e.message||`Gagal membeli item.`;this.showToast(t,`error`),n.disabled=!1,n.innerHTML=r}}async equipAvatar(t,n){if(!t||n.disabled)return;let r=n.innerHTML;n.disabled=!0,n.innerHTML=`<span class="animate-spin inline-block mr-1">⏳</span>...`;try{let n=await e.post(`/shop/equip-avatar`,{avatar_code:t});this.currentAvatar=n.current_avatar,this.showToast(n.message||`Foto profil diperbarui!`,`success`),this.render()}catch(e){let t=e.response?.data?.message||e.message||`Gagal memperbarui foto profil.`;this.showToast(t,`error`),n.disabled=!1,n.innerHTML=r}}showToast(e,t=`success`){typeof window.showToast==`function`?window.showToast(e,t):alert(e)}renderCategoryFilters(){}renderItems(){let e=document.querySelector(`#shop-items-grid`);if(!e)return;let t=this.catalog;if(t.length===0){e.innerHTML=`
        <div class="col-span-full text-center py-12 text-[#6B6B55]">
          <p class="font-baloo font-bold text-base">${(window.translations||{}).out_of_stock||`Stok Habis`}</p>
        </div>
      `;return}e.innerHTML=t.map(e=>{let t=this.inventory.find(t=>t.item_code===e.item_code||t.item_code===e.avatar_key),n=t?t.quantity:0,r=this.userCoin>=e.price,i=e.item_type===`avatar`&&this.currentAvatar===e.avatar_key;return`
        <div class="card-gg card-gg-hover p-5 flex flex-col justify-between relative group">
          <!-- Top Badge & Owned Counter -->
          <div>
            <div class="flex items-center justify-between mb-3">
              ${e.item_type===`avatar`&&e.image?`
                <div class="w-14 h-14 rounded-full border-2 border-[#1F3D20] p-0.5 bg-[#FBFAF0] shadow-xs overflow-hidden shrink-0">
                  <img src="${e.image}" alt="${e.name}" class="w-full h-full object-cover rounded-full" />
                </div>
              `:`
                <span class="text-3xl p-2 rounded-2xl bg-[#E7E6BE]/60 border border-[#1F3D20]/10 shadow-xs inline-block">
                  ${e.icon}
                </span>
              `}

              ${i?`
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-700 text-[#F5F4DA] text-[10px] font-baloo font-extrabold shadow-xs">
                  ✓ Dipakai
                </span>
              `:n>0?`
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-[10px] font-baloo font-extrabold shadow-xs">
                  Dimiliki
                </span>
              `:`
                <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#6B6B55] text-[10px] font-baloo font-extrabold">
                  Baru
                </span>
              `}
            </div>

            <h3 class="font-baloo font-bold text-base text-[#1F3D20] mb-1">
              ${e.name}
            </h3>

            <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed mb-2">
              ${e.description}
            </p>

            <div class="flex flex-wrap gap-1.5 mb-4">
              ${e.item_type===`avatar`?`
                <span class="px-2 py-0.5 rounded-md bg-[#D4E6C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  🖼️ Foto Profil Eksklusif
                </span>
              `:``}

              ${e.item_type===`seed`&&e.growth_duration_minutes?`
                <span class="px-2 py-0.5 rounded-md bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ⏱️ ${e.growth_duration_minutes}m Tumbuh
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#D4E6C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ✨ +${e.exp_reward} EXP
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FEF0C7] text-[#8B5A2B] text-[10px] font-baloo font-bold flex items-center gap-1">
                  ${typeof window.getNcIconSvg==`function`?window.getNcIconSvg(`w-3.5 h-3.5`):`🪙`} +${e.coin_reward} NC
                </span>
              `:``}

              ${e.item_type===`tool`&&e.time_reduction_minutes?`
                <span class="px-2 py-0.5 rounded-md bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ⏱️ Potong -${e.time_reduction_minutes}m
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FADBD8] text-[#78281F] text-[10px] font-baloo font-bold">
                  🧪 Sekali Pakai (Kebun)
                </span>
              `:``}

              ${e.item_type===`tool`&&e.discount_percent?`
                <span class="px-2 py-0.5 rounded-md bg-[#FEF0C7] text-[#8B5A2B] text-[10px] font-baloo font-bold">
                  ⛏️ Diskon ${e.discount_percent}% Lahan
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  📜 Alat Permanen (Kebun)
                </span>
              `:``}

              ${e.item_type===`material`?`
                <span class="px-2 py-0.5 rounded-md bg-[#D4E6C4] text-[#1F3D20] text-[10px] font-baloo font-bold">
                  ✨ +${e.exp_reward||50} EXP Bonus
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FEF0C7] text-[#8B5A2B] text-[10px] font-baloo font-bold flex items-center gap-1">
                  ${typeof window.getNcIconSvg==`function`?window.getNcIconSvg(`w-3.5 h-3.5`):`🪙`} +${e.coin_reward} NC
                </span>
              `:``}
            </div>
          </div>

          <!-- Price & Action Button -->
          <div class="pt-4 border-t border-[#1F3D20]/10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 font-baloo font-bold text-sm text-[#1F3D20]">
              ${typeof window.getNcIconSvg==`function`?window.getNcIconSvg(`w-4 h-4`):`🪙`}
              <span>${e.price}</span>
              <span class="text-[10px] text-[#6B6B55]">NC</span>
            </div>

            ${e.item_type===`avatar`&&n>0?`
              ${i?`
                <button disabled class="btn-gg-secondary opacity-80 text-xs py-1.5 px-4 cursor-default">
                  ✓ Dipakai
                </button>
              `:`
                <button 
                  data-avatar-key="${e.avatar_key}"
                  class="equip-avatar-btn btn-gg-secondary text-xs py-1.5 px-4 cursor-pointer"
                >
                  Gunakan
                </button>
              `}
            `:`
              <button 
                data-item-code="${e.item_code}"
                class="buy-btn btn-gg-primary text-xs py-1.5 px-4 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${r?``:`bg-[#8B6A4C] opacity-60`}"
                ${r?``:`title="Coin tidak cukup"`}
              >
                ${r?`Beli Item`:`Coin Kurang`}
              </button>
            `}
          </div>
        </div>
      `}).join(``)}render(){this.renderCategoryFilters(),this.renderItems()}},l=class{constructor(){this.friends=[],this.incomingRequests=[],this.itemRequests=[],this.inventory=[]}async init(){await this.loadData(),this.bindEvents()}async loadData(){try{let t=await e.get(`/friends`);(t.success||t.friends)&&(this.friends=t.friends||[],this.incomingRequests=t.incoming_requests||[],this.itemRequests=t.item_requests||[],this.inventory=t.inventory||[],this.renderFriendsList(),this.renderIncomingRequests(),this.renderItemRequests())}catch(e){console.warn(`[FriendsModule] Gagal memuat data aliansi teman:`,e)}}bindEvents(){let e=document.querySelector(`#friend-search-input`);if(e){let t=null;e.addEventListener(`input`,e=>{clearTimeout(t),t=setTimeout(()=>this.searchUsers(e.target.value),350)})}}renderFriendsList(){let e=document.querySelector(`#friends-list-container`);if(e){if(this.friends.length===0){e.innerHTML=`
        <div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">
          Belum ada teman dalam Aliansi Anda. Klik "+ Tambah Aliansi" untuk memperluas koneksi!
        </div>
      `;return}e.innerHTML=this.friends.map(e=>`
      <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-sm flex items-center justify-center shrink-0">
            ${e.name?e.name.substring(0,2).toUpperCase():`US`}
          </div>
          <div>
            <span class="font-baloo font-bold text-sm text-[#1F3D20] block leading-snug">${e.name}</span>
            <span class="text-[11px] text-[#6B6B55] block font-nunito">Lvl ${e.level||1} • ${e.email}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
          <button onclick="window.friendsApp.openRequestItemModal(${e.id}, '${e.name}')" class="px-3 py-1.5 rounded-xl bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer flex items-center gap-1 shadow-xs">
            <span>📩</span>
            <span>Minta Barang</span>
          </button>

          <button onclick="window.friendsApp.openGiftItemModal(${e.id}, '${e.name}')" class="px-3 py-1.5 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer flex items-center gap-1 shadow-xs">
            <span>🎁</span>
            <span>Beri Barang</span>
          </button>
        </div>
      </div>
    `).join(``)}}renderIncomingRequests(){let e=document.querySelector(`#incoming-requests-container`),t=document.querySelector(`#incoming-requests-count`);if(e){if(t&&(t.textContent=this.incomingRequests.length,t.style.display=this.incomingRequests.length>0?`inline-block`:`none`),this.incomingRequests.length===0){e.innerHTML=`
        <div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">
          Tidak ada permintaan pertemanan masuk.
        </div>
      `;return}e.innerHTML=this.incomingRequests.map(e=>`
      <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 flex items-center justify-between gap-3">
        <div>
          <span class="font-baloo font-bold text-xs text-[#1F3D20] block">${e.name}</span>
          <span class="text-[10px] text-[#6B6B55] block">${e.email} • ${e.created_at}</span>
        </div>
        <div class="flex items-center gap-1.5">
          <button onclick="window.friendsApp.acceptFriend(${e.requester_id})" class="px-2.5 py-1 rounded-lg bg-[#27AE60] text-white font-baloo font-bold text-xs hover:bg-[#219653] transition-colors cursor-pointer">
            Terima
          </button>
          <button onclick="window.friendsApp.removeFriend(${e.requester_id})" class="px-2 py-1 rounded-lg bg-gray-200 text-gray-700 font-baloo font-bold text-xs hover:bg-gray-300 transition-colors cursor-pointer">
            Tolak
          </button>
        </div>
      </div>
    `).join(``)}}renderItemRequests(){let e=document.querySelector(`#incoming-item-requests-container`);if(e){if(this.itemRequests.length===0){e.style.display=`none`;return}e.style.display=`block`,e.innerHTML=`
      <div class="p-4 rounded-2xl bg-[#FFD700]/15 border border-[#FFD700]/40 space-y-3">
        <h4 class="font-baloo font-extrabold text-sm text-[#1F3D20] flex items-center gap-1.5">
          <span>📩</span>
          <span>Permintaan Barang Shop dari Teman (${this.itemRequests.length})</span>
        </h4>
        <div class="space-y-2">
          ${this.itemRequests.map(e=>`
            <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 flex items-center justify-between gap-3">
              <div>
                <span class="font-baloo font-bold text-xs text-[#1F3D20] block">${e.sender_name} meminta item:</span>
                <span class="font-baloo font-extrabold text-xs text-[#8B6A4C] block">📦 ${e.item_code}</span>
                ${e.note?`<span class="text-[10px] text-[#6B6B55] italic block">"${e.note}"</span>`:``}
              </div>
              <button onclick="window.friendsApp.giftItem(${e.sender_id}, '${e.item_code}', ${e.id})" class="px-3 py-1.5 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer shadow-xs">
                Kirimkan Barang
              </button>
            </div>
          `).join(``)}
        </div>
      </div>
    `}}async searchUsers(t){let n=document.querySelector(`#search-results-container`);if(n){if(!t||t.trim().length<2){n.innerHTML=`<div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">Ketik nama atau email untuk mencari pengguna...</div>`;return}n.innerHTML=`<div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">Mencari pengguna...</div>`;try{let r=(await e.get(`/friends/search?q=${encodeURIComponent(t)}`)).results||[];if(r.length===0){n.innerHTML=`<div class="text-center py-4 text-[#6B6B55] font-nunito text-xs italic">Tidak ada pengguna yang cocok ditemukan.</div>`;return}n.innerHTML=r.map(e=>`
        <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 flex items-center justify-between gap-3">
          <div>
            <span class="font-baloo font-bold text-xs text-[#1F3D20] block">${e.name}</span>
            <span class="text-[10px] text-[#6B6B55] block">${e.email} • Lvl ${e.level}</span>
          </div>
          <div>
            ${e.friendship_status===`accepted`?`<span class="text-[10px] font-baloo font-bold text-[#27AE60] bg-[#27AE60]/10 px-2 py-0.5 rounded-full">✓ Teman</span>`:e.friendship_status===`pending`?`<span class="text-[10px] font-baloo font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">${e.is_requester?`Menunggu Konfirmasi`:`Permintaan Masuk`}</span>`:`<button onclick="window.friendsApp.sendFriendRequest(${e.id})" class="px-3 py-1 rounded-lg bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                    + Tambah Teman
                   </button>`}
          </div>
        </div>
      `).join(``)}catch{n.innerHTML=`<div class="text-center py-4 text-red-500 font-nunito text-xs">Gagal mencari pengguna.</div>`}}}async sendFriendRequest(t){try{let n=await e.post(`/friends/add`,{friend_id:t});alert(n.message||`Permintaan pertemanan berhasil dikirim!`),await this.loadData();let r=document.querySelector(`#friend-search-input`);r&&r.value&&this.searchUsers(r.value)}catch(e){alert(e.response?.data?.message||e.message||`Gagal mengirim permintaan pertemanan.`)}}async acceptFriend(t){try{let n=await e.post(`/friends/accept`,{requester_id:t});alert(n.message||`Permintaan pertemanan diterima!`),await this.loadData()}catch(e){alert(e.response?.data?.message||e.message||`Gagal menerima pertemanan.`)}}async removeFriend(t){try{let n=await e.post(`/friends/remove`,{friend_id:t});alert(n.message||`Berhasil memperbarui pertemanan.`),await this.loadData()}catch(e){alert(e.response?.data?.message||e.message||`Gagal memperbarui pertemanan.`)}}openRequestItemModal(e,t){document.querySelector(`#req-friend-id`).value=e,document.querySelector(`#req-friend-name`).textContent=t,document.querySelector(`#request-item-modal`).classList.remove(`hidden`)}closeRequestItemModal(){document.querySelector(`#request-item-modal`).classList.add(`hidden`)}async submitItemRequest(t){t.preventDefault();let n=document.querySelector(`#req-friend-id`).value,r=document.querySelector(`#req-item-code`).value,i=document.querySelector(`#req-note`).value,a=document.querySelector(`#req-submit-btn`);a.disabled=!0;try{let t=await e.post(`/friends/request-item`,{friend_id:n,item_code:r,note:i});alert(t.message||`Permintaan barang shop berhasil dikirim!`),this.closeRequestItemModal(),await this.loadData()}catch(e){alert(e.response?.data?.message||e.message||`Gagal mengirim permintaan barang.`)}finally{a.disabled=!1}}openGiftItemModal(e,t){document.querySelector(`#gift-friend-id`).value=e,document.querySelector(`#gift-friend-name`).textContent=t;let n=document.querySelector(`#gift-item-code`);this.inventory.length===0?n.innerHTML=`<option value="" disabled selected>Inventaris Anda kosong (Beli item di Shop dulu)</option>`:n.innerHTML=this.inventory.map(e=>`
        <option value="${e.item_code}">${e.item_code} (Dimiliki: x${e.quantity})</option>
      `).join(``),document.querySelector(`#gift-item-modal`).classList.remove(`hidden`)}closeGiftItemModal(){document.querySelector(`#gift-item-modal`).classList.add(`hidden`)}async submitGiftItem(t){t.preventDefault();let n=document.querySelector(`#gift-friend-id`).value,r=document.querySelector(`#gift-item-code`).value,i=document.querySelector(`#gift-submit-btn`);if(!r){alert(`Pilih barang shop yang akan diberikan.`);return}i.disabled=!0;try{let t=await e.post(`/friends/gift-item`,{friend_id:n,item_code:r});alert(t.message||`Barang shop berhasil dikirimkan ke teman!`),this.closeGiftItemModal(),await this.loadData()}catch(e){alert(e.response?.data?.message||e.message||`Gagal mengirimkan barang.`)}finally{i.disabled=!1}}async giftItem(t,n,r=null){try{let i=await e.post(`/friends/gift-item`,{friend_id:t,item_code:n,request_id:r});alert(i.message||`Barang shop berhasil dikirimkan ke teman!`),await this.loadData()}catch(e){alert(e.response?.data?.message||e.message||`Gagal mengirimkan barang.`)}}};window.apiClient=e,window.ArScanner=t,window.GalleryModule=n,window.MiniGameModule=r,window.HomeModule=i,window.ThreeDCardTilt=a,window.LeaderboardManager=o,window.MapManager=s,window.ShopModule=c,window.FriendsModule=l,window.showToast=function(e,t=`success`){let n=document.querySelector(`#toast-container`);n||(n=document.createElement(`div`),n.id=`toast-container`,n.className=`fixed top-20 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-auto pointer-events-none`,document.body.appendChild(n));let r=document.createElement(`div`);r.className=`pointer-events-auto px-4 py-3 rounded-2xl shadow-xl font-baloo font-bold text-sm transition-all duration-300 transform translate-y-2 opacity-0 flex items-center gap-2 border ${t===`success`?`bg-[#1F3D20] text-[#F5F4DA] border-[#E2E1C4]/30`:`bg-[#C0392B] text-white border-red-400/30`}`,r.innerHTML=`
    <span class="text-base">${t===`success`?`✨`:`⚠️`}</span>
    <span class="leading-tight">${e}</span>
  `,n.appendChild(r),requestAnimationFrame(()=>{r.classList.remove(`translate-y-2`,`opacity-0`),r.classList.add(`translate-y-0`,`opacity-100`)}),setTimeout(()=>{r.classList.remove(`opacity-100`,`translate-y-0`),r.classList.add(`opacity-0`,`-translate-y-2`),setTimeout(()=>{r.remove(),n.children.length===0&&n.remove()},300)},3500)},window.updateUserCoin=function(e){document.querySelectorAll(`#user-coin, #shop-user-coin`).forEach(t=>{t.textContent=e})},window.updateUserExp=function(e){document.querySelectorAll(`#user-exp, #shop-user-exp`).forEach(t=>{t.textContent=e})},window.getNcIconSvg=function(e=`w-4 h-4`){return`<svg class="${e} shrink-0 inline-block align-middle" viewBox="0 0 24 24" fill="none">
    <circle cx="12" cy="12" r="10" fill="#F4C430" stroke="#B8860B" stroke-width="1.5"/>
    <circle cx="12" cy="12" r="7.5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
    <path d="M12 6.5c-3 3.5-3.5 7.5-1.2 10.5 3-3.5 3.5-7.5 1.2-10.5z" fill="#1F3D20"/>
    <path d="M12 6.5c3 3.5 3.5 7.5 1.2 10.5-3-3.5-3.5-7.5-1.2-10.5z" fill="#27AE60"/>
  </svg>`};