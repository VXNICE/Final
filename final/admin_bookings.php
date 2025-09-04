<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Bookings</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-2xl font-bold mb-4">Bookings</h1>
<table class="min-w-full text-sm text-left border" id="bookingsTable">
<thead class="bg-gray-100">
<tr>
  <th class="px-2 py-1">ID</th>
  <th class="px-2 py-1">User</th>
  <th class="px-2 py-1">Room</th>
  <th class="px-2 py-1">Status</th>
  <th class="px-2 py-1">Method</th>
  <th class="px-2 py-1">Actions</th>
</tr>
</thead>
<tbody></tbody>
</table>
<script>
async function load(){
  const res = await fetch('api/bookings_list.php', {credentials:'include'});
  const data = await res.json();
  const tb = document.querySelector('#bookingsTable tbody');
  if(!data.success){ tb.innerHTML = `<tr><td colspan="6" class="px-2 py-1 text-red-600">${data.message||'Error'}</td></tr>`; return; }
  tb.innerHTML = data.bookings.map(b=>`
    <tr class="border-t">
      <td class="px-2 py-1">${b.id}</td>
      <td class="px-2 py-1">${b.user_name||''}</td>
      <td class="px-2 py-1">${b.room_title||''}</td>
      <td class="px-2 py-1">${b.payment_status}</td>
      <td class="px-2 py-1">${b.payment_method||''}</td>
      <td class="px-2 py-1 space-x-2">
        ${b.payment_receipt_path ? `<a href="${b.payment_receipt_path}" target="_blank" class="text-blue-600">Receipt</a>` : ''}
        ${b.payment_status==='pending' ? `<button data-appr="${b.id}" class="text-green-600">Approve</button><button data-decl="${b.id}" class="text-red-600">Decline</button>` : ''}
      </td>
    </tr>`).join('');
  tb.querySelectorAll('[data-appr]').forEach(btn=>btn.addEventListener('click',()=>verify(btn.dataset.appr,'approve')));
  tb.querySelectorAll('[data-decl]').forEach(btn=>btn.addEventListener('click',()=>verify(btn.dataset.decl,'decline')));
}
async function verify(id,action){
  const fd = new FormData(); fd.append('booking_id',id); fd.append('action',action);
  const res = await fetch('api/booking_verify.php',{method:'POST',body:fd,credentials:'include'});
  const data = await res.json();
  alert(data.success? 'Updated':'Failed');
  load();
}
load();
</script>
</body>
</html>
