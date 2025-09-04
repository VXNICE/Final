// assets/js/register.js
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  const btn  = document.getElementById('registerBtn');
  const msg  = document.getElementById('message');

  const setMsg = (text, ok=false) => {
    msg.textContent = text || '';
    msg.className = 'mt-4 text-center text-sm ' + (ok ? 'text-green-600' : 'text-red-600');
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    setMsg('');
    btn.disabled = true;
    btn.textContent = 'Creating...';

    try {
      const res  = await fetch('api/register.php', {
        method: 'POST',
        body: new FormData(form),
        credentials: 'include'
      });

      const raw  = await res.text();
      let data   = null;
      try { data = JSON.parse(raw); } catch {}

      if (!res.ok) {
        setMsg(`HTTP ${res.status}: ${raw.slice(0,200)}`);
        return;
      }
      if (!data || !data.success) {
        setMsg((data && data.message) ? data.message : (raw || 'Registration failed.'));
        return;
      }

      setMsg(data.message || 'Registration successful!', true);
      form.reset();
      setTimeout(() => { window.location.href = 'login.html'; }, 1200);
    } catch (err) {
      console.error(err);
      setMsg('Network/JS error: ' + (err?.message || err));
    } finally {
      btn.disabled = false;
      btn.textContent = 'Create account';
    }
  });
});
