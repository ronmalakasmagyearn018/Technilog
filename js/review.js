// ============================================================
//  review.js — Technilog/js/review.js
//  Handles: load reviews, star filter, submit review form,
//           image preview, admin reply
// ============================================================

(function () {
  // ── Config ─────────────────────────────────────────────────
  const API       = '../backend/review.php';
  const BASE_URL  = '../';   // root of Technilog project

  // ── State ──────────────────────────────────────────────────
  let currentProductId  = null;
  let currentFilter     = 0;   // 0 = all stars
  let currentOrderId    = null; // set when user can review
  let selectedRating    = 0;
  let reviewsData       = [];

  // ── Init ───────────────────────────────────────────────────
  function init() {
    const params = new URLSearchParams(window.location.search);
    currentProductId = params.get('id') || params.get('product_id');
    if (!currentProductId) return;

    injectHTML();
    loadReviews();
    checkCanReview();
    bindEvents();
  }

  // ── Inject section HTML under #relatedSection ───────────────
  function injectHTML() {
    const related = document.getElementById('relatedSection');
    if (!related) return;

    const section = document.createElement('div');
    section.id = 'reviewsSection';
    section.className = 'bg-white rounded-2xl shadow p-6 mt-6';
    section.innerHTML = `
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h2 class="font-bold text-lg text-gray-800">Customer Reviews</h2>
        <button id="writeReviewBtn" class="hidden px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
          style="background:var(--color-primary);">
          <i class="fas fa-pen mr-1"></i> Write a Review
        </button>
      </div>

      <!-- Summary bar -->
      <div id="reviewSummary" class="flex flex-col sm:flex-row gap-5 mb-5 p-4 rounded-xl"
        style="background:var(--color-quarternary, #f0f4ff);">
        <div class="flex flex-col items-center justify-center min-w-[90px]">
          <div id="avgScore" class="text-4xl font-extrabold" style="color:var(--color-primary);">—</div>
          <div id="avgStars" class="text-yellow-400 text-lg my-1"></div>
          <div id="totalReviews" class="text-xs text-gray-500">0 reviews</div>
        </div>
        <div id="breakdownBars" class="flex-1 flex flex-col gap-1 justify-center"></div>
      </div>

      <!-- Star filter pills -->
      <div id="starFilters" class="flex flex-wrap gap-2 mb-5">
        <button data-star="0"
          class="star-filter-btn active px-3 py-1 rounded-full text-sm font-semibold border transition"
          style="background:var(--color-primary);color:#fff;border-color:var(--color-primary);">
          All
        </button>
        ${[5,4,3,2,1].map(s => `
          <button data-star="${s}"
            class="star-filter-btn px-3 py-1 rounded-full text-sm font-semibold border border-gray-300 text-gray-600 transition hover:border-yellow-400 hover:text-yellow-500">
            <i class="fas fa-star text-yellow-400 text-xs mr-0.5"></i>${s} Star
          </button>`).join('')}
      </div>

      <!-- Reviews list -->
      <div id="reviewsList" class="space-y-4">
        <p class="text-gray-400 text-sm">Loading reviews…</p>
      </div>

      <!-- Write Review Modal -->
      <div id="reviewModal" class="hidden fixed inset-0 z-[999] flex items-center justify-center"
        style="background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 relative animate-pop">
          <button id="closeReviewModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 text-xl">
            <i class="fas fa-times"></i>
          </button>
          <h3 class="font-bold text-gray-800 text-lg mb-4">Write a Review</h3>

          <!-- Star picker -->
          <div class="mb-4">
            <div class="text-sm font-semibold text-gray-600 mb-2">Your Rating</div>
            <div id="starPicker" class="flex gap-2">
              ${[1,2,3,4,5].map(s => `
                <i data-val="${s}" class="star-pick fas fa-star text-3xl cursor-pointer text-gray-300 transition"
                   style="transition:color 0.15s"></i>`).join('')}
            </div>
          </div>

          <!-- Comment -->
          <div class="mb-4">
            <label class="text-sm font-semibold text-gray-600 mb-1 block">Comment</label>
            <textarea id="reviewComment" rows="4" maxlength="1000" placeholder="Share your experience with this product…"
              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none resize-none focus:border-blue-400"></textarea>
          </div>

          <!-- Image upload -->
          <div class="mb-5">
            <label class="text-sm font-semibold text-gray-600 mb-1 block">Photo (optional)</label>
            <label for="reviewImage" class="flex items-center gap-2 cursor-pointer text-sm text-gray-500 border border-dashed border-gray-300 rounded-xl px-3 py-2 hover:border-blue-400 transition">
              <i class="fas fa-image text-blue-400"></i> Choose image
            </label>
            <input type="file" id="reviewImage" accept="image/*" class="hidden">
            <div id="imagePreviewWrap" class="hidden mt-2 relative inline-block">
              <img id="imagePreview" src="" alt="Preview" class="h-20 rounded-lg object-cover border border-gray-200">
              <button id="removeImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Submit -->
          <button id="submitReviewBtn"
            class="w-full py-3 rounded-xl font-bold text-white text-sm transition"
            style="background:var(--color-primary);">
            Submit Review
          </button>
          <div id="reviewFormMsg" class="mt-2 text-xs text-center hidden"></div>
        </div>
      </div>

      <!-- Admin Reply Modal -->
      <div id="adminReplyModal" class="hidden fixed inset-0 z-[999] flex items-center justify-center"
        style="background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 relative">
          <button id="closeAdminReplyModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 text-xl">
            <i class="fas fa-times"></i>
          </button>
          <h3 class="font-bold text-gray-800 text-lg mb-4">Reply to Review</h3>
          <input type="hidden" id="replyReviewId">
          <textarea id="adminReplyText" rows="4" placeholder="Type your reply…"
            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none resize-none focus:border-blue-400 mb-4"></textarea>
          <button id="submitAdminReply"
            class="w-full py-3 rounded-xl font-bold text-white text-sm"
            style="background:var(--color-primary);">
            Post Reply
          </button>
          <div id="adminReplyMsg" class="mt-2 text-xs text-center hidden"></div>
        </div>
      </div>
    `;

    related.after(section);

    // Inject animation style
    if (!document.getElementById('reviewAnimStyle')) {
      const st = document.createElement('style');
      st.id = 'reviewAnimStyle';
      st.textContent = `
        @keyframes reviewPop { from{transform:scale(0.9);opacity:0} to{transform:scale(1);opacity:1} }
        .animate-pop { animation: reviewPop 0.2s cubic-bezier(.34,1.56,.64,1); }
        .star-pick {
          transition: color 0.12s, transform 0.12s !important;
          cursor: pointer;
        }
        .star-pick:hover { transform: scale(1.25) !important; }
        .star-pick.lit  { color: #f59e0b !important; transform: scale(1.1); }
      `;
      document.head.appendChild(st);
    }
  }

  // ── Load & render reviews ───────────────────────────────────
  async function loadReviews() {
    const list = document.getElementById('reviewsList');
    if (!list) return;
    list.innerHTML = '<p class="text-gray-400 text-sm">Loading…</p>';

    try {
      const url = `${API}?action=get&product_id=${currentProductId}` +
        (currentFilter ? `&rating=${currentFilter}` : '');
      const res  = await fetch(url);
      const data = await res.json();

      if (!data.success) { list.innerHTML = '<p class="text-red-400 text-sm">Failed to load reviews.</p>'; return; }

      reviewsData = data.reviews || [];
      renderSummary(data.average, data.total, data.breakdown);
      renderReviews(reviewsData);
    } catch {
      list.innerHTML = '<p class="text-red-400 text-sm">Error loading reviews.</p>';
    }
  }

  // ── Render summary bar ──────────────────────────────────────
  function renderSummary(avg, total, breakdown) {
    const scoreEl = document.getElementById('avgScore');
    const starsEl = document.getElementById('avgStars');
    const totalEl = document.getElementById('totalReviews');
    const barsEl  = document.getElementById('breakdownBars');
    if (!scoreEl) return;

    scoreEl.textContent = avg > 0 ? avg.toFixed(1) : '—';
    starsEl.innerHTML   = avg > 0 ? starsHTML(avg) : '';
    totalEl.textContent = `${total} review${total !== 1 ? 's' : ''}`;

    const max = Math.max(...Object.values(breakdown), 1);
    barsEl.innerHTML = [5,4,3,2,1].map(s => {
      const count = breakdown[s] || 0;
      const pct   = Math.round((count / max) * 100);
      return `
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <span class="w-8 text-right font-semibold">${s}<i class="fas fa-star text-yellow-400 ml-0.5 text-[10px]"></i></span>
          <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
            <div class="h-2 rounded-full bg-yellow-400 transition-all" style="width:${pct}%"></div>
          </div>
          <span class="w-5 text-gray-400">${count}</span>
        </div>`;
    }).join('');
  }

  // ── Render review cards ─────────────────────────────────────
  function renderReviews(reviews) {
    const list    = document.getElementById('reviewsList');
    const isAdmin = localStorage.getItem('tl_role') === 'admin';

    if (!reviews.length) {
      list.innerHTML = '<p class="text-gray-400 text-sm">No reviews yet' +
        (currentFilter ? ` for ${currentFilter}-star rating` : '') + '.</p>';
      return;
    }

    list.innerHTML = reviews.map(r => {
      const myUserId  = localStorage.getItem('tl_user_id') || '';
      const myAvatar  = localStorage.getItem('tl_avatar')  || '';
      // Use saved avatar if this is the current user's review, else use DB avatar_path
      const avatarSrc = (String(r.user_id) === String(myUserId) && myAvatar)
        ? myAvatar
        : (r.avatar_path ? (BASE_URL + r.avatar_path) : '');
      const initials  = (r.user_name || 'U')[0].toUpperCase();
      const avatarEl  = avatarSrc
        ? `<img src="${esc(avatarSrc)}" alt="${esc(initials)}"
             class="w-8 h-8 rounded-full object-cover border border-gray-200"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
           <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:var(--color-primary);display:none;">${initials}</div>`
        : `<div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:var(--color-primary);">${initials}</div>`;

      return `<div class="border border-gray-100 rounded-xl p-4 hover:shadow-sm transition" id="review-${r.id}">
        <div class="flex items-start justify-between gap-2 mb-2">
          <div class="flex items-center gap-2">
            <div class="flex items-center flex-shrink-0">${avatarEl}</div>
            <div>
              <div class="font-semibold text-gray-800 text-sm">${esc(r.user_name || 'Anonymous')}</div>
              <div class="text-xs text-gray-400">${formatDate(r.created_at)}</div>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <div class="text-yellow-400">${starsHTML(r.rating)}</div>
            ${isAdmin ? `<button onclick="TLReview.openReplyModal(${r.id})"
              class="text-xs px-2 py-1 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
              <i class="fas fa-reply mr-1"></i>Reply</button>` : ''}
          </div>
        </div>

        <p class="text-gray-700 text-sm leading-relaxed mb-2">${esc(r.comment)}</p>

        ${r.image_path ? `
          <div class="mb-2">
            <img src="${BASE_URL}${r.image_path}" alt="Review photo"
              class="h-28 rounded-lg object-cover border border-gray-200 cursor-pointer hover:opacity-90 transition"
              onclick="TLReview.lightbox(this.src)">
          </div>` : ''}

        ${r.admin_reply ? `
          <div class="mt-3 p-3 rounded-xl text-sm" style="background:var(--color-quarternary, #f0f4ff);">
            <div class="flex items-center gap-1 text-xs font-bold mb-1" style="color:var(--color-primary);">
              <i class="fas fa-shield-halved mr-1"></i>Technilog Team
              <span class="font-normal text-gray-400 ml-1">${formatDate(r.replied_at)}</span>
            </div>
            <p class="text-gray-700">${esc(r.admin_reply)}</p>
          </div>` : ''}
      </div>`;
    }).join('');
  }

  // ── Check if current user can write a review ────────────────
  async function checkCanReview() {
    const userId = localStorage.getItem('tl_user_id');
    if (!userId) return;

    // Check if coming from deliveries page with ?review=1&order_id=X
    const urlParams   = new URLSearchParams(window.location.search);
    const fromReview  = urlParams.get('review') === '1';
    const urlOrderId  = urlParams.get('order_id');

    // Fast-path: order_id already in URL (came from "Write a Review" button)
    if (fromReview && urlOrderId) {
      try {
        const checkRes  = await fetch(`${API}?action=check&order_id=${urlOrderId}&product_id=${currentProductId}&user_id=${userId}`);
        const checkData = await checkRes.json();
        if (checkData.can_review) {
          currentOrderId = parseInt(urlOrderId);
          const btn = document.getElementById('writeReviewBtn');
          if (btn) btn.classList.remove('hidden');
          // Auto-scroll to reviews section and open the modal
          setTimeout(() => {
            const section = document.getElementById('reviewsSection');
            if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => {
              resetForm();
              document.getElementById('reviewModal')?.classList.remove('hidden');
            }, 600);
          }, 400);
          return;
        } else if (checkData.already_reviewed) {
          // Already reviewed — just scroll to the reviews section
          setTimeout(() => {
            const section = document.getElementById('reviewsSection');
            if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }, 400);
          return;
        }
      } catch { /* fall through to full scan */ }
    }

    // Full scan: find any received order with this product
    try {
      const email = localStorage.getItem('tl_user_email') || '';
      const res   = await fetch(`../backend/get_deliveries.php?user_id=${userId}&email=${encodeURIComponent(email)}`);
      const orders = await res.json();

      if (!Array.isArray(orders)) return;

      const receivedOrders = orders.filter(o => (o.status || '').toLowerCase() === 'received');

      for (const order of receivedOrders) {
        let items = [];
        try { items = JSON.parse(order.items || '[]'); } catch { continue; }
        const hasProduct = items.some(i => String(i.id || i.product_id) === String(currentProductId));
        if (!hasProduct) continue;

        const checkRes  = await fetch(`${API}?action=check&order_id=${order.id}&product_id=${currentProductId}&user_id=${userId}`);
        const checkData = await checkRes.json();

        if (checkData.can_review) {
          currentOrderId = order.id;
          const btn = document.getElementById('writeReviewBtn');
          if (btn) btn.classList.remove('hidden');
          break;
        }
      }
    } catch { /* silently fail */ }
  }

  // ── Bind all events ─────────────────────────────────────────
  function bindEvents() {
    // Star filter buttons
    document.getElementById('starFilters')?.addEventListener('click', e => {
      const btn = e.target.closest('.star-filter-btn');
      if (!btn) return;
      document.querySelectorAll('.star-filter-btn').forEach(b => {
        b.style.background = '';
        b.style.color = '';
        b.style.borderColor = '';
        b.classList.remove('active');
      });
      btn.style.background   = 'var(--color-primary)';
      btn.style.color        = '#fff';
      btn.style.borderColor  = 'var(--color-primary)';
      btn.classList.add('active');
      currentFilter = parseInt(btn.dataset.star) || 0;
      loadReviews();
    });

    // Open write review modal
    document.getElementById('writeReviewBtn')?.addEventListener('click', () => {
      resetForm();
      document.getElementById('reviewModal').classList.remove('hidden');
    });

    // Close review modal
    document.getElementById('closeReviewModal')?.addEventListener('click', () => {
      document.getElementById('reviewModal').classList.add('hidden');
    });
    document.getElementById('reviewModal')?.addEventListener('click', e => {
      if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
    });

    // Star picker
    document.getElementById('starPicker')?.addEventListener('click', e => {
      const star = e.target.closest('.star-pick');
      if (!star) return;
      selectedRating = parseInt(star.dataset.val);
      updateStarPicker(selectedRating);
    });
    document.getElementById('starPicker')?.addEventListener('mouseover', e => {
      const star = e.target.closest('.star-pick');
      if (star) updateStarPicker(parseInt(star.dataset.val));
    });
    document.getElementById('starPicker')?.addEventListener('mouseleave', () => {
      updateStarPicker(selectedRating);
    });

    // Image preview
    document.getElementById('reviewImage')?.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = ev => {
        document.getElementById('imagePreview').src = ev.target.result;
        document.getElementById('imagePreviewWrap').classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    });
    document.getElementById('removeImage')?.addEventListener('click', () => {
      document.getElementById('reviewImage').value = '';
      document.getElementById('imagePreview').src = '';
      document.getElementById('imagePreviewWrap').classList.add('hidden');
    });

    // Submit review
    document.getElementById('submitReviewBtn')?.addEventListener('click', submitReview);

    // Admin reply modal close
    document.getElementById('closeAdminReplyModal')?.addEventListener('click', () => {
      document.getElementById('adminReplyModal').classList.add('hidden');
    });
    document.getElementById('adminReplyModal')?.addEventListener('click', e => {
      if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
    });

    // Submit admin reply
    document.getElementById('submitAdminReply')?.addEventListener('click', submitAdminReply);
  }

  // ── Submit review ───────────────────────────────────────────
  async function submitReview() {
    const comment  = document.getElementById('reviewComment').value.trim();
    const msgEl    = document.getElementById('reviewFormMsg');
    const submitBtn = document.getElementById('submitReviewBtn');

    if (!selectedRating) return showFormMsg('Please select a star rating.', 'red');
    if (!comment)        return showFormMsg('Please write a comment.', 'red');
    if (!currentOrderId) return showFormMsg('No eligible order found.', 'red');

    const userId      = localStorage.getItem('tl_user_id')    || '';
    const userName    = localStorage.getItem('tl_name')       || localStorage.getItem('tl_username') || 'User';
    const productName = document.getElementById('prodName')?.textContent || '';
    const imageFile   = document.getElementById('reviewImage')?.files[0];

    const fd = new FormData();
    fd.append('action',       'submit');
    fd.append('order_id',     currentOrderId);
    fd.append('product_id',   currentProductId);
    fd.append('user_id',      userId);
    fd.append('user_name',    userName);
    fd.append('product_name', productName);
    fd.append('rating',       selectedRating);
    fd.append('comment',      comment);
    if (imageFile) fd.append('image', imageFile);

    submitBtn.disabled   = true;
    submitBtn.textContent = 'Submitting…';

    try {
      const res  = await fetch(API, { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showFormMsg('Review submitted! Thank you.', 'green');
        setTimeout(() => {
          document.getElementById('reviewModal').classList.add('hidden');
          document.getElementById('writeReviewBtn')?.classList.add('hidden');
          loadReviews();
        }, 1200);
      } else {
        showFormMsg(data.message || 'Failed to submit.', 'red');
      }
    } catch {
      showFormMsg('Network error. Please try again.', 'red');
    } finally {
      submitBtn.disabled   = false;
      submitBtn.textContent = 'Submit Review';
    }
  }

  // ── Submit admin reply ──────────────────────────────────────
  async function submitAdminReply() {
    const reviewId = document.getElementById('replyReviewId').value;
    const reply    = document.getElementById('adminReplyText').value.trim();
    const adminId  = localStorage.getItem('tl_user_id');

    if (!reply) return showAdminMsg('Reply cannot be empty.', 'red');

    try {
      const res  = await fetch(`${API}?action=reply`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ review_id: reviewId, admin_id: adminId, reply })
      });
      const data = await res.json();
      if (data.success) {
        showAdminMsg('Reply posted!', 'green');
        setTimeout(() => {
          document.getElementById('adminReplyModal').classList.add('hidden');
          loadReviews();
        }, 900);
      } else {
        showAdminMsg(data.message || 'Failed.', 'red');
      }
    } catch {
      showAdminMsg('Network error.', 'red');
    }
  }

  // ── Helpers ─────────────────────────────────────────────────
  function starsHTML(rating) {
    const full  = Math.floor(rating);
    const half  = rating - full >= 0.5 ? 1 : 0;
    const empty = 5 - full - half;
    return '<i class="fas fa-star"></i>'.repeat(full) +
           (half ? '<i class="fas fa-star-half-alt"></i>' : '') +
           '<i class="far fa-star"></i>'.repeat(empty);
  }

  function updateStarPicker(val) {
    document.querySelectorAll('.star-pick').forEach(s => {
      const lit = parseInt(s.dataset.val) <= val;
      s.style.color = lit ? '#f59e0b' : '#d1d5db';
      if (lit) s.classList.add('lit'); else s.classList.remove('lit');
    });
  }

  function resetForm() {
    selectedRating = 0;
    updateStarPicker(0);
    document.getElementById('reviewComment').value = '';
    document.getElementById('reviewImage').value   = '';
    document.getElementById('imagePreview').src    = '';
    document.getElementById('imagePreviewWrap').classList.add('hidden');
    document.getElementById('reviewFormMsg').classList.add('hidden');
  }

  function showFormMsg(msg, color) {
    const el = document.getElementById('reviewFormMsg');
    el.textContent  = msg;
    el.style.color  = color === 'red' ? '#ef4444' : '#22c55e';
    el.classList.remove('hidden');
  }

  function showAdminMsg(msg, color) {
    const el = document.getElementById('adminReplyMsg');
    el.textContent  = msg;
    el.style.color  = color === 'red' ? '#ef4444' : '#22c55e';
    el.classList.remove('hidden');
  }

  function esc(str) {
    return String(str || '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function formatDate(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    return d.toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
  }

  // ── Public API (used by inline onclick) ────────────────────
  window.TLReview = {
    openReplyModal(reviewId) {
      document.getElementById('replyReviewId').value = reviewId;
      document.getElementById('adminReplyText').value = '';
      document.getElementById('adminReplyMsg').classList.add('hidden');
      document.getElementById('adminReplyModal').classList.remove('hidden');
    },
    lightbox(src) {
      const lb = document.createElement('div');
      lb.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;cursor:zoom-out;';
      lb.innerHTML = `<img src="${src}" style="max-width:90vw;max-height:90vh;border-radius:12px;object-fit:contain;">`;
      lb.onclick = () => lb.remove();
      document.body.appendChild(lb);
    }
  };

  // ── Kick off ────────────────────────────────────────────────
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();