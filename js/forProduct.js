const params = new URLSearchParams(location.search);
const prodId  = params.get('id');
let product        = null;
let selectedVariant = 0;

// ── Cart badge ──────────────────────────────────────────────────
function updateCartBadge() {
    const _uid = localStorage.getItem('tl_user_id') || 'guest';
    const cart  = JSON.parse(localStorage.getItem('tl_cart_' + _uid) || '[]');
    const total = cart.reduce((s, i) => s + i.qty, 0);
    const el    = document.getElementById('cartHeaderBadge');
    if (el) el.textContent = total;
}
updateCartBadge();

// ── Load product ────────────────────────────────────────────────
async function loadProduct() {
    if (!prodId) { window.location.href = 'main.html'; return; }
    try {
        const res = await fetch(`../backend/get_product.php?id=${prodId}`);
        product   = await res.json();
        renderProduct();
        loadRelated(product.category, product.id);
    } catch (e) {
        document.body.innerHTML += '<p class="text-center text-red-400 py-10">Product not found.</p>';
    }
}

function renderProduct() {
    document.title = product.name + ' — Technilog';
    document.getElementById('breadName').textContent = product.name;
    document.getElementById('prodName').textContent  = product.name;
    document.getElementById('prodCat').textContent   = product.category;
    const descEl   = document.getElementById('prodDesc');
    const fullDesc = product.description || '';
    const LIMIT    = 300;
    if (fullDesc.length <= LIMIT) {
        descEl.textContent = fullDesc;
    } else {
        const short = fullDesc.slice(0, LIMIT).trimEnd();
        descEl.innerHTML = '';
        const textNode = document.createElement('span');
        textNode.id = 'descShort';
        textNode.textContent = short + '… ';
        const seeMore = document.createElement('button');
        seeMore.id = 'descToggleBtn';
        seeMore.textContent = 'See more';
        seeMore.style.cssText = 'color:var(--color-primary);font-weight:600;font-size:inherit;background:none;border:none;padding:0;cursor:pointer;';
        seeMore.onclick = function () {
            const isExpanded = descEl.dataset.expanded === '1';
            if (isExpanded) {
                textNode.textContent = short + '… ';
                seeMore.textContent  = 'See more';
                descEl.dataset.expanded = '0';
            } else {
                textNode.textContent = fullDesc + ' ';
                seeMore.textContent  = 'See less';
                descEl.dataset.expanded = '1';
            }
        };
        descEl.dataset.expanded = '0';
        descEl.appendChild(textNode);
        descEl.appendChild(seeMore);
    }

    const imgs    = product.images || [];
    const mainImg = document.getElementById('mainImage');
    if (imgs.length) {
        mainImg.src = imgs[0];
        document.getElementById('thumbsRow').innerHTML = imgs.map((src, i) =>
            `<img class="thumb ${i === 0 ? 'active' : ''}" src="${src}" onclick="setMainImage('${src}', this)">`
        ).join('');
    } else {
        mainImg.src = '../image/logo.png';
    }

    const prices = product.prices || [];
    if (prices.length > 1) {
        document.getElementById('variantsSection').classList.remove('hidden');
        document.getElementById('variantPills').innerHTML = prices.map((v, i) =>
            `<button class="variant-pill ${i === 0 ? 'selected' : ''}" onclick="selectVariant(${i}, this)">
                ${v.label} — ₱${parseFloat(v.price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </button>`
        ).join('');
    }
    setVariantPrice(0);

    if (product.specifications) {
        document.getElementById('specsSection').classList.remove('hidden');
        const rows = product.specifications.split(',').map(s => {
            const [k, ...v] = s.split(':');
            return k && v.length ? `<tr><td>${k.trim()}</td><td>${v.join(':').trim()}</td></tr>` : '';
        }).join('');
        document.getElementById('specsTable').innerHTML =
            rows || `<tr><td colspan="2">${product.specifications}</td></tr>`;
    }

    const stock = prices[0]?.stock ?? 99;
    document.getElementById('stockLabel').textContent = `${stock} in stock`;
}

