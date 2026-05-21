// js/verify.js — used by auth/verify.html

const email = sessionStorage.getItem('tl_pending_email');
if (!email) { window.location.href = 'signup.html'; }

document.getElementById('emailDisplay').textContent = email;

// ── Message helper ────────────────────────────
function showMsg(text, isError) {
  const box = document.getElementById('msgBox');
  box.textContent = text;
  box.className = 'mb-4 py-2 px-3 rounded-lg text-center text-sm ' +
    (isError ? 'bg-red-500/20 text-red-100' : 'bg-green-500/20 text-green-100');
}

// ── 6-digit code boxes ────────────────────────
const boxes = document.querySelectorAll('.code-box');
boxes.forEach((box, i) => {
  box.addEventListener('input', () => {
    box.value = box.value.replace(/\D/g, '').slice(0, 1);
    if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
  });
  box.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
  });
  box.addEventListener('paste', e => {
    const pasted = (e.clipboardData || window.clipboardData)
      .getData('text').replace(/\D/g, '').slice(0, 6);
    if (pasted.length === 6) {
      e.preventDefault();
      [...pasted].forEach((ch, j) => { if (boxes[j]) boxes[j].value = ch; });
      boxes[5].focus();
    }
  });
});

function getCode() { return [...boxes].map(b => b.value).join(''); }

// ── Verify ────────────────────────────────────
document.getElementById('verifyBtn').addEventListener('click', async function () {
  const code = getCode();
  if (code.length < 6) { showMsg('Please enter all 6 digits.', true); return; }

  this.disabled = true;
  this.textContent = 'Verifying…';

  try {
    // verify.php is in the same auth/ folder as verify.html
    const res  = await fetch('verify.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email, code }),
    });
    const data = await res.json();

    if (data.success) {
      showMsg('✓ Verified! Redirecting to login…', false);
      sessionStorage.removeItem('tl_pending_email');
      sessionStorage.removeItem('tl_pending_username');
      setTimeout(() => { window.location.href = 'login.html'; }, 1500);
    } else {
      showMsg(data.message || 'Verification failed.', true);
      this.disabled = false;
      this.textContent = 'Verify Email';
    }
  } catch (err) {
    showMsg('Network error. Is your server running?', true);
    this.disabled = false;
    this.textContent = 'Verify Email';
  }
});

// ── Resend with 60s cooldown ──────────────────
const resendBtn   = document.getElementById('resendBtn');
const cooldownTxt = document.getElementById('cooldownTxt');

function startCooldown() {
  let left = 60;
  resendBtn.disabled = true;
  cooldownTxt.classList.remove('hidden');
  cooldownTxt.textContent = `Resend in ${left}s`;
  const timer = setInterval(() => {
    left--;
    cooldownTxt.textContent = `Resend in ${left}s`;
    if (left <= 0) {
      clearInterval(timer);
      resendBtn.disabled = false;
      cooldownTxt.classList.add('hidden');
    }
  }, 1000);
}

resendBtn.addEventListener('click', async function () {
  this.disabled = true;
  showMsg('Sending a new code…', false);
  try {
    // resend_code.php is in the same auth/ folder as verify.html
    const res  = await fetch('resend_code.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email }),
    });
    const data = await res.json();
    showMsg(data.message, !data.success);
    if (data.success) {
      boxes.forEach(b => b.value = '');
      boxes[0].focus();
      startCooldown();
    } else {
      this.disabled = false;
    }
  } catch (err) {
    showMsg('Network error.', true);
    this.disabled = false;
  }
});

boxes[0].focus();