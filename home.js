document.addEventListener("DOMContentLoaded", function () {
  let index = 0;
  const slides = document.querySelector(".slides");
  const slideCount = document.querySelectorAll(".slide").length;
  const visibleSlides = 3; // number of slides to show at a time

  function slideShow() {
    index += visibleSlides;
    if (index >= slideCount) {
      index = 0; // loop back
    }
    slides.style.transform = `translateX(-${(index / visibleSlides) * 100}%)`;
  }

  setInterval(slideShow, 3000);
});
