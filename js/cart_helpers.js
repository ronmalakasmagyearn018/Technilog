// js/cart_helpers.js
// ── Shared helpers for cart and Buy Now ──────────────────────────

const _userId = () => localStorage.getItem('tl_user_id') || 'guest';
const CART_KEY = () => 'tl_cart_' + _userId();

// ── Add to Cart ───────────────────────────────────────────────────
function addToCart(product) {
  // product = { id, name, price, image, category, qty }
  let cart = getCart();
  const idx = cart.findIndex(i => String(i.id) === String(product.id));
  if (idx >= 0) {
    cart[idx].qty = (cart[idx].qty || 1) + (product.qty || 1);
  } else {
    cart.push({ ...product, qty: product.qty || 1 });
  }
  localStorage.setItem(CART_KEY(), JSON.stringify(cart));
}

// ── Get Cart ──────────────────────────────────────────────────────
function getCart() {
  try {
    const raw = localStorage.getItem(CART_KEY());
    return raw ? JSON.parse(raw) : [];
  } catch { return []; }
}

// ── Clear Cart ────────────────────────────────────────────────────
function clearCart() {
  localStorage.removeItem(CART_KEY());
}

// ── Buy Now ───────────────────────────────────────────────────────
// Stores ONLY this product in sessionStorage and redirects to checkout.
// sessionStorage is cleared on tab close OR when checkout.html reads it.
function buyNow(product) {
  // Clear any previous buy-now item
  sessionStorage.removeItem('tl_buynow');
  // Store the single product
  sessionStorage.setItem('tl_buynow', JSON.stringify({ ...product, qty: product.qty || 1 }));
  window.location.href = 'checkout.html';
}

// ── Cart item count badge ─────────────────────────────────────────
function updateCartBadge() {
  const cart  = getCart();
  const count = cart.reduce((s, i) => s + (parseInt(i.qty)||1), 0);
  document.querySelectorAll('.cart-count-badge').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', updateCartBadge);