// Location: Technilog/js/resetPass.js
// Used by : auth/resetpass.html

const email = sessionStorage.getItem('tl_reset_email');
if (!email) { window.location.href = 'forgotpass.html'; }
document.getElementById('emailDisplay').textContent = email;

let verifiedCode = null;

function showMsg(text, isError) {
  const box = document.getElementById('msgBox');
  box.textContent = text;
  box.className = 'mb-4 py-2 px-3 rounded-lg text-center text-sm ' +
    (isError ? 'bg-red-500/20 text-red-100' : 'bg-green-500/20 text-green-100');
}

// ── 6-digit boxes ─────────────────────────────────────
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

// ── STEP 1: Verify code ───────────────────────────────
document.getElementById('verifyCodeBtn').addEventListener('click', async function () {
  const code = getCode();
  if (code.length < 6) { showMsg('Please enter all 6 digits.', true); return; }

  this.disabled = true;
  this.textContent = 'Verifying…';

  try {
    const res  = await fetch('reset_verify.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email, code }),
    });
    const data = await res.json();

    if (data.success) {
      verifiedCode = code;
      showMsg('Code verified! Set your new password below.', false);
      document.getElementById('stepCode').classList.add('hidden');
      document.getElementById('stepPassword').classList.remove('hidden');
    } else {
      showMsg(data.message || 'Verification failed.', true);
      this.disabled = false;
      this.textContent = 'Verify Code';
    }
  } catch (err) {
    showMsg('Network error. Is your server running?', true);
    this.disabled = false;
    this.textContent = 'Verify Code';
  }
});

// ── Resend with 60s cooldown ──────────────────────────
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
    const res  = await fetch('forgot_send.php', {
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

// ── Password toggles ──────────────────────────────────
document.getElementById('toggleNewPassword').addEventListener('click', function () {
  const p = document.getElementById('new_password');
  p.type = p.type === 'password' ? 'text' : 'password';
  this.classList.toggle('fa-eye'); this.classList.toggle('fa-eye-slash');
});
document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
  const p = document.getElementById('confirm_password');
  p.type = p.type === 'password' ? 'text' : 'password';
  this.classList.toggle('fa-eye'); this.classList.toggle('fa-eye-slash');
});

// ── STEP 2: Reset password ────────────────────────────
document.getElementById('resetBtn').addEventListener('click', async function () {
  const new_password     = document.getElementById('new_password').value;
  const confirm_password = document.getElementById('confirm_password').value;

  if (!new_password || !confirm_password) {
    showMsg('Please fill in both password fields.', true); return;
  }
  if (new_password.length < 8) {
    showMsg('Password must be at least 8 characters.', true); return;
  }
  if (!/[A-Z]/.test(new_password[0])) {
    showMsg('Password must start with an uppercase letter.', true); return;
  }
  if (!/[0-9]/.test(new_password)) {
    showMsg('Password must contain at least one number.', true); return;
  }
  if (new_password !== confirm_password) {
    showMsg('Passwords do not match.', true); return;
  }
  if (!verifiedCode) {
    showMsg('Session lost. Please start again.', true);
    setTimeout(() => { window.location.href = 'forgotpass.html'; }, 1500);
    return;
  }

  this.disabled = true;
  this.textContent = 'Resetting…';

  try {
    const res  = await fetch('reset_password.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email, code: verifiedCode, new_password, confirm_password }),
    });
    const data = await res.json();

    if (data.success) {
      sessionStorage.removeItem('tl_reset_email');
      showMsg('✓ ' + data.message, false);
      setTimeout(() => { window.location.href = 'login.html'; }, 2000);
    } else {
      showMsg(data.message || 'Reset failed.', true);
      this.disabled = false;
      this.textContent = 'Reset Password';
    }
  } catch (err) {
    showMsg('Network error. Is your server running?', true);
    this.disabled = false;
    this.textContent = 'Reset Password';
  }
});

boxes[0].focus();