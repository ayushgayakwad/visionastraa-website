document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const menuIcon = document.querySelector('.menu-icon');
    const navbarMenu = document.querySelector('.navbar ul');
    
    if (menuIcon) {
        menuIcon.addEventListener('click', function() {
            navbarMenu.classList.toggle('show');
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.navbar')) {
            if (navbarMenu.classList.contains('show')) {
                navbarMenu.classList.remove('show');
            }
        }
    });
    
    // Carousel Functionality
    const carousel = document.querySelector('.carousel-inner');
    const prevBtn = document.querySelector('.carousel-control.prev');
    const nextBtn = document.querySelector('.carousel-control.next');
    let currentIndex = 0;
    
    if (carousel && prevBtn && nextBtn) {
        const items = document.querySelectorAll('.carousel-item');
        const totalItems = items.length;
        
        function updateCarousel() {
            const itemWidth = carousel.clientWidth;
            carousel.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
        }
        
        prevBtn.addEventListener('click', function() {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : totalItems - 1;
            updateCarousel();
        });
        
        nextBtn.addEventListener('click', function() {
            currentIndex = (currentIndex < totalItems - 1) ? currentIndex + 1 : 0;
            updateCarousel();
        });
        
        // Handle window resize
        window.addEventListener('resize', updateCarousel);
        
        // Initialize carousel position
        updateCarousel();
    }
});