// assets/js/dashboard.js
// Robust dashboard JS - attach handlers reliably and expose openBooking.
// Replace your current file with this. Use browser Console to view logs.

(function(){
  'use strict';

  // Surface uncaught errors to console clearly
  window.addEventListener('error', (ev) => {
    console.error('Uncaught error:', ev.message, 'at', ev.filename + ':' + ev.lineno + ':' + ev.colno);
  });
  window.addEventListener('unhandledrejection', (ev) => {
    console.error('Unhandled promise rejection:', ev.reason);
  });

  document.addEventListener('DOMContentLoaded', () => {
    console.log('dashboard.js loaded at', new Date().toISOString());

    // Element refs (may be null)
    const grid = document.getElementById('units');
    const empty = document.getElementById('empty');
    const selLoc = document.getElementById('filterLocation');
    const minR = document.getElementById('filterMinPrice');
    const maxR = document.getElementById('filterMaxPrice');
    const minLbl = document.getElementById('minPriceLabel');
    const maxLbl = document.getElementById('maxPriceLabel');
    const addBtn = document.getElementById('addUnitBtn');

    // Unit modal refs
    const modal = document.getElementById('unitModal');
    const modalTitle = document.getElementById('unitModalTitle');
    const modalClose = document.getElementById('unitModalClose');
    const form = document.getElementById('unitForm');
    const cancelBtn = document.getElementById('unitCancel');
    const submitBtn = document.getElementById('unitSubmit');
    const imgInput = form?.querySelector('input[name="image"]');
    const imgPreview = document.getElementById('imgPreview');

    // View modal refs
    const viewModal = document.getElementById('viewModal');
    const viewClose = document.getElementById('viewClose');
    const viewImg = document.getElementById('viewImg');
    const viewTitle = document.getElementById('viewTitle');
    const viewLocation = document.getElementById('viewLocation');
    const viewPrice = document.getElementById('viewPrice');
    const viewCapacity = document.getElementById('viewCapacity');
    const viewDesc = document.getElementById('viewDesc');
    const viewLocationLinkWrap = document.getElementById('viewLocationLinkWrap');
    const viewLocationLink = document.getElementById('viewLocationLink');
    const viewBookBtn = document.getElementById('viewBookBtn');
    const viewCloseBtn = document.getElementById('viewCloseBtn');

    // Booking modal refs
    const bookingModal = document.getElementById('bookingModal');
    const bookingForm = document.getElementById('bookingForm');
    const bookingClose = document.getElementById('bookingClose');
    const bookingCancel = document.getElementById('bookingCancel');
    const bookingSubmit = document.getElementById('bookingSubmit');
    const bookingRoomId = document.getElementById('bookingRoomId');
    const bookingStart = document.getElementById('bookingStart');
    const bookingEnd = document.getElementById('bookingEnd');
    const bookingGuests = document.getElementById('bookingGuests');
    const bookingError = document.getElementById('bookingError');

    // Payment modal refs
    const paymentModal = document.getElementById('paymentModal');
    const paymentForm = document.getElementById('paymentForm');
    const paymentClose = document.getElementById('paymentClose');
    const paymentCancel = document.getElementById('paymentCancel');
    const paymentBookingId = document.getElementById('paymentBookingId');
    const paymentError = document.getElementById('paymentError');
    const paymentSubmit = document.getElementById('paymentSubmit');

    // state
    let allRooms = [];
    let canManage = false;
    window.currentRoomId = window.currentRoomId || null; // global fallback

    // helpers
    const show = el => el && el.classList && el.classList.remove('hidden');
    const hide = el => el && el.classList && el.classList.add('hidden');
    const escHtml = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    const escAttr = s => String(s ?? '').replace(/"/g,'&quot;');

    // attach a safe Book Now handler in case viewBookBtn is added later
    function ensureBookNowHandler() {
      const btn = document.getElementById('viewBookBtn');
      if (!btn) {
        // try again later in 200ms (in case HTML was rendered after)
        setTimeout(ensureBookNowHandler, 200);
        return;
      }
      // remove duplicate handlers if any by cloning
      const newBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newBtn, btn);
      newBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = window.currentRoomId || (viewModal && viewModal.dataset && viewModal.dataset.roomId) || null;
        if (!id) {
          alert('Room not selected. Click View on a room first.');
          return;
        }
        // close view modal for UX if open
        viewModal?.classList?.add('hidden');
        openBooking(id);
      });
      console.log('viewBookBtn handler attached.');
    }
    ensureBookNowHandler();

    // Image preview (optional)
    if (imgInput) {
      imgInput.addEventListener('change', () => {
        const file = imgInput.files?.[0];
        if (file) {
          imgPreview.src = URL.createObjectURL(file);
          show(imgPreview);
        } else {
          imgPreview.src = '';
          hide(imgPreview);
        }
      });
    }

    // Auth + load rooms
    (async function checkAuthAndLoad() {
      try {
        const r = await fetch('api/me.php', { credentials: 'include' });
        const payload = await r.json();
        const user = payload?.user || null;
        if (!user) {
          console.warn('Not authenticated, redirecting to login');
          window.location.href = 'login.html';
          return;
        }
        canManage = ['admin','owner','manager'].includes((user.role_name||'').toLowerCase());
        if (addBtn && !canManage) addBtn.style.display = 'none';
        await loadRooms();
      } catch (err) {
        console.error('Auth/load error', err);
        // still try to load rooms even if auth check fails
        await loadRooms().catch(()=>{});
      }
    })();

    async function loadRooms() {
      if (empty) hide(empty);
      if (grid) grid.innerHTML = '';
      try {
        const q = new URLSearchParams({
          location: selLoc?.value || '',
          min: minR?.value || '',
          max: maxR?.value || ''
        });
        const res = await fetch(`api/rooms_list.php?${q.toString()}`, { credentials: 'include' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to load rooms');
        allRooms = data.rooms || [];
        buildLocationFilter(allRooms);
        render();
      } catch (err) {
        console.error('loadRooms error', err);
        if (empty) { empty.textContent = 'Failed to load rooms.'; show(empty); }
      }
    }

    function buildLocationFilter(rooms) {
      if (!selLoc) return;
      const unique = Array.from(new Set(rooms.map(r => (r.location || '').trim()).filter(Boolean))).sort((a,b)=>a.localeCompare(b));
      selLoc.innerHTML = '<option value="">All locations</option>' + unique.map(loc => `<option value="${escAttr(loc)}">${escHtml(loc)}</option>`).join('');
    }

    function render() {
      if (minLbl) minLbl.textContent = minR?.value ?? '';
      if (maxLbl) maxLbl.textContent = maxR?.value ?? '';
      const min = Number(minR?.value || 0);
      const max = Number(maxR?.value || Infinity);
      const loc = selLoc?.value || '';

      const list = allRooms.filter(r => {
        const price = r.price == null ? null : Number(r.price);
        const okLoc = loc === '' || (r.location || '') === loc;
        const okPrice = price == null || (price >= min && price <= max);
        return okLoc && okPrice;
      });

      if (!list.length) {
        if (grid) grid.innerHTML = '';
        if (empty) { empty.textContent = 'No units found.'; show(empty); }
        return;
      }
      hide(empty);

      if (!grid) return;
      grid.innerHTML = list.map(r => {
        const title = r.title || '(Untitled)';
        const loc = r.location || '';
        const price = r.price != null ? Number(r.price) : '';
        const cap = r.capacity != null ? r.capacity : '';
        const img = r.image || '';
        const id = r.id;
        return `
          <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
            ${img ? `<img src="${escAttr(img)}" alt="${escAttr(title)}" class="h-40 w-full object-cover">` : `<div class="h-40 w-full bg-gray-200"></div>`}
            <div class="p-4">
              <h3 class="font-semibold text-gray-900">${escHtml(title)}</h3>
              <p class="text-sm text-gray-600">${escHtml(loc)}</p>
              <div class="mt-2 flex items-center justify-between text-sm text-gray-700">
                <span>${price !== '' ? '₱ ' + price.toLocaleString() : ''}</span>
                <span>${cap !== '' ? cap + ' pax' : ''}</span>
              </div>
              <div class="mt-3 grid gap-2 ${canManage ? 'grid-cols-3' : 'grid-cols-2'}">
                <button data-view="${id}" class="px-3 py-2 rounded bg-blue-700 text-white text-sm hover:bg-blue-800">View</button>
                ${canManage ? `
                  <button data-edit="${id}" class="px-3 py-2 rounded border text-sm hover:bg-gray-50">Edit</button>
                  <button data-del="${id}" class="px-3 py-2 rounded border text-sm hover:bg-red-50">Delete</button>
                ` : `<button data-book="${id}" class="px-3 py-2 rounded bg-green-600 text-white text-sm hover:bg-green-700">Book Now</button>`}
              </div>
            </div>
          </div>
        `;
      }).join('');

      // attach handlers
      grid.querySelectorAll('[data-view]').forEach(btn => btn.addEventListener('click', () => openView(btn.dataset.view)));
      grid.querySelectorAll('[data-book]').forEach(btn => btn.addEventListener('click', () => openBooking(btn.dataset.book)));
      if (canManage) {
        grid.querySelectorAll('[data-edit]').forEach(btn => btn.addEventListener('click', () => openEdit(btn.dataset.edit)));
        grid.querySelectorAll('[data-del]').forEach(btn => btn.addEventListener('click', () => doDelete(btn.dataset.del)));
      }
    }

    // filters
    [selLoc, minR, maxR].forEach(el => el && el.addEventListener('input', render));

    // logout
    document.getElementById('logoutBtn')?.addEventListener('click', async () => { try { await fetch('api/logout.php', { credentials: 'include' }); } catch{} window.location.href = 'login.html';});
    document.getElementById('logoutBtnMobile')?.addEventListener('click', async () => { try { await fetch('api/logout.php', { credentials: 'include' }); } catch{} window.location.href = 'login.html';});

    // unit modal (create/edit)
    addBtn?.addEventListener('click', () => openCreate());
    modalClose?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    function openCreate(){
      if (!form) return;
      modalTitle.textContent = 'Add Unit';
      form.reset(); form.id.value = ''; form.existing_image_path.value = ''; resetImagePreview(''); document.querySelector('[name="location_link"]').value = ''; submitBtn.textContent = 'Create';
      modal.classList.remove('hidden'); modal.classList.add('flex');
    }
    function openEdit(id){
      const r = allRooms.find(x=>String(x.id)===String(id)); if(!r) return;
      modalTitle.textContent='Edit Unit'; form.reset(); form.id.value=r.id||''; form.title.value=r.title||''; form.location.value=r.location||''; form.capacity.value=r.capacity||''; form.price_per_night.value=r.price ?? ''; form.existing_image_path.value=r.image||''; form.description.value=r.description||''; document.querySelector('[name="location_link"]').value=r.location_link||''; resetImagePreview(r.image||''); submitBtn.textContent='Update'; modal.classList.remove('hidden'); modal.classList.add('flex');
    }
    function closeModal(){ modal?.classList.add('hidden'); modal?.classList.remove('flex'); }

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      submitBtn.disabled = true;
      const fd = new FormData(form);
      const isEdit = !!fd.get('id');
      const url = isEdit ? 'api/room_update.php' : 'api/room_create.php';
      try {
        const res = await fetch(url, { method: 'POST', body: fd, credentials: 'include' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message||'Save failed');
        closeModal(); await loadRooms();
      } catch (err) { alert(err.message||err); } finally { submitBtn.disabled = false; }
    });

    async function doDelete(id){
      if(!confirm('Delete this unit?')) return;
      try {
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch('api/room_delete.php', { method:'POST', body:fd, credentials:'include' });
        const data = await res.json();
        if(!data.success) throw new Error(data.message||'Delete failed');
        await loadRooms();
      } catch(e){ alert(e.message||e); }
    }

    // view modal & booking
    function openView(id){
      const r = allRooms.find(x=>String(x.id)===String(id)); if(!r) return;
      viewImg && (viewImg.src = r.image || '');
      viewTitle && (viewTitle.textContent = r.title || '(Untitled)');
      viewLocation && (viewLocation.textContent = r.location ? `📍 ${r.location}` : '');
      viewPrice && (viewPrice.textContent = (r.price != null) ? `₱ ${Number(r.price).toLocaleString()}` : '');
      viewCapacity && (viewCapacity.textContent = (r.capacity != null) ? `• ${r.capacity} pax` : '');
      viewDesc && (viewDesc.textContent = r.description || '');
      window.currentRoomId = r.id;
      if (viewModal) viewModal.dataset.roomId = r.id;
      if (r.location_link && /^https?:\/\//i.test(r.location_link)) { if(viewLocationLink){ viewLocationLink.href = r.location_link; show(viewLocationLinkWrap); } }
      else { if(viewLocationLink){ viewLocationLink.href = '#'; hide(viewLocationLinkWrap); } }
      viewModal?.classList.remove('hidden'); viewModal?.classList.add('flex');
    }

    function closeView(){ window.currentRoomId = null; if(viewModal) viewModal.dataset.roomId = ''; viewModal?.classList.add('hidden'); viewModal?.classList.remove('flex'); }
    viewClose?.addEventListener('click', closeView);
    viewCloseBtn?.addEventListener('click', closeView);
    viewModal?.addEventListener('click', (e)=>{ if(e.target===viewModal) closeView(); });

    // booking modal (exposed for quick fixes)
    window.openBooking = function(roomId, defaultStart=null, defaultEnd=null){
      if(!bookingRoomId) return;
      bookingRoomId.value = roomId;
      if(bookingStart) bookingStart.value = defaultStart || '';
      if(bookingEnd) bookingEnd.value = defaultEnd || '';
      if(bookingGuests) bookingGuests.value = 1;
      bookingForm?.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
      bookingForm?.querySelector('textarea') && (bookingForm.querySelector('textarea').value = '');
      bookingError && bookingError.classList.add('hidden');
      bookingModal?.classList.remove('hidden'); bookingModal?.classList.add('flex');
      window.currentRoomId = roomId;
      console.log('openBooking called for', roomId);
    };

    function closeBooking(){ bookingModal?.classList.add('hidden'); bookingModal?.classList.remove('flex'); }
    bookingClose?.addEventListener('click', closeBooking);
    bookingCancel?.addEventListener('click', closeBooking);
    bookingModal?.addEventListener('click', (e)=>{ if(e.target===bookingModal) closeBooking(); });

    // payment modal handlers
    function openPaymentModal(id){
      if(!paymentBookingId) return;
      paymentBookingId.value = id;
      paymentForm?.reset();
      paymentError?.classList.add('hidden');
      paymentModal?.classList.remove('hidden'); paymentModal?.classList.add('flex');
    }
    window.openPaymentModal = openPaymentModal;
    function closePayment(){ paymentModal?.classList.add('hidden'); paymentModal?.classList.remove('flex'); }
    paymentClose?.addEventListener('click', closePayment);
    paymentCancel?.addEventListener('click', closePayment);
    paymentModal?.addEventListener('click', e=>{ if(e.target===paymentModal) closePayment(); });

    paymentForm?.addEventListener('submit', async e=>{
      e.preventDefault();
      paymentSubmit.disabled = true;
      paymentError?.classList.add('hidden');
      try{
        const fd = new FormData(paymentForm);
        const res = await fetch('api/booking_payment_upload.php',{method:'POST',body:fd,credentials:'include'});
        const txt = await res.text();
        let data; try{ data = JSON.parse(txt); } catch{ data={success:false,message:txt}; }
        if(!data.success){ throw new Error(data.message || 'Upload failed'); }
        closePayment();
        alert('Payment proof uploaded. Awaiting approval.');
      } catch(err){
        console.error('payment upload',err);
        paymentError && (paymentError.textContent = err.message || err, paymentError.classList.remove('hidden'));
      } finally {
        paymentSubmit.disabled = false;
      }
    });

    // ensure viewBookBtn has click handler (extra safety)
    (function attachViewBookNow(){
      const attempt = () => {
        const b = document.getElementById('viewBookBtn');
        if(!b) return setTimeout(attempt, 150);
        b.addEventListener('click', (e)=>{ e.preventDefault(); const id = window.currentRoomId || (viewModal && viewModal.dataset && viewModal.dataset.roomId) || null; if(!id){ alert('Room not selected.'); return; } closeView(); openBooking(id); });
        console.log('viewBookBtn safe handler attached (permanent).');
      };
      attempt();
    })();

    // booking submit
    bookingForm?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      bookingError && bookingError.classList.add('hidden');
      const room_id = bookingRoomId?.value;
      const start_date = bookingStart?.value;
      const end_date = bookingEnd?.value;
      const guests = Number(bookingGuests?.value || 1);
      const extras = Array.from(bookingForm?.querySelectorAll('input[name="extras[]"]:checked') || []).map(i=>i.value);
      const notes = (document.getElementById('bookingNotes') || {value:''}).value.trim();

      if(!room_id || !start_date || !end_date){
        bookingError && (bookingError.textContent = 'Please choose start and end dates.', bookingError.classList.remove('hidden'));
        return;
      }
      if(new Date(end_date) < new Date(start_date)){
        bookingError && (bookingError.textContent = 'End date must be same or after start date.', bookingError.classList.remove('hidden'));
        return;
      }
      bookingSubmit.disabled = true;
      try {
        const fd = new FormData();
        fd.append('room_id', room_id);
        fd.append('start_date', start_date);
        fd.append('end_date', end_date);
        fd.append('guests', String(guests));
        fd.append('extras', JSON.stringify(extras));
        fd.append('notes', notes);

        const res = await fetch('api/bookings_create.php', { method:'POST', credentials:'include', body:fd });
        const textRes = await res.text();
        let data;
        try { data = JSON.parse(textRes); } catch { data = { success:false, message:textRes }; }
        if(!data.success) {
          bookingError && (bookingError.textContent = data.message || 'Booking failed', bookingError.classList.remove('hidden'));
          return;
        }
        closeBooking();
        openPaymentModal(data.booking_id);
        await loadRooms();
      } catch(err){
        console.error('Booking error', err);
        bookingError && (bookingError.textContent = err.message || 'Network/server error', bookingError.classList.remove('hidden'));
      } finally {
        bookingSubmit.disabled = false;
      }
    });

    // expose loader
    window.sf_loadRooms = loadRooms;
  }); // DOMContentLoaded
})(); // IIFE
