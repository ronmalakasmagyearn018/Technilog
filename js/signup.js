// js/signup.js — used by auth/signup.html

// ── Password toggles ──────────────────────────
document.getElementById('togglePassword').addEventListener('click', function () {
  const p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
  this.classList.toggle('fa-eye');
  this.classList.toggle('fa-eye-slash');
});
document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
  const p = document.getElementById('confirm_password');
  p.type = p.type === 'password' ? 'text' : 'password';
  this.classList.toggle('fa-eye');
  this.classList.toggle('fa-eye-slash');
});

// ── Message helper ────────────────────────────
function showMsg(text, isError) {
  const box = document.getElementById('msgBox');
  box.textContent = text;
  box.className = 'mb-4 py-2 px-3 rounded-lg text-center text-sm ' +
    (isError ? 'bg-red-500/20 text-red-100' : 'bg-green-500/20 text-green-100');
}

// ── Sign Up ───────────────────────────────────
document.getElementById('signupBtn').addEventListener('click', async function () {
  const username         = document.getElementById('username').value.trim();
  const email            = document.getElementById('email').value.trim();
  const password         = document.getElementById('password').value;
  const confirm_password = document.getElementById('confirm_password').value;

  if (!username || !email || !password || !confirm_password) {
    showMsg('All fields are required.', true); return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showMsg('Enter a valid email address.', true); return;
  }
  if (password.length < 8) {
    showMsg('Password must be at least 8 characters.', true); return;
  }
  if (password !== confirm_password) {
    showMsg('Passwords do not match.', true); return;
  }

  this.disabled = true;
  this.textContent = 'Creating account…';

  try {
    // signup.php is in the same auth/ folder as signup.html
    const res  = await fetch('signup.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ username, email, password, confirm_password }),
    });
    const data = await res.json();

    if (data.success) {
      sessionStorage.setItem('tl_pending_email',    email);
      sessionStorage.setItem('tl_pending_username', username);
      showMsg('Code sent! Redirecting…', false);
      setTimeout(() => { window.location.href = 'verify.html'; }, 1200);
    } else {
      showMsg(data.message || 'Sign up failed.', true);
      this.disabled = false;
      this.textContent = 'Sign Up';
    }
  } catch (err) {
    showMsg('Network error. Is your server running?', true);
    this.disabled = false;
    this.textContent = 'Sign Up';
  }
});