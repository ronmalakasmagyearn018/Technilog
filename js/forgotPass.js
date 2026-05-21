// Location: Technilog/js/forgotPass.js
// Used by : auth/forgotpass.html

function showMsg(text, isError) {
  const box = document.getElementById('msgBox');
  box.textContent = text;
  box.className = 'mb-4 py-2 px-3 rounded-lg text-center text-sm ' +
    (isError ? 'bg-red-500/20 text-red-100' : 'bg-green-500/20 text-green-100');
}

document.getElementById('sendCodeBtn').addEventListener('click', async function () {
  const email = document.getElementById('email').value.trim();

  if (!email) {
    showMsg('Please enter your email address.', true); return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showMsg('Please enter a valid email address.', true); return;
  }

  this.disabled = true;
  this.textContent = 'Sending code…';

  try {
    // forgot_send.php is in the same auth/ folder as forgotpass.html
    const res  = await fetch('forgot_send.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email }),
    });
    const data = await res.json();

    if (data.success) {
      sessionStorage.setItem('tl_reset_email', email);
      showMsg('Code sent! Redirecting…', false);
      setTimeout(() => { window.location.href = 'resetpass.html'; }, 1200);
    } else {
      showMsg(data.message || 'Something went wrong.', true);
      this.disabled = false;
      this.textContent = 'Send Verification Code';
    }
  } catch (err) {
    showMsg('Network error. Is your server running?', true);
    this.disabled = false;
    this.textContent = 'Send Verification Code';
  }
});