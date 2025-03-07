document.addEventListener("DOMContentLoaded", () => {
    let currentSlide = 1;
    const carouselInner = document.querySelector('.carousel-inner');
    const slides = Array.from(document.querySelectorAll('.carousel-item'));
    const totalSlides = slides.length;

    if (!carouselInner || slides.length === 0) {
        console.error("Carousel elements not found!");
        return;
    }

    // Clone first and last slides for infinite looping
    const firstClone = slides[0].cloneNode(true);
    const lastClone = slides[totalSlides - 1].cloneNode(true);

    firstClone.classList.add("clone");
    lastClone.classList.add("clone");

    // Append clones to carousel
    carouselInner.appendChild(firstClone);
    carouselInner.prepend(lastClone);

    // Get updated slides after cloning
    const allSlides = Array.from(document.querySelectorAll('.carousel-item'));
    const updatedTotalSlides = allSlides.length;

    // Set initial position
    carouselInner.style.transform = `translateX(-${100}%)`;

    function updateSlidePosition() {
        carouselInner.style.transition = 'transform 0.5s ease-in-out';
        carouselInner.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    // Move to the next slide
    function nextSlide() {
        if (currentSlide >= updatedTotalSlides - 1) return;
        currentSlide++;
        updateSlidePosition();
    }

    // Move to the previous slide
    function prevSlide() {
        if (currentSlide <= 0) return;
        currentSlide--;
        updateSlidePosition();
    }

    // Handle infinite loop transition
    carouselInner.addEventListener('transitionend', () => {
        if (currentSlide === updatedTotalSlides - 1) {
            setTimeout(() => {
                carouselInner.style.transition = 'none';
                currentSlide = 1;
                carouselInner.style.transform = `translateX(-${currentSlide * 100}%)`;
            }, 50);
        } else if (currentSlide === 0) {
            setTimeout(() => {
                carouselInner.style.transition = 'none';
                currentSlide = totalSlides;
                carouselInner.style.transform = `translateX(-${currentSlide * 100}%)`;
            }, 50);
        }
    });

    // Auto-slide every 5 seconds
    let autoSlide = setInterval(nextSlide, 5000);

    // Reset auto-slide on user interaction
    function resetAutoSlide() {
        clearInterval(autoSlide);
        autoSlide = setInterval(nextSlide, 5000);
    }

    // Attach event listeners to buttons
    const nextButton = document.querySelector('.next');
    const prevButton = document.querySelector('.prev');

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            nextSlide();
            resetAutoSlide();
        });
    } else {
        console.warn("Next button not found!");
    }

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            prevSlide();
            resetAutoSlide();
        });
    } else {
        console.warn("Previous button not found!");
    }
});


