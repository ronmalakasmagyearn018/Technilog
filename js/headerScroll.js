const header = document.getElementById("mainHeader");

let lastScrollY = window.scrollY;

window.addEventListener("scroll", () => {
  const currentScrollY = window.scrollY;

  if (currentScrollY > 60) {
    header.classList.add("shrink");
  } else {
    header.classList.remove("shrink");
  }

  lastScrollY = currentScrollY;
});