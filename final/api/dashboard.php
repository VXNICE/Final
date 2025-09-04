<?php
// dashboard.php (User-focused)
// Protect page
session_start();
if (empty($_SESSION['user'])) {
  header('Location: login.html');
  exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - StayFind</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="min-h-screen flex">

    <!-- Side Menu -->
    <aside class="w-64 bg-white border-r hidden md:flex md:flex-col">
      <div class="px-5 py-4 border-b">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded bg-blue-700"></div>
          <div>
            <p class="text-sm text-gray-500">Signed in as</p>
            <p class="font-semibold text-gray-800">
              <?php echo htmlspecialchars($user['name'] ?: $user['email']); ?>
            </p>
          </div>
        </div>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1">
        <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium">
          <span>🏠</span> <span>Dashboard</span>
        </a>
        <a href="#units" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
          <span>🏘️</span> <span>Units</span>
        </a>
        <a href="settings.html" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
          <span>⚙️</span> <span>Settings</span>
        </a>
      </nav>

      <div class="px-3 py-4 border-t">
        <button id="logoutBtn" class="w-full px-3 py-2 rounded-lg bg-blue-700 text-white hover:bg-blue-800">
          Logout
        </button>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex-1">
      <!-- Top bar (mobile) -->
      <header class="md:hidden bg-white border-b sticky top-0 z-10">
        <div class="px-4 py-3 flex items-center justify-between">
          <h1 class="font-semibold text-blue-700">StayFind</h1>
          <div class="flex items-center gap-3">
            <a href="settings.html" class="px-3 py-1.5 rounded border hover:bg-gray-50 text-sm">Settings</a>
            <button id="logoutBtnMobile" class="px-3 py-1.5 rounded bg-blue-700 text-white text-sm hover:bg-blue-800">Logout</button>
          </div>
        </div>
      </header>

      <section class="mx-auto max-w-6xl px-4 py-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
            <p class="text-sm text-gray-600">Browse available units. Use filters to narrow results.</p>
          </div>

          <!-- Add Unit (disabled for user-focused dashboard) -->
          <button
            type="button"
            disabled
            title="Add Unit is for Owners/Admins (coming soon)"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-300 text-white cursor-not-allowed"
          >
            ➕ Add Unit
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white border rounded-xl p-4">
          <div class="grid gap-4 md:grid-cols-3">
            <!-- Location -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
              <select id="filterLocation" class="w-full border rounded-lg px-3 py-2">
                <option value="">All locations</option>
                <!-- options populated by JS -->
              </select>
            </div>

            <!-- Min Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Min price (₱)</label>
              <input id="filterMinPrice" type="range" min="0" max="10000" step="100" value="0" class="w-full">
              <div class="text-xs text-gray-600">₱ <span id="minPriceLabel">0</span></div>
            </div>

            <!-- Max Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Max price (₱)</label>
              <input id="filterMaxPrice" type="range" min="0" max="20000" step="100" value="20000" class="w-full">
              <div class="text-xs text-gray-600">₱ <span id="maxPriceLabel">20000</span></div>
            </div>
          </div>
        </div>

        <!-- Units Grid -->
        <div id="units" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Cards injected here -->
        </div>

        <p id="empty" class="hidden text-center text-gray-500">No units found.</p>
      </section>
    </main>
  </div>

  <script>
    // Elements
    const grid   = document.getElementById('units');
    const empty  = document.getElementById('empty');
    const selLoc = document.getElementById('filterLocation');
    const minR   = document.getElementById('filterMinPrice');
    const maxR   = document.getElementById('filterMaxPrice');
    const minLbl = document.getElementById('minPriceLabel');
    const maxLbl = document.getElementById('maxPriceLabel');

    // Data cache
    let allRooms = [];

    // Fetch rooms
    async function loadRooms() {
      empty.classList.add('hidden');
      grid.innerHTML = '';
      try {
        const res = await fetch('api/rooms_list.php', { credentials: 'include' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to load rooms');
        allRooms = data.rooms || [];
        buildLocationFilter(allRooms);
        renderFiltered();
      } catch (e) {
        console.error(e);
        empty.textContent = 'Failed to load rooms.';
        empty.classList.remove('hidden');
      }
    }

    // Build location dropdown from rooms
    function buildLocationFilter(rooms) {
      const unique = Array.from(new Set(
        rooms.map(r => (r.location || '').trim()).filter(Boolean)
      )).sort((a,b) => a.localeCompare(b));
      selLoc.innerHTML = '<option value="">All locations</option>' +
        unique.map(loc => `<option value="${escapeHtml(loc)}">${escapeHtml(loc)}</option>`).join('');
    }

    // Render after applying filters
    function renderFiltered() {
      minLbl.textContent = minR.value;
      maxLbl.textContent = maxR.value;

      const min = Number(minR.value);
      const max = Number(maxR.value);
      const loc = selLoc.value;

      const list = allRooms.filter(r => {
        const price = r.price == null ? null : Number(r.price);
        const okLoc = loc === '' || (r.location || '') === loc;
        const okPrice = price == null || (price >= min && price <= max);
        return okLoc && okPrice;
      });

      renderCards(list);
    }

    // Render cards
    function renderCards(rooms) {
      if (!rooms || rooms.length === 0) {
        grid.innerHTML = '';
        empty.textContent = 'No units found.';
        empty.classList.remove('hidden');
        return;
      }
      empty.classList.add('hidden');

      grid.innerHTML = rooms.map(r => {
        const title = r.title || '(Untitled)';
        const loc   = r.location || '';
        const price = r.price != null ? Number(r.price) : '';
        const cap   = r.capacity != null ? r.capacity : '';
        const img   = r.image || '';

        return `
          <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
            ${img ? `<img src="${escapeAttr(img)}" alt="${escapeAttr(title)}" class="h-40 w-full object-cover">` : `<div class="h-40 w-full bg-gray-200"></div>`}
            <div class="p-4">
              <h3 class="font-semibold text-gray-900">${escapeHtml(title)}</h3>
              <p class="text-sm text-gray-600">${escapeHtml(loc)}</p>
              <div class="mt-2 flex items-center justify-between text-sm text-gray-700">
                <span>${price !== '' ? '₱ ' + price.toLocaleString() : ''}</span>
                <span>${cap !== '' ? cap + ' pax' : ''}</span>
              </div>
              <div class="mt-3">
                <button class="w-full px-3 py-2 rounded bg-blue-700 text-white text-sm hover:bg-blue-800">
                  View
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Escape helpers
    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[m]));
    }
    function escapeAttr(s){ return String(s).replace(/"/g,'&quot;'); }

    // Events
    [selLoc, minR, maxR].forEach(el => el.addEventListener('input', renderFiltered));

    // Logout
    const doLogout = async () => {
      try { await fetch('api/logout.php', { credentials: 'include' }); } catch {}
      window.location.href = 'login.html';
    };
    document.getElementById('logoutBtn')?.addEventListener('click', doLogout);
    document.getElementById('logoutBtnMobile')?.addEventListener('click', doLogout);

    // Init
    loadRooms();
  </script>
</body>
</html>
