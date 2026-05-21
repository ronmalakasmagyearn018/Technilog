// js/workLogin.js — used by auth/login.html

// ── Password toggle ───────────────────────────
document.getElementById('togglePassword').addEventListener('click', function () {
  const p = document.getElementById('loginPassword');
  p.type = p.type === 'password' ? 'text' : 'password';
  this.classList.toggle('fa-eye');
  this.classList.toggle('fa-eye-slash');
});

// ── Back button ───────────────────────────────
document.getElementById('backBtn').addEventListener('click', () => {
  window.location.href = '../frontend/main.html';
});

// ── Message helper ────────────────────────────
function showMsg(text, isError) {
  const box = document.getElementById('msgBox');
  box.textContent = text;
  box.style.display = 'block';
  box.className = 'text-center text-sm rounded-lg ' +
    (isError ? 'bg-red-500/20 text-red-100' : 'bg-green-500/20 text-green-100');
}

// ── Build absolute URL relative to current page ──
function pageUrl(filename) {
  // Gets the folder of the current HTML page and appends filename
  const base = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
  return base + filename;
}

// ── Login ─────────────────────────────────────
document.getElementById('loginBtn').addEventListener('click', async function () {
  const email    = document.getElementById('loginEmail').value.trim();
  const password = document.getElementById('loginPassword').value;

  if (!email || !password) {
    showMsg('Please enter your email and password.', true); return;
  }

  this.disabled = true;
  this.textContent = 'Logging in…';

  try {
    const loginUrl = pageUrl('login.php');

    const res = await fetch(loginUrl, {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'ngrok-skip-browser-warning': '1',
      },
      body:    JSON.stringify({ email, password }),
    });

    const rawText = await res.text();

    let data;
    try {
      data = JSON.parse(rawText);
    } catch (e) {
      showMsg('Server error: ' + rawText.substring(0, 120), true);
      this.disabled = false;
      this.textContent = 'Log In';
      return;
    }

    if (data.success) {
      // Clear any stale keys
      localStorage.removeItem('tl_return_to');
      localStorage.removeItem('tl_pending_action');

      // Set auth keys
      localStorage.setItem('tl_logged_in',  'true');
      localStorage.setItem('tl_user_email', data.email    || email);
      localStorage.setItem('tl_name',       data.username || '');
      localStorage.setItem('tl_role',       data.role     || 'user');
      localStorage.setItem('tl_user_id',    data.id       || '');

      // Navigate using absolute URL so spaces/encoding don't break it
      window.location.replace(pageUrl('../frontend/main.html'));
    } else {
      showMsg(data.message || 'Login failed.', true);
      this.disabled = false;
      this.textContent = 'Log In';
    }
  } catch (err) {
    showMsg('Network error: ' + err.message, true);
    this.disabled = false;
    this.textContent = 'Log In';
  }
});