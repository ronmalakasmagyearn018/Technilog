const images = [
  "../image/width_smartcamera.png",
  "../image/promotion1.png",
  "../image/promotion2.png"
];

let index = 0;

const img1 = document.getElementById("slider1");
const img2 = document.getElementById("slider2");

let showingFirst = true;

setInterval(() => {

  index = (index + 1) % images.length;

  if (showingFirst) {
    img2.src = images[index];

    img2.classList.remove("opacity-0");
    img2.classList.add("opacity-100");

    img1.classList.add("opacity-0");
    img1.classList.remove("opacity-100");

  } else {
    img1.src = images[index];

    img1.classList.remove("opacity-0");
    img1.classList.add("opacity-100");

    img2.classList.add("opacity-0");
    img2.classList.remove("opacity-100");
  }

  showingFirst = !showingFirst;

}, 5000);