function setMainImage(src, el) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function selectVariant(i, el) {
    selectedVariant = i;
    document.querySelectorAll('.variant-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    setVariantPrice(i);
}

function setVariantPrice(i) {
    const prices = product?.prices || [];
    const price  = prices[i]?.price ?? 0;
    const stock  = prices[i]?.stock ?? 0;
    document.getElementById('priceDisplay').textContent =
        '₱' + parseFloat(price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('stockLabel').textContent = `${stock} in stock`;
}

// ── Qty ─────────────────────────────────────────────────────────
function changeQty(delta) {
    const input = document.getElementById('qtyInput');
    const stock = product?.prices?.[selectedVariant]?.stock ?? 99;
    let val = parseInt(input.value) + delta;
    if (val < 1)     val = 1;
    if (val > stock) val = stock;
    input.value = val;
}

// ── Login guard ─────────────────────────────────────────────────
function requireLogin(action) {
    if (localStorage.getItem('tl_logged_in') === 'true') return true;
    localStorage.setItem('tl_return_to',      window.location.href);
    localStorage.setItem('tl_pending_action', action);
    window.location.href = '../auth/login.html';
    return false;
}

// ── Cart ────────────────────────────────────────────────────────
function getCartItem() {
    const prices  = product?.prices || [];
    const variant = prices[selectedVariant] || {};
    const qty     = parseInt(document.getElementById('qtyInput').value) || 1;
    return {
        id:       product.id,
        name:     product.name,
        image:    product.images?.[0] || '',
        variant:  variant.label || 'Standard',
        price:    parseFloat(variant.price || 0),
        category: (product.category || '').trim(),
        qty,
    };
}

function addToCart() {
    if (!requireLogin('addToCart')) return;
    const item = getCartItem();
    const _uid2 = localStorage.getItem('tl_user_id') || 'guest';
    const cart = JSON.parse(localStorage.getItem('tl_cart_' + _uid2) || '[]');
    const idx  = cart.findIndex(c => c.id === item.id && c.variant === item.variant);
    if (idx > -1) cart[idx].qty += item.qty;
    else          cart.push(item);
    localStorage.setItem('tl_cart_' + _uid2, JSON.stringify(cart));
    showToast('Added to cart!');
    updateCartBadge();
}

function buyNow() {
    if (!requireLogin('buyNow')) return;
    // Encode the item in the URL so each Buy Now click is always fresh —
    // no stale sessionStorage, no cross-tab bleed.
    const item = getCartItem();
    const encoded = encodeURIComponent(JSON.stringify(item));
    window.location.href = 'checkout.html?buynow=' + encoded;
}

function showToast(msg) {
    const t = document.getElementById('cartToast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

// ── Related products ────────────────────────────────────────────
async function loadRelated(category, excludeId) {
    try {
        const res     = await fetch(`../backend/get_products.php?category=${encodeURIComponent(category)}`);
        const data    = await res.json();
        const related = data.filter(p => p.id != excludeId).slice(0, 4);
        const grid    = document.getElementById('relatedGrid');
        if (!related.length) {
            grid.innerHTML = '<p class="col-span-full text-gray-400 text-sm">No related products.</p>';
            return;
        }
        grid.innerHTML = related.map(p => {
            const img   = p.images?.[0] || '';
            const price = p.prices?.[0]?.price ?? 0;
            return `
                <div class="related-card" onclick="window.location='product.html?id=${p.id}'">
                    ${img
                        ? `<img src="${img}" alt="${p.name}">`
                        : `<div style="height:130px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;"><i class="fas fa-image text-2xl"></i></div>`
                    }
                    <div class="related-card-body">
                        <div class="text-xs text-gray-500 mb-1">${p.category}</div>
                        <div class="font-semibold text-gray-800 text-sm leading-tight">${p.name}</div>
                        <div class="font-bold mt-1 text-sm" style="color:var(--color-primary);">
                            ₱${parseFloat(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                        </div>
                    </div>
                </div>`;
        }).join('');
    } catch (e) { }
}

loadProduct();

// ── Handle return from login (runs once on page load) ───────────
// If user was bounced to login from this exact product page,
// auto-fire their original action after the product loads.
(function handleReturnFromLogin() {
    const pending  = localStorage.getItem('tl_pending_action');
    if (!pending) return;

    // Clear keys immediately — prevents any loop
    localStorage.removeItem('tl_pending_action');

    // Poll until product data is ready, then fire
    const poll = setInterval(() => {
        if (!product) return;
        clearInterval(poll);
        if      (pending === 'addToCart') addToCart();
        else if (pending === 'buyNow')   buyNow();
    }, 80);
})();