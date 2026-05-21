(function () {

  /* ─── AUTH GUARD ─── */
  if (!window.location.pathname.includes('login.html')) {
    if (localStorage.getItem('tl_logged_in') !== 'true') {
      const base = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
      window.location.replace(base + 'login.html');
      return;
    }
  }

  /* ─── STORAGE HELPERS ─── */
  document.addEventListener('DOMContentLoaded', function() {
    const userEmail = localStorage.getItem('tl_user_email') || '';
    const userKey   = 'tl_profile_' + userEmail;
    const userId    = localStorage.getItem('tl_user_id') || '';

    function saveProfile(data) {
      const existing = loadProfile();
      localStorage.setItem(userKey, JSON.stringify({ ...existing, ...data }));
    }

    function loadProfile() {
      try { return JSON.parse(localStorage.getItem(userKey) || '{}'); } catch { return {}; }
    }

    function syncAddressToCheckout(address) {
      if (!userId) return;
      localStorage.setItem('tl_saved_address_' + userId, JSON.stringify(address));
    }

    async function saveProfileToDB(payload) {
      if (!userId) return;
      try {
        await fetch('../backend/update_profile.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: parseInt(userId), ...payload })
        });
      } catch (_) {}
    }

    /* ─── PROFILE DATA ─── */
    const stored = loadProfile();

    const profile = {
      name:    stored.name   || localStorage.getItem('tl_name') || '',
      email:   userEmail,
      avatar:  stored.avatar || '',
      address: '',
    };

    // Load address from SERVER so it works on any device/browser
    async function syncAddressFromServer() {
      if (!userId) return;
      try {
        const res  = await fetch('../backend/get_address.php?user_id=' + encodeURIComponent(userId));
        const data = await res.json();
        if (data.success && data.address) {
          profile.address = data.address;
          saveProfile({ address: data.address });
          renderAll();
        }
      } catch (_) {}
    }

    syncAddressFromServer();

    /* ─── RENDER ─── */
    function getAddressText(addr) {
      if (!addr) return '';
      if (typeof addr === 'string') return addr;
      if (addr.text) return addr.text;
      return [addr.street, addr.barangay, addr.city, addr.province, addr.zip].filter(Boolean).join(', ');
    }

    function maskEmail(email) {
      if (!email || !email.includes('@')) return email || '';
      const [local, domain] = email.split('@');
      if (local.length <= 2) return email;
      return local[0] + '*'.repeat(Math.min(local.length - 1, 8)) + '@' + domain;
    }

    function setText(id, val) {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    }

    function setTextOrUnset(id, val) {
      const el = document.getElementById(id);
      if (!el) return;
      if (val) { el.textContent = val; el.classList.remove('unset'); }
      else     { el.textContent = 'Set Now'; el.classList.add('unset'); }
    }

    function renderAll() {
      setText('pName',       profile.name || 'Set Now');
      setText('displayName', profile.name || 'User');
      setTextOrUnset('pAddress', getAddressText(profile.address));
      setText('pEmail',       maskEmail(profile.email));
      setText('displayEmail', maskEmail(profile.email));

      const avatarEl = document.getElementById('avatarImg');
      if (avatarEl) {
        avatarEl.src = profile.avatar
          ? profile.avatar
          : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.name || 'User') + '&background=4D80E4&color=fff&size=200';
      }
    }

    renderAll();

    // Sync avatar from server so it works on any device / browser
    async function syncAvatarFromServer() {
      if (!userId) return;
      try {
        const res  = await fetch('../backend/get_forum_user.php?user_id=' + userId);
        const data = await res.json();
        if (data && data.success && data.user && data.user.profile_pic) {
          const serverUrl = '../' + data.user.profile_pic;
          profile.avatar  = serverUrl;
          saveProfile({ avatar: serverUrl, profile_pic: data.user.profile_pic });
          const avatarEl = document.getElementById('avatarImg');
          if (avatarEl) avatarEl.src = serverUrl + '?t=' + Date.now();
        }
      } catch (_) {}
    }

    syncAvatarFromServer();

    /* ─── AVATAR UPLOAD ─── */
    const avatarWrap  = document.getElementById('avatarWrap');
    const avatarInput = document.getElementById('avatarInput');
    if (avatarWrap && avatarInput) {
      avatarWrap.addEventListener('click', () => avatarInput.click());
      avatarInput.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        // Show preview immediately
        const reader = new FileReader();
        reader.onload = async (e) => {
          const dataUrl = e.target.result;
          const avatarEl = document.getElementById('avatarImg');
          if (avatarEl) avatarEl.src = dataUrl;

          // Upload to server so other users can see it
          if (!userId) {
            // No user_id: fall back to localStorage only
            profile.avatar = dataUrl;
            saveProfile({ avatar: dataUrl });
            return;
          }

          try {
            const fd = new FormData();
            fd.append('user_id', userId);
            fd.append('avatar_base64', dataUrl);

            const res  = await fetch('../backend/upload_avatar.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success && data.profile_pic) {
              // Save the server path so other users see your real photo
              const serverUrl = '../' + data.profile_pic;
              profile.avatar = serverUrl;
              saveProfile({ avatar: serverUrl, profile_pic: data.profile_pic });
              if (avatarEl) avatarEl.src = serverUrl + '?t=' + Date.now();
            } else {
              // Upload failed — keep localStorage fallback
              profile.avatar = dataUrl;
              saveProfile({ avatar: dataUrl });
            }
          } catch (_) {
            profile.avatar = dataUrl;
            saveProfile({ avatar: dataUrl });
          }
        };
        reader.readAsDataURL(file);
      });
    }

    /* ─── MODAL ELEMENTS ─── */
    const modal            = document.getElementById('editModal');
    const modalTitle       = document.getElementById('modalTitle');
    const modalInput       = document.getElementById('modalInput');
    const textInputWrap    = document.getElementById('textInputWrap');
    const addressInputWrap = document.getElementById('addressInputWrap');
    const modalSave        = document.getElementById('modalSave');
    const modalCancel      = document.getElementById('modalCancel');

    let activeField = null;

    /* ─── OPEN MODAL ─── */
    function openModal(field) {
      activeField = field;
      const titles = { name: 'Name', address: 'Address', email: 'Email' };
      modalTitle.textContent = 'Edit ' + (titles[field] || field);

      textInputWrap.style.display    = 'none';
      addressInputWrap.style.display = 'none';

      modal.classList.add('open');
    }

    window.openModal = openModal;

    function closeModal() {
      modal.classList.remove('open');
      activeField = null;
    }

    document.querySelectorAll('.profile-row.editable-row').forEach(function(row) {
      row.addEventListener('click', function() {
        var field = row.getAttribute('data-field');
        if (field) openModal(field);
      });
    });

    if (modalCancel) modalCancel.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });

    if (modalSave) {
      modalSave.addEventListener('click', async function() {
        if (!activeField) return;

        if (activeField === 'address') {
          const g = function(id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; };
          const street   = g('addrStreet'),
                barangay = g('addrBarangay'),
                city     = g('addrCity'),
                province = g('addrProvince'),
                zip      = g('addrZip');
          if (!street && !city) return;
          const addrObj = { street, barangay, city, province, zip,
                            text: [street, barangay, city, province, zip].filter(Boolean).join(', ') };
          profile.address = addrObj;
          saveProfile({ address: addrObj });
          // Save to server (works across all devices and browsers)
          try {
            await fetch('../backend/save_address.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ user_id: parseInt(userId, 10), address: addrObj })
            });
          } catch (_) {}
        }

        renderAll();
        closeModal();
      });
    }

    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
      backBtn.addEventListener('click', function() {
        window.location.href = '../frontend/main.html';
      });
    }

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function() {
        const modal = document.getElementById('logoutModal');
        if (modal) modal.classList.add('open');
      });
    }

    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
    if (confirmLogoutBtn) {
      confirmLogoutBtn.addEventListener('click', function() {
        ['tl_logged_in','tl_user_email','tl_name','tl_role','tl_cart',
         'tl_checkout_items','tl_return_to','tl_pending_action','tl_avatar',
         ].forEach(function(k) { localStorage.removeItem(k); });
        const base = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
        window.location.replace(base + 'login.html');
      });
    }

  }); // end DOMContentLoaded

})();