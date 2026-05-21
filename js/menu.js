const menu = document.getElementById("mobile-menu"); // mobile sidebar
const openBtn = document.getElementById("menu-btn");  // hamburger button
const closeBtn = document.getElementById("close-btn"); // X button

openBtn.addEventListener("click", () => {
  menu.style.right = "0"; // slide menu into view
});

closeBtn.addEventListener("click", () => {
  menu.style.right = "-100%"; // hide menu back
});

// ── Show Admin link if user is admin ────────────
(function () {
  const adminLink = document.getElementById("adminMenuLink");
  if (!adminLink) return;
  const role = localStorage.getItem("tl_role");
  if (role === "admin") {
    adminLink.classList.remove("hidden");
  }
})();